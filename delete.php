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

  // Get the filename of the image to delete
  $filename = $db->querySingle("SELECT filename FROM images WHERE id = $id");

  // Delete the image from the database
  $stmt = $db->prepare("DELETE FROM images WHERE id = :id");
  $stmt->bindValue(':id', $id);
  $stmt->execute();

  // Delete the original image and thumbnail
  unlink('images/' . $filename);
  unlink('thumbnails/' . $filename);

  header("Location: profile.php");
  exit;
?>
