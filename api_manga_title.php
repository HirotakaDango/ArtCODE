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
    $queryLatest = "
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
    $stmtLatest = $db->prepare($queryLatest);
    $stmtLatest->bindParam(':episode_name', $episode_name);
    $stmtLatest->bindParam(':user_id', $user_id);
    $stmtLatest->execute();
    // Fetch the latest image result
    $latest_cover = $stmtLatest->fetch(PDO::FETCH_ASSOC);
    
    // Query to get the first image for the specified episode and user
    $queryFirst = "
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
    $stmtFirst = $db->prepare($queryFirst);
    $stmtFirst->bindParam(':episode_name', $episode_name);
    $stmtFirst->bindParam(':user_id', $user_id);
    $stmtFirst->execute();
    // Fetch the first image result
    $first_cover = $stmtFirst->fetch(PDO::FETCH_ASSOC);
    
    // Remove email field from results (optional, if you fetched it)
    // This is not necessary if you properly excluded it from the SELECT query
    if (isset($latest_cover['email'])) {
      unset($latest_cover['email']);
    }
    if (isset($first_cover['email'])) {
      unset($first_cover['email']);
    }

    // Query to count the total number of images from images and image_child tables
    $query = "
      SELECT COUNT(*) as total_count
      FROM (
        SELECT id, filename, email
        FROM images
        WHERE artwork_type = 'manga'
        AND episode_name = :episode_name
    
        UNION ALL
    
        SELECT image_child.id, image_child.filename, images.email
        FROM image_child
        JOIN images ON image_child.image_id = images.id
        WHERE images.artwork_type = 'manga'
        AND images.episode_name = :episode_name
      ) AS all_images
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':episode_name', $episode_name);
    $stmt->execute();
    // Fetch the total count result
    $total_count = $stmt->fetchColumn();

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

    // --- TAGS LOGIC START ---
    
    // Build tags from all images for this episode (collect unique tags)
    $tags = []; // This will store unique tags found in the current episode
    foreach ($results as $image) {
      // Ensure the 'tags' field exists and is not null before exploding
      if (isset($image['tags']) && !is_null($image['tags'])) {
        $imageTags = explode(',', $image['tags']);
        foreach ($imageTags as $tag) {
          $tag = trim($tag);
          if (!empty($tag)) {
            // Use the tag as the key. Initial count can be anything, will be overwritten.
            $tags[$tag] = 0; // Add tag to the list if not already present
          }
        }
      }
    }
    
    // Count for tags (Count how many unique episodes (manga type) globally contain each tag found in the current episode)
    // Iterate through the unique tags collected from the current episode and query the global count for each.
    if (!empty($tags)) {
      $queryTagCount = "
        SELECT COUNT(DISTINCT episode_name) AS count
        FROM images
        WHERE artwork_type = 'manga'
        AND (',' || tags || ',') LIKE :tag_pattern
      ";
      $stmtTagCount = $db->prepare($queryTagCount);
    
      foreach (array_keys($tags) as $tag) {
        // Prepare the pattern to match the tag within the comma-separated string
        $tagPattern = '%,' . $tag . ',%';
        // Bind the parameter for the current tag in the prepared statement
        $stmtTagCount->bindParam(':tag_pattern', $tagPattern);
        $stmtTagCount->execute();
        // Fetch the count for this specific tag
        $count = $stmtTagCount->fetchColumn();
        // Update the count for this specific tag in the $tags array
        $tags[$tag] = $count;
      }
    }
    // The $tags array now contains unique tags found in the current episode's images,
    // with counts representing the total number of unique manga episodes containing that tag globally.
    
    // --- TAGS LOGIC END ---


    // Get the parodies from the current title
    $parodies = [];
    foreach ($results as $image) {
      $imageParodies = explode(',', $image['parodies']);
      foreach ($imageParodies as $parody) {
        $parody = trim($parody);
        if (!empty($parody)) {
          if (!isset($parodies[$parody])) {
            $parodies[$parody] = 0;
          }
        }
      }
    }

    // Get the count of latest images by episode_name for each parody
    $query = "
      SELECT parodies, COUNT(*) as count FROM (
        SELECT parodies, episode_name, MAX(id) as latest_image_id
        FROM images
        WHERE artwork_type = 'manga'
        GROUP BY parodies, episode_name
      ) GROUP BY parodies
    ";
    $stmt = $db->query($query);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $parodyList = explode(',', $row['parodies']);
      foreach ($parodyList as $parody) {
        $parody = trim($parody); // Fix variable name here
        if (isset($parodies[$parody])) {
          $parodies[$parody] += $row['count'];
        }
      }
    }

    // Get the characters from the current title
    $characters = [];
    foreach ($results as $image) {
      $imageCharacters = explode(',', $image['characters']);
      foreach ($imageCharacters as $character) {
        $character = trim($character);
        if (!empty($character)) {
          if (!isset($characters[$character])) {
            $characters[$character] = 0;
          }
        }
      }
    }

    // Get the count of latest images by episode_name for each character
    $query = "
      SELECT characters, COUNT(*) as count FROM (
        SELECT characters, episode_name, MAX(id) as latest_image_id
        FROM images
        WHERE artwork_type = 'manga'
        GROUP BY characters, episode_name
      ) GROUP BY characters
    ";
    $stmt = $db->query($query);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $characterList = explode(',', $row['characters']);
      foreach ($characterList as $character) {
        $character = trim($character); // Fix variable name here
        if (isset($characters[$character])) {
          $characters[$character] += $row['count'];
        }
      }
    }

    // Get the count of all non-empty "group" images grouped by the "group" column based on all episode_name
    $queryGroupCounts = "
      SELECT images.`group`, COUNT(*) as count
      FROM (
        SELECT DISTINCT episode_name, MAX(id) as latest_image_id
        FROM images
        WHERE artwork_type = 'manga'
        AND email = (SELECT email FROM users WHERE id = :user_id)
        GROUP BY episode_name
      ) AS latest_images
      JOIN images ON latest_images.latest_image_id = images.id
      JOIN users ON images.email = users.email
      WHERE images.artwork_type = 'manga'
      AND users.id = :user_id
      AND images.`group` IS NOT NULL AND images.`group` <> ''
      AND images.`group` IN (
        SELECT DISTINCT images.`group`
        FROM images
        WHERE artwork_type = 'manga'
        AND episode_name = :episode_name
        AND email = (SELECT email FROM users WHERE id = :user_id)
      )
      GROUP BY images.`group`
    ";
    
    $stmtGroupCounts = $db->prepare($queryGroupCounts);
    $stmtGroupCounts->bindParam(':user_id', $user_id);
    $stmtGroupCounts->bindParam(':episode_name', $episode_name);
    $stmtGroupCounts->execute();
    $groupCounts = $stmtGroupCounts->fetchAll(PDO::FETCH_ASSOC);

    // Get the count of all images grouped by the "categories" column based on the latest image in current tags for the current episode_name
    $queryCategoriesCounts = "
      SELECT images.categories, COUNT(*) as count
      FROM (
        SELECT DISTINCT episode_name, MAX(id) as latest_image_id
        FROM images
        WHERE artwork_type = 'manga'
        GROUP BY episode_name
      ) AS latest_images
      JOIN images ON latest_images.latest_image_id = images.id
      WHERE images.artwork_type = 'manga'
      AND images.categories IS NOT NULL AND images.categories <> ''
      AND images.categories IN (
        SELECT DISTINCT images.categories
        FROM images
        WHERE artwork_type = 'manga'
        AND episode_name = :episode_name
      )
      GROUP BY images.categories
    ";
    $stmtCategoriesCounts = $db->prepare($queryCategoriesCounts);
    $stmtCategoriesCounts->bindParam(':episode_name', $episode_name);
    $stmtCategoriesCounts->execute();
    $categoriesCounts = $stmtCategoriesCounts->fetchAll(PDO::FETCH_ASSOC);

    // Get the count of all images grouped by the "language" column based on the latest image in current tags for the current episode_name
    $queryLanguageCounts = "
      SELECT images.language, COUNT(*) as count
      FROM (
        SELECT DISTINCT episode_name, MAX(id) as latest_image_id
        FROM images
        WHERE artwork_type = 'manga'
        GROUP BY episode_name
      ) AS latest_images
      JOIN images ON latest_images.latest_image_id = images.id
      WHERE images.artwork_type = 'manga'
      AND images.language IS NOT NULL AND images.language <> ''
      AND images.language IN (
        SELECT DISTINCT images.language
        FROM images
        WHERE artwork_type = 'manga'
        AND episode_name = :episode_name
      )
      GROUP BY images.language
    ";
    $stmtLanguageCounts = $db->prepare($queryLanguageCounts);
    $stmtLanguageCounts->bindParam(':episode_name', $episode_name);
    $stmtLanguageCounts->execute();
    $languageCounts = $stmtLanguageCounts->fetchAll(PDO::FETCH_ASSOC);

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
      'parodies' => $parodies,
      'characters' => $characters,
      'artist_image_count' => $artistImageCount,
      'total_count' => $total_count,
      'total_view_count' => $total_view_count,
      'group_counts' => $groupCounts,
      'categories_counts' => $categoriesCounts,
      'language_counts' => $languageCounts
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