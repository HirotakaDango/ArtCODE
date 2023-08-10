<?php
// Replace this with the actual path to your database.sqlite file
$dbPath = 'database.sqlite';

// Connect to the database
$db = new SQLite3($dbPath);

// Check if the connection was successful
if (!$db) {
  die("Connection failed: " . $db->lastErrorMsg());
}
// Assuming you have the SQLite3 database connection established
$query = "SELECT * FROM images ORDER BY id DESC";
$result = $db->query($query);

$images = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  $images[] = [
    'id' => $row['id'],
    'filename' => $row['filename'],
    'tags' => $row['tags'],
    'title' => $row['title'],
    'imgdesc' => $row['imgdesc']
  ];
}

header('Content-Type: application/json');
echo json_encode($images);
?>
