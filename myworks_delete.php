<?php
require_once('auth.php');

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Get the id of the image to delete
$id = $_POST['id'];

// Start a transaction
$db->exec('BEGIN');

try {
  // Get the filename of the image to delete
  $filename = $db->querySingle("SELECT filename FROM images WHERE id = $id");

  // Delete records from the reply_comments table based on the comment ID (comment_id)
  $stmt = $db->prepare("DELETE FROM reply_comments WHERE comment_id IN (SELECT id FROM comments WHERE filename = :filename)");
  $stmt->bindValue(':filename', $id);
  $stmt->execute();

  // Delete records from the comments table based on the image ID (filename)
  $stmt = $db->prepare("DELETE FROM comments WHERE filename = :filename");
  $stmt->bindValue(':filename', $id);
  $stmt->execute();

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

  // Get the IDs and filenames of child images associated with the deleted image from the "image_child" table
  $child_image_ids = $db->query("SELECT id, filename FROM image_child WHERE image_id = $id");

  // Loop through each child image and delete it along with the records in the "image_child" table
  while ($child_image_id = $child_image_ids->fetchArray(SQLITE3_ASSOC)) {
    $child_id = $child_image_id['id'];
    $child_filename = $child_image_id['filename'];

    // Delete corresponding records from the "image_child" table
    $stmt = $db->prepare("DELETE FROM image_child WHERE id = :child_id");
    $stmt->bindValue(':child_id', $child_id);
    $stmt->execute();

    // Delete the child image if it exists and is a file
    $child_image_path = 'images/' . $child_filename;
    if (file_exists($child_image_path) && is_file($child_image_path)) {
      unlink($child_image_path);
    }
  }

  // Delete the original image and thumbnail if they exist and are files
  $image_path = 'images/' . $filename;
  $thumbnail_path = 'thumbnails/' . $filename;
  if (file_exists($image_path) && is_file($image_path)) {
    unlink($image_path);
  }
  if (file_exists($thumbnail_path) && is_file($thumbnail_path)) {
    unlink($thumbnail_path);
  }

  // Commit the transaction
  $db->exec('COMMIT');
} catch (Exception $e) {
  // Rollback the transaction if an error occurs
  $db->exec('ROLLBACK');
  throw $e;
}

// Redirect to profile.php with the 'by' and 'page' parameters
$pageParam = isset($_GET['page']) ? $_GET['page'] : 1; // Default to page 1 if 'page' parameter is not set
$byParam = isset($_GET['by']) ? $_GET['by'] : 'newest';
header("Location: myworks.php?by=$byParam&page=$pageParam");
exit;
?>
