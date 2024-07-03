<?php
// api_manga_parodies.php
header('Content-Type: application/json');

// Connect to the SQLite database
$db = new SQLite3('database.sqlite', SQLITE3_OPEN_READONLY);

// Function to retrieve parody counts
function getParodyCounts($db) {
  // Retrieve the count of latest images for each parody
  $query = "SELECT `group`, COUNT(*) as count FROM (
              SELECT `group`, episode_name, MAX(id) as latest_image_id
              FROM images
              WHERE artwork_type = 'manga'
              GROUP BY `group`, episode_name
            ) GROUP BY `group`";

  // Log the query for debugging
  error_log("SQL Query: " . $query);

  $result = $db->query($query);

  if (!$result) {
    return ['error' => 'Query failed: ' . $db->lastErrorMsg()];
  }

  // Store the counts as an associative array
  $counts = [];
  while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $parodyList = explode(',', $row['group']);
    foreach ($parodyList as $parody) {
      $trimmedParody = trim($parody);
      if (!empty($trimmedParody)) { // Exclude empty parodies
        if (!isset($counts[$trimmedParody])) {
          $counts[$trimmedParody] = 0;
        }
        $counts[$trimmedParody] += $row['count'];
      }
    }
  }

  // Sort alphabetically and numerically
  ksort($counts, SORT_NATURAL | SORT_FLAG_CASE);

  // Prepare the response
  $response = ['group' => $counts];

  return $response;
}

try {
  $response = getParodyCounts($db);

  // Output response as JSON
  echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
  echo json_encode(['error' => $e->getMessage()]);
}
?>