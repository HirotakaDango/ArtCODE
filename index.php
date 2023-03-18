<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: session.php");
  exit;
}

$username = $_SESSION['username'];

// Connect to the SQLite database using parameterized query
$db = new SQLite3('database.sqlite');
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS images (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, username TEXT, tags TEXT, title TEXT, imgdesc TEXT, link TEXT, date DATETIME)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS favorites (id INTEGER PRIMARY KEY AUTOINCREMENT, image_id INTEGER, username TEXT)");
$stmt->execute();
$stmt = $db->prepare('CREATE TABLE IF NOT EXISTS following (id INTEGER PRIMARY KEY AUTOINCREMENT, follower_username TEXT NOT NULL, following_username TEXT NOT NULL)');
$stmt->execute();
$stmt = $db->prepare('CREATE TABLE IF NOT EXISTS news (id INTEGER PRIMARY KEY, title TEXT, description TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, ver TEXT, verlink TEXT)');
$stmt->execute();

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $existing_fav = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE username = '$username' AND image_id = $image_id");

  if ($existing_fav == 0) {
    $db->exec("INSERT INTO favorites (username, image_id) VALUES ('$username', $image_id)");
  }
  
  // Redirect to the same page with the appropriate sorting parameter
  if (isset($_GET['by']) && $_GET['by'] == 'oldest') {
    header('Location: ' . $_SERVER['PHP_SELF'] . '?by=oldest');
  } else {
    header('Location: ' . $_SERVER['PHP_SELF'] . '?by=newest');
  }
  exit();
  
} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $db->exec("DELETE FROM favorites WHERE username = '$username' AND image_id = $image_id");

  // Redirect to the same page with the appropriate sorting parameter
  if (isset($_GET['by']) && $_GET['by'] == 'oldest') {
    header('Location: ' . $_SERVER['PHP_SELF'] . '?by=oldest');
  } else {
    header('Location: ' . $_SERVER['PHP_SELF'] . '?by=newest');
  }
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  </head>
  <body>
    <?php include('header.php'); ?>
    <?php include 'popular-content.php'; ?>
        <?php 
        if(isset($_GET['by'])){
            $sort = $_GET['by'];
 
            switch ($sort) {
                case 'newest':
                include "index_desc.php";
                break;
                case 'oldest':
                include "index_asc.php";
                break;
            }
        }
        else{
            include "index_desc.php";
        }
        
        ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>