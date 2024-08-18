<?php
// Replace this with the actual path to your database.sqlite file
$dbPath = 'database.sqlite';

// Connect to the database
$db = new SQLite3($dbPath);

// Check if the connection was successful
if (!$db) {
  die("Connection failed: " . $db->lastErrorMsg());
}

// Get artworkid, display, and option parameters from query string
$artworkId = isset($_GET['artworkid']) ? intval($_GET['artworkid']) : 0;
$display = isset($_GET['display']) ? $_GET['display'] : '';
$option = isset($_GET['option']) ? $_GET['option'] : '';

if ($display === 'all_images') {
  // Query to retrieve all image details from the 'images' table
  $queryAllImages = $db->query("
      SELECT id, filename, tags, title, imgdesc, link, date, view_count, type, episode_name, artwork_type, `group`, categories, language, parodies, characters, original_filename
      FROM images
  ");

  $allImagesData = [];
  while ($row = $queryAllImages->fetchArray(SQLITE3_ASSOC)) {
    $imageId = $row['id'];
    
    if ($option === 'image_child') {
      // Query to retrieve related image_child records for each image
      $queryImageChild = $db->prepare("
          SELECT id, filename, image_id, original_filename
          FROM image_child
          WHERE image_id = :imageId
      ");
      $queryImageChild->bindValue(':imageId', $imageId, SQLITE3_INTEGER);
      $resultImageChild = $queryImageChild->execute();

      $imageChildData = [];
      while ($childRow = $resultImageChild->fetchArray(SQLITE3_ASSOC)) {
        $imageChildData[] = $childRow;
      }

      $row['image_child'] = $imageChildData;
    }

    $allImagesData[] = $row;
  }

  // Output all images with optional image_child as JSON
  header('Content-Type: application/json');
  echo json_encode(['images' => $allImagesData], JSON_PRETTY_PRINT);

} else {
  if ($artworkId <= 0) {
    die("Invalid artwork ID");
  }

  // Query to retrieve image details from the 'images' table based on artworkId
  $queryImage = $db->prepare("
      SELECT id, filename, tags, title, imgdesc, link, date, view_count, type, episode_name, artwork_type, `group`, categories, language, parodies, characters, original_filename
      FROM images
      WHERE id = :artworkid
  ");
  $queryImage->bindValue(':artworkid', $artworkId, SQLITE3_INTEGER);
  $resultImage = $queryImage->execute();

  $imageData = $resultImage->fetchArray(SQLITE3_ASSOC);

  if (!$imageData) {
    die("Image not found");
  }

  // Query to retrieve related image_child records with all details
  $queryImageChild = $db->prepare("
      SELECT id, filename, image_id, original_filename
      FROM image_child
      WHERE image_id = :artworkid
  ");
  $queryImageChild->bindValue(':artworkid', $artworkId, SQLITE3_INTEGER);
  $resultImageChild = $queryImageChild->execute();

  $imageChildData = [];
  while ($row = $resultImageChild->fetchArray(SQLITE3_ASSOC)) {
    $imageChildData[] = $row;
  }

  // Check the display parameter and prepare the appropriate response
  if ($display === 'info') {
    // Detailed information response
    $response = [
      'images' => array_merge([$imageData], $imageChildData)
    ];
  } else {
    // Basic information response
    $response = [
      'image' => '/images/' . $imageData['filename'],
      'image_child' => array_map(function($img) {
        return '/images/' . $img['filename'];
      }, $imageChildData)
    ];
  }

  // Output the response as JSON
  header('Content-Type: application/json');
  echo json_encode($response, JSON_PRETTY_PRINT);
}
?>