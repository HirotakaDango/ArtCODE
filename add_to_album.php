<?php
session_start();

if (!isset($_SESSION['email'])) {
  header('Location: session.php');
  exit();
}

if (!isset($_POST['album_id']) || !isset($_POST['image_id'])) {
  header('Location: album_images.php?album_id=' . urlencode($album_id));
  exit();
}

$album_id = intval($_POST['album_id']);
$image_id = intval($_POST['image_id']);
$email = $_SESSION['email'];

$db = new SQLite3('database.sqlite');

// Check if the current user has permission to add the image to the specified album
$stmt = $db->prepare('SELECT 1 FROM album WHERE id = :album_id AND email = :email');
$stmt->bindValue(':album_id', $album_id, SQLITE3_INTEGER);
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute()->fetchArray(SQLITE3_NUM);

if (!$result) {
  // Current user does not have permission to add image to this album
  $_SESSION['danger_message'] = 'You do not have permission to add images to this album';
  header('Location: album_images.php?album_id=' . urlencode($album_id));
  exit();
}

// Check if the image is already associated with the specified album
$stmt = $db->prepare('SELECT 1 FROM image_album WHERE image_id = :image_id AND album_id = :album_id');
$stmt->bindValue(':image_id', $image_id, SQLITE3_INTEGER);
$stmt->bindValue(':album_id', $album_id, SQLITE3_INTEGER);
$result = $stmt->execute()->fetchArray(SQLITE3_NUM);

if ($result) {
  // Image is already associated with the album
  $_SESSION['danger_message'] = 'The image is already associated with the album';
  header('Location: album_images.php?album_id=' . urlencode($album_id));
  exit();
}

// Add the image to the specified album
$stmt = $db->prepare('INSERT INTO image_album (image_id, email, album_id) VALUES (:image_id, :email, :album_id)');
$stmt->bindValue(':image_id', $image_id, SQLITE3_INTEGER);
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$stmt->bindValue(':album_id', $album_id, SQLITE3_INTEGER);
$stmt->execute();

// Set success message
$_SESSION['success_message'] = 'Image successfully added to the album';

// Close the database connection
$db->close();

// Redirect the user to the album_images.php page for the specified album
header('Location: album_images.php?album_id=' . urlencode($album_id));
exit();

?>
