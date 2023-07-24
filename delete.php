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

  // Get the IDs of child images associated with the deleted image from the "image_child" table
  $child_image_ids = $db->query("SELECT id FROM image_child WHERE image_id = $id");

  // Loop through each child image and delete it along with the records in the "image_child" table
  while ($child_image_id = $child_image_ids->fetchArray(SQLITE3_ASSOC)) {
    $child_id = $child_image_id['id'];

    // Delete corresponding records from the "image_child" table
    $stmt = $db->prepare("DELETE FROM image_child WHERE id = :child_id");
    $stmt->bindValue(':child_id', $child_id);
    $stmt->execute();

    // Get the filename of the child image to delete
    $child_filename = $db->querySingle("SELECT filename FROM image_child WHERE id = $child_id");

    // Delete the child image
    unlink('images/' . $child_filename);
  }

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

// Redirect to profile.php with the 'by' parameter
$byParam = isset($_GET['by']) ? $_GET['by'] : ''; // Default value if 'by' parameter is not set
header("Location: profile.php" . ($byParam ? "?by=$byParam" : ""));
exit;
?>
