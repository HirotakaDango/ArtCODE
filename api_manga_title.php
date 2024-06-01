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
    // Get the latest image as $latest_cover
    $latest_cover = null;
    if (!empty($results)) {
      $latest_cover = $results[0];
    }

    // Get the tags and their counts
    $tags = [];
    foreach ($results as $image) {
      $imageTags = explode(',', $image['tags']);
      foreach ($imageTags as $tag) {
        $tag = trim($tag);
        if (!empty($tag)) {
          if (isset($tags[$tag])) {
            $tags[$tag]++;
          } else {
            $tags[$tag] = 1;
          }
        }
      }
    }

    // Get the number of images by the current artist
    $query = "
      SELECT COUNT(*) AS count
      FROM images
      WHERE artwork_type = 'manga'
      AND email = (
        SELECT email
        FROM users
        WHERE id = :user_id
      )
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $artistImageCount = $stmt->fetchColumn();

    // Prepare the response data
    $response = [
      'latest_cover' => $latest_cover,
      'images' => $results,
      'tags' => $tags,
      'artist_image_count' => $artistImageCount
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