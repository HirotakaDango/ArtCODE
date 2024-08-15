<?php
// Replace this with the actual path to your database.sqlite file
$dbPath = 'database.sqlite';

// Connect to the database
$db = new SQLite3($dbPath);

// Check if the connection was successful
if (!$db) {
  die("Connection failed: " . $db->lastErrorMsg());
}

// Get artworkid from query string
$artworkId = isset($_GET['artworkid']) ? intval($_GET['artworkid']) : 0;

if ($artworkId <= 0) {
  die("Invalid artwork ID");
}

// Query to retrieve image details from the 'images' table based on artworkId
$queryImage = $db->prepare("SELECT id, filename FROM images WHERE id = :artworkid");
$queryImage->bindValue(':artworkid', $artworkId, SQLITE3_INTEGER);
$resultImage = $queryImage->execute();

$imageData = $resultImage->fetchArray(SQLITE3_ASSOC);

if (!$imageData) {
  die("Image not found");
}

// Construct the image URL path
$imageUrl = '/images/' . $imageData['filename'];

// Query to retrieve related image_child records
$queryImageChild = $db->prepare("SELECT filename FROM image_child WHERE image_id = :artworkid");
$queryImageChild->bindValue(':artworkid', $artworkId, SQLITE3_INTEGER);
$resultImageChild = $queryImageChild->execute();

$imageChildUrls = [];
while ($row = $resultImageChild->fetchArray(SQLITE3_ASSOC)) {
  $imageChildUrls[] = '/images/' . $row['filename'];
}

// Output the image URL and related image_child URLs
header('Content-Type: application/json');
echo json_encode([
  'image' => $imageUrl,
  'image_child' => $imageChildUrls
], JSON_PRETTY_PRINT);
?>