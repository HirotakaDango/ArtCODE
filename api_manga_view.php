<?php
// api_manga_view.php

header('Content-Type: application/json');

try {
  // Check if title, uid, and id parameters are provided
  if (isset($_GET['title']) && isset($_GET['uid']) && isset($_GET['id']) && isset($_GET['page'])) {
    $episode_name = $_GET['title'];
    $user_id = $_GET['uid'];
    $image_id = $_GET['id'];
    $page = $_GET['page'];
    
    // Connect to the SQLite database
    $db = new PDO('sqlite:database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Query to get images from the images table and image_child for the current image ID
    $query = "
      SELECT 
        images.*, 
        users.id AS userid, 
        users.artist
      FROM images
      JOIN users ON images.email = users.email
      WHERE artwork_type = 'manga'
      AND episode_name = :episode_name
      AND users.id = :user_id
      AND images.id = :image_id
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':episode_name', $episode_name);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':image_id', $image_id);
    $stmt->execute();
    
    // Fetch the result
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Query to get image_child for the current image ID
    $query_child = "
      SELECT * 
      FROM image_child 
      WHERE image_id = :image_id
    ";
    $stmt_child = $db->prepare($query_child);
    $stmt_child->bindParam(':image_id', $image_id);
    $stmt_child->execute();
    
    // Fetch all image_child results
    $results_child = $stmt_child->fetchAll(PDO::FETCH_ASSOC);
    
    // Prepare the response data
    $response = [
      'image_details' => $result,
      'image_child' => $results_child
    ];
    
    // Output response as JSON
    echo json_encode($response, JSON_PRETTY_PRINT);
  } else {
    echo json_encode(['error' => 'Missing title, uid, id, or page parameter']);
  }
} catch (PDOException $e) {
  echo json_encode(['error' => $e->getMessage()]);
}
?>
