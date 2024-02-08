<?php
// Replace this with the actual path to your database.sqlite file
$dbPath = 'database.sqlite';

// Connect to the database
$db = new SQLite3($dbPath);

// Check if the connection was successful
if (!$db) {
  die("Connection failed: " . $db->lastErrorMsg());
}

// Query to retrieve images from the 'images' table along with artist info
$queryImages = "SELECT i.id, i.filename, i.tags, i.title, i.imgdesc, i.view_count, u.artist, u.id as userId
                FROM images AS i
                LEFT JOIN users AS u ON i.email = u.email
                ORDER BY i.id DESC";
$resultImages = $db->query($queryImages);

$images = [];
while ($row = $resultImages->fetchArray(SQLITE3_ASSOC)) {
  $images[] = [
    'id' => $row['id'],
    'filename' => $row['filename'],
    'tags' => $row['tags'],
    'title' => $row['title'],
    'imgdesc' => $row['imgdesc'],
    'view_count' => $row['view_count'], 
    'artist' => $row['artist'],
    'userId' => $row['userId']
  ];
}

// Query to retrieve images from 'image_child' based on image IDs from 'images'
$imageIds = implode(', ', array_column($images, 'id')); // Get a comma-separated list of image IDs
$queryImageChild = "SELECT * FROM image_child WHERE image_id IN ($imageIds)";
$resultImageChild = $db->query($queryImageChild);

$imageChildData = [];
while ($row = $resultImageChild->fetchArray(SQLITE3_ASSOC)) {
  $imageChildData[] = [
    'id' => $row['id'],
    'filename' => $row['filename'],
    'image_id' => $row['image_id']
  ];
}

// Retrieve favorites count for each image
$favoritesCounts = [];  // To store image_id => favorites count pairs
$queryFavoritesCount = "SELECT image_id, COUNT(*) AS count FROM favorites GROUP BY image_id";
$resultFavoritesCount = $db->query($queryFavoritesCount);

while ($row = $resultFavoritesCount->fetchArray(SQLITE3_ASSOC)) {
  $imageId = $row['image_id'];
  $favoritesCount = $row['count'];
  $favoritesCounts[$imageId] = $favoritesCount;
}

// Add favorites count to the images array
foreach ($images as &$image) {
  $imageId = $image['id'];
  $image['favorites_count'] = isset($favoritesCounts[$imageId]) ? $favoritesCounts[$imageId] : 0;
}

// Output the updated images array with favorites counts
header('Content-Type: application/json');
echo json_encode(['images' => $images, 'image_child' => $imageChildData], JSON_PRETTY_PRINT);

?>
