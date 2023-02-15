<?php
  session_start();
  if (!isset($_SESSION['username'])) {
    header("Location: session.php");
    exit;
  }

  // Connect to the SQLite database
  $db = new SQLite3('database.sqlite');

  // Get the filename of the image to delete
  $filename = $_POST['filename'];

  // Delete the image from the database
  $stmt = $db->prepare("DELETE FROM images WHERE filename = :filename");
  $stmt->bindValue(':filename', $filename);
  $stmt->execute();

  // Delete the original image and thumbnail
  unlink('images/' . $filename);
  unlink('thumbnails/' . $filename);

  header("Location: profile.php");
  exit;
?>
