<?php
// Replace this with the actual path to your database.sqlite file
$dbPath = 'database.sqlite';

// Connect to the database
$db = new SQLite3($dbPath);

// Check if the connection was successful
if (!$db) {
  die("Connection failed: " . $db->lastErrorMsg());
}

// Get query parameters
$artworkId = isset($_GET['artworkid']) ? intval($_GET['artworkid']) : 0;
$display = isset($_GET['display']) ? $_GET['display'] : '';
$option = isset($_GET['option']) ? $_GET['option'] : '';
$artworkType = isset($_GET['artwork_type']) ? $_GET['artwork_type'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$uid = isset($_GET['uid']) ? intval($_GET['uid']) : 0;

if ($display === 'all_images') {
  // Prepare the base query for all images with user join
  $queryAllImagesSql = "
    SELECT images.id, images.filename, images.tags, images.title, images.imgdesc, images.link, images.date, images.view_count, images.type, images.episode_name, images.artwork_type, images.`group`, images.categories, images.language, images.parodies, images.characters, images.original_filename
    FROM images
    INNER JOIN users ON users.email = images.email
  ";

  // Add conditions to filter by user ID, artwork_type, and type if provided
  $conditions = [];
  if ($uid) {
    $conditions[] = "users.id = :uid";
  }
  if ($artworkType) {
    $conditions[] = "images.artwork_type = :artworkType";
  }
  if ($type) {
    $conditions[] = "images.type = :type";
  }
  if ($conditions) {
    $queryAllImagesSql .= " WHERE " . implode(" AND ", $conditions);
  }

  $queryAllImages = $db->prepare($queryAllImagesSql);

  if ($uid) {
    $queryAllImages->bindValue(':uid', $uid, SQLITE3_INTEGER);
  }
  if ($artworkType) {
    $queryAllImages->bindValue(':artworkType', $artworkType, SQLITE3_TEXT);
  }
  if ($type) {
    $queryAllImages->bindValue(':type', $type, SQLITE3_TEXT);
  }

  $resultAllImages = $queryAllImages->execute();

  $allImagesData = [];
  while ($row = $resultAllImages->fetchArray(SQLITE3_ASSOC)) {
    $imageId = $row['id'];

    if ($option === 'image_child') {
      // Query to retrieve related image_child records with user join for each image
      $queryImageChild = $db->prepare("
        SELECT image_child.id, image_child.filename, image_child.image_id, image_child.original_filename
        FROM image_child
        INNER JOIN users ON users.email = image_child.email
        WHERE image_child.image_id = :imageId AND users.id = :uid
      ");
      $queryImageChild->bindValue(':imageId', $imageId, SQLITE3_INTEGER);
      $queryImageChild->bindValue(':uid', $uid, SQLITE3_INTEGER);
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

  // Query to retrieve image details from the 'images' table with user join based on artworkId
  $queryImage = $db->prepare("
    SELECT images.id, images.filename, images.tags, images.title, images.imgdesc, images.link, images.date, images.view_count, images.type, images.episode_name, images.artwork_type, images.`group`, images.categories, images.language, images.parodies, images.characters, images.original_filename
    FROM images
    INNER JOIN users ON users.email = images.email
    WHERE images.id = :artworkid
  ");
  $queryImage->bindValue(':artworkid', $artworkId, SQLITE3_INTEGER);
  $resultImage = $queryImage->execute();

  $imageData = $resultImage->fetchArray(SQLITE3_ASSOC);

  if (!$imageData) {
    die("Image not found");
  }

  // Query to retrieve related image_child records
  $queryImageChild = $db->prepare("
    SELECT image_child.id, image_child.filename, image_child.image_id, image_child.original_filename
    FROM image_child
    INNER JOIN users ON users.email = image_child.email
    WHERE image_child.image_id = :artworkid
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