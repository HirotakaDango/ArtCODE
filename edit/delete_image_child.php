<?php
require_once('../auth.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Connect to the database using PDO
  $db = new PDO('sqlite:../database.sqlite');

  // Get the image ID from the form submission
  $image_id = $_POST['image_id'];

  // Get the filename of the original image and its thumbnail
  $stmt = $db->prepare("SELECT filename FROM image_child WHERE id = :image_id");
  $stmt->bindParam(':image_id', $image_id);
  $stmt->execute();
  $result = $stmt->fetch();

  if ($result) {
    $filename = $result['filename'];

    // Define paths to the image and thumbnail
    $image_path = '../images/' . $filename;
    $thumbnail_path = '../thumbnails/' . $filename;

    // Delete the original file from the "images" folder
    if (file_exists($image_path)) {
      unlink($image_path);
    }

    // Delete the thumbnail file from the "thumbnails" folder
    if (file_exists($thumbnail_path)) {
      unlink($thumbnail_path);
    }
  }

  // Perform the deletion in the "image_child" table
  $stmt = $db->prepare("DELETE FROM image_child WHERE id = :image_id");
  $stmt->bindParam(':image_id', $image_id);
  $stmt->execute();

  // Redirect back to the page after deletion
  header("Location: ".$_SERVER['HTTP_REFERER']);
  exit();
} else {
  // Handle invalid requests (not POST)
  // You can redirect the user to an error page or perform other actions.
  echo "Invalid request method";
}
?>