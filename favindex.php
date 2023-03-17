<?php
  session_start();
  if (!isset($_SESSION['username'])) {
    header("Location: session.php");
    exit;
  }

  // Connect to the SQLite database
  $db = new SQLite3('database.sqlite');

  // Get all of the favorite images for the current user
  $username = $_SESSION['username'];
  $result = $db->query("SELECT images.* FROM images INNER JOIN favorites ON images.id = favorites.image_id WHERE favorites.username = '$username' ORDER BY favorites.id DESC");

  // Process any favorite/unfavorite requests
  if (isset($_POST['favorite'])) {
    $image_id = $_POST['image_id'];
    
    // Check if the image has already been favorited by the current user
    $existing_fav = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE username = '$username' AND image_id = $image_id");
    
    if ($existing_fav == 0) {
      $db->exec("INSERT INTO favorites (username, image_id) VALUES ('$username', $image_id)");
    }
    
    // Redirect to the same page to prevent duplicate form submissions
    header("Location: index.php");
    exit();
    
  } elseif (isset($_POST['unfavorite'])) {
    $image_id = $_POST['image_id'];
    $db->exec("DELETE FROM favorites WHERE username = '$username' AND image_id = $image_id");
    
    // Redirect to the same page to prevent duplicate form submissions
    header("Location: index.php");
    exit();
  }
?>