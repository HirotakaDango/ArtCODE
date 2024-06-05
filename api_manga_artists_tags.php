<?php
// Connect to the SQLite database
$db = new SQLite3('database.sqlite', SQLITE3_OPEN_READONLY);

// Function to retrieve counts based on the search parameter
function getCounts($db, $column) {
  if (isset($_GET[$column]) && $_GET[$column] == 'all') {
    if ($column == 'tag') {
      // Retrieve the count of latest images for each tag
      $query = "SELECT tags, COUNT(*) as count FROM (
                  SELECT tags, episode_name, MAX(id) as latest_image_id
                  FROM images
                  WHERE artwork_type = 'manga'
                  GROUP BY tags, episode_name
                ) GROUP BY tags";
    } elseif ($column == 'artist') {
      // Retrieve the count of latest images for each artist along with user id
      $query = "SELECT users.artist, users.id as userid, COUNT(latest_images.latest_image_id) as count FROM (
                  SELECT email, episode_name, MAX(id) as latest_image_id
                  FROM images
                  WHERE artwork_type = 'manga'
                  GROUP BY email, episode_name
                ) latest_images
                JOIN users ON latest_images.email = users.email
                GROUP BY users.artist, users.id";
    }

    // Log the query for debugging
    error_log("SQL Query: " . $query);

    $result = $db->query($query);

    if (!$result) {
      return ['error' => 'Query failed: ' . $db->lastErrorMsg()];
    }

    // Store the counts as an associative array
    $counts = [];
    if ($column == 'tag') {
      while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $tagList = explode(',', $row['tags']);
        foreach ($tagList as $tag) {
          $trimmedTag = trim($tag);
          if (!isset($counts[$trimmedTag])) {
            $counts[$trimmedTag] = 0;
          }
          $counts[$trimmedTag] += $row['count'];
        }
      }
    } elseif ($column == 'artist') {
      while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $artist = trim($row['artist']);
        if (!isset($counts[$artist])) {
          $counts[$artist] = [
            'count' => 0,
            'userid' => $row['userid']
          ];
        }
        $counts[$artist]['count'] += $row['count'];
      }
    }

    // Sort alphabetically and numerically
    ksort($counts, SORT_NATURAL | SORT_FLAG_CASE);

    // Prepare the response
    $response = [$column . 's' => $counts];

    return $response;
  }
  return ['error' => 'Invalid or missing parameter'];
}

try {
  $response = [];

  if (isset($_GET['tag']) && $_GET['tag'] == 'all') {
    $response = getCounts($db, 'tag');
  } elseif (isset($_GET['artist']) && $_GET['artist'] == 'all') {
    $response = getCounts($db, 'artist');
  } else {
    $response = ['error' => 'Invalid or missing parameter'];
  }

  // Output response as JSON
  echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
  echo json_encode(['error' => $e->getMessage()]);
}
?>