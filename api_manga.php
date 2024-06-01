<?php
// api_manga.php
header('Content-Type: application/json');
try {
  // Connect to the SQLite database
  $db = new PDO('sqlite:database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
  // Build the base query
  $query = "
    SELECT 
      images.*, 
      users.id as userid, 
      users.email, 
      users.artist
    FROM images
    JOIN users ON images.email = users.email
    WHERE artwork_type = 'manga'
  ";
  
  // Add conditions based on the provided parameters
  $conditions = [];
  $params = [];

  if (isset($_GET['artist'])) {
    $conditions[] = 'users.artist LIKE :artist';
    $params[':artist'] = '%' . $_GET['artist'] . '%';
  }
  
  if (isset($_GET['uid'])) {
    $conditions[] = 'users.id = :user_id';
    $params[':user_id'] = $_GET['uid'];
  }

  if (isset($_GET['tag'])) {
    $conditions[] = "(',' || images.tags || ',' LIKE :tag)";
    $params[':tag'] = '%,' . $_GET['tag'] . ',%';
  } 
  
  if (isset($_GET['search'])) {
    $searchTerms = explode(',', $_GET['search']);
    foreach ($searchTerms as $index => $term) {
      $paramName = ":term$index";
      $conditions[] = "(images.title LIKE $paramName OR images.tags LIKE $paramName OR images.episode_name LIKE $paramName)";
      $params[$paramName] = '%' . trim($term) . '%';
    }
  }
  
  // If there are conditions, add them to the query
  if ($conditions) {
    $query .= ' AND ' . implode(' AND ', $conditions);
  }
  
  // Add the grouping and ordering
  $query .= "
    AND images.id IN (
      SELECT MAX(images.id)
      FROM images
      JOIN users ON images.email = users.email
      WHERE artwork_type = 'manga'
      GROUP BY episode_name, users.id
    )
    ORDER BY episode_name DESC, users.id ASC
  ";
  
  // Prepare and execute the query
  $stmt = $db->prepare($query);
  foreach ($params as $param => $value) {
    $stmt->bindValue($param, $value);
  }
  $stmt->execute();
  
  // Fetch all results
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Check if there are results, if not set to empty array
  if (!$results) {
    $results = [];
  }

  // Remove email field from results
  foreach ($results as &$result) {
    unset($result['email']);
  }

  // Output results as JSON
  echo json_encode($results, JSON_PRETTY_PRINT);
} catch (PDOException $e) {
  echo json_encode(['error' => $e->getMessage()]);
}
?>