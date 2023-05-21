<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Get the id of the image to delete
$id = $_POST['id'];

// Start a transaction
$db->exec('BEGIN');

try {
  // Get the filename of the image to delete
  $filename = $db->querySingle("SELECT filename FROM images WHERE id = $id");

  // Delete the image from the images table
  $stmt = $db->prepare("DELETE FROM images WHERE id = :id");
  $stmt->bindValue(':id', $id);
  $stmt->execute();

  // Delete corresponding records from the image_album table
  $stmt = $db->prepare("DELETE FROM image_album WHERE image_id = :image_id");
  $stmt->bindValue(':image_id', $id);
  $stmt->execute();

  // Delete corresponding records from the favorites table
  $stmt = $db->prepare("DELETE FROM favorites WHERE image_id = :image_id");
  $stmt->bindValue(':image_id', $id);
  $stmt->execute();

  // Delete the original image and thumbnail
  unlink('images/' . $filename);
  unlink('thumbnails/' . $filename);

  // Commit the transaction
  $db->exec('COMMIT');
} catch (Exception $e) {
  // Rollback the transaction if an error occurs
  $db->exec('ROLLBACK');
  throw $e;
}

header("Location: profile.php");
exit;
?>
