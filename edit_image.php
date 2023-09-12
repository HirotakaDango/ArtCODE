<?php
require_once('auth.php');

// Connect to SQLite database
$db = new PDO('sqlite:database.sqlite');

// Retrieve image details
if (isset($_GET['id'])) {
  $id = $_GET['id'];
  
  // Retrieve the email of the logged-in user
  $email = $_SESSION['email'];
  
  // Select the image details using the image ID and the email of the logged-in user
  $stmt = $db->prepare('SELECT * FROM images WHERE id = :id AND email = :email');
  $stmt->bindParam(':id', $id);
  $stmt->bindParam(':email', $email);
  $stmt->execute();
  $image = $stmt->fetch(PDO::FETCH_ASSOC);

  // Check if the image exists and belongs to the logged-in user
  if (!$image) {
    echo '<meta charset="UTF-8"> 
          <meta name="viewport" content="width=device-width, initial-scale=1">
          <img src="icon/403-Error-Forbidden.svg" style="height: 100%; width: 100%;">
         ';
    exit();
  }

} else {
  // Redirect to error page if image ID is not specified
  header('Location: edit_image.php?id=' . $id);
  exit();
}

// Update image details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $imgdesc = nl2br(filter_var($_POST['imgdesc'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));
  $link = filter_var($_POST['link'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $tags = filter_var($_POST['tags'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $type = filter_var($_POST['type'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW); // Sanitize the type input

  $stmt = $db->prepare('UPDATE images SET title = :title, imgdesc = :imgdesc, link = :link, tags = :tags, type = :type WHERE id = :id');
  $stmt->bindParam(':title', $title);
  $stmt->bindParam(':imgdesc', $imgdesc);
  $stmt->bindParam(':link', $link);
  $stmt->bindParam(':tags', $tags);
  $stmt->bindParam(':type', $type); // Bind the type parameter
  $stmt->bindParam(':id', $id);
  $stmt->execute();
  
  // Redirect to image details page after update
  header('Location: edit_image.php?id=' . $id);
  exit();
}

header("Location: edit/?id=" . $id);
exit();
?>