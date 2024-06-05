<?php
// api_manga_title.php
header('Content-Type: application/json');

// Check if title and uid parameters are provided
if (isset($_GET['title']) && isset($_GET['uid'])) {
  $episode_name = $_GET['title'];
  $user_id = $_GET['uid'];
  try {
    // Connect to the SQLite database
    $db = new PDO('sqlite:database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to get the latest image for the specified episode and user
    $query = "
      SELECT 
        images.*,
        users.id as userid,
        users.artist
      FROM images
      JOIN users ON images.email = users.email
      WHERE artwork_type = 'manga'
      AND episode_name = :episode_name
      AND users.id = :user_id
      ORDER BY images.id DESC
      LIMIT 1
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':episode_name', $episode_name);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    // Fetch the latest image result
    $latest_cover = $stmt->fetch(PDO::FETCH_ASSOC);

    // Query to get the latest image for the specified episode and user
    $query = "
      SELECT 
        images.*,
        users.id as userid,
        users.artist
      FROM images
      JOIN users ON images.email = users.email
      WHERE artwork_type = 'manga'
      AND episode_name = :episode_name
      AND users.id = :user_id
      ORDER BY images.id ASC
      LIMIT 1
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':episode_name', $episode_name);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    // Fetch the latest image result
    $first_cover = $stmt->fetch(PDO::FETCH_ASSOC);

    // Query to get all images for the specified episode and user
    $query = "
      SELECT 
        images.*,
        users.id as userid,
        users.artist
      FROM images
      JOIN users ON images.email = users.email
      WHERE artwork_type = 'manga'
      AND episode_name = :episode_name
      AND users.id = :user_id
      ORDER BY images.id DESC
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':episode_name', $episode_name);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    // Fetch all results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Remove email field from results
    foreach ($results as &$result) {
      unset($result['email']);
    }

    // Calculate the total view_count from all images
    $total_view_count = 0;
    foreach ($results as $image) {
      $total_view_count += $image['view_count'];
    }

    // Get the tags from the current title
    $tags = [];
    foreach ($results as $image) {
      $imageTags = explode(',', $image['tags']);
      foreach ($imageTags as $tag) {
        $tag = trim($tag);
        if (!empty($tag)) {
          if (!isset($tags[$tag])) {
            $tags[$tag] = 0;
          }
        }
      }
    }

    // Get the count of latest images by episode_name for each tag
    $query = "
      SELECT tags, COUNT(*) as count FROM (
        SELECT tags, episode_name, MAX(id) as latest_image_id
        FROM images
        WHERE artwork_type = 'manga'
        GROUP BY tags, episode_name
      ) GROUP BY tags
    ";
    $stmt = $db->query($query);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $tagList = explode(',', $row['tags']);
      foreach ($tagList as $tag) {
        $tag = trim($tag);
        if (isset($tags[$tag])) {
          $tags[$tag] += $row['count'];
        }
      }
    }

    // Get the number of latest images by the current artist grouped by episode_name
    $query = "
      SELECT COUNT(*) AS count
      FROM (
        SELECT MAX(id) as latest_image_id
        FROM images
        WHERE artwork_type = 'manga'
        AND email = (
          SELECT email
          FROM users
          WHERE id = :user_id
        )
        GROUP BY episode_name
      ) latest_images
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $artistImageCount = $stmt->fetchColumn();

    // Prepare the response data
    $response = [
      'latest_cover' => $latest_cover,
      'first_cover' => $first_cover,
      'images' => $results,
      'tags' => $tags,
      'artist_image_count' => $artistImageCount,
      'total_view_count' => $total_view_count
    ];

    // Output response as JSON
    echo json_encode($response, JSON_PRETTY_PRINT);

  } catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
  }
} else {
  echo json_encode(['error' => 'Missing title or uid parameter']);
}
?>