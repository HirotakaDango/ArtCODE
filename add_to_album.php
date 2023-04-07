<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  header('Location: session.php');
  exit();
}

// Check if the image ID and album name were provided in the URL
if (!isset($_GET['image_id']) || !isset($_GET['album_name'])) {
  header('Location: album.php');
  exit();
}

$image_id = intval($_GET['image_id']);
$album_name = $_GET['album_name'];

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Check if the album exists
$stmt = $db->prepare('SELECT COUNT(*) FROM album WHERE email = :email AND album_name = :album_name');
$stmt->bindValue(':email', $_SESSION['email'], SQLITE3_TEXT);
$stmt->bindValue(':album_name', $album_name, SQLITE3_TEXT);
$count = $stmt->execute()->fetchArray(SQLITE3_NUM)[0];
if ($count == 0) {
  // The album doesn't exist, so create it
  $stmt = $db->prepare('INSERT INTO album (email, album_name, image_id) VALUES (:email, :album_name, :image_id)');
} else {
  // The album exists, so update it
  $stmt = $db->prepare('UPDATE album SET image_id = :image_id WHERE email = :email AND album_name = :album_name');
}
$stmt->bindValue(':email', $_SESSION['email'], SQLITE3_TEXT);
$stmt->bindValue(':album_name', $album_name, SQLITE3_TEXT);
$stmt->bindValue(':image_id', $image_id, SQLITE3_INTEGER);
$stmt->execute();

// Close the database connection
$db->close();

// Redirect the user to the album_images.php page for the specified album
header('Location: album_images.php?album=' . urlencode($album_name));
exit();
?>
