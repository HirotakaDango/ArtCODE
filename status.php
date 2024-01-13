<?php
require_once('auth.php');

// Connect to the database
$db = new PDO('sqlite:database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("CREATE TABLE IF NOT EXISTS favorites_status (id INTEGER PRIMARY KEY AUTOINCREMENT, status_id INTEGER, email TEXT)");

// Get the email of the current user
$email = $_SESSION['email'];

// Get the emails of the users that the current user is following
$following_query = $db->prepare("SELECT following_email FROM following WHERE follower_email = :email");
$following_query->bindValue(':email', $email, PDO::PARAM_STR);
$following_query->execute();

// Create an array to store the emails of the users that the current user is following
$following_emails = array();
while ($row = $following_query->fetch(PDO::FETCH_ASSOC)) {
  $following_emails[] = $row['following_email'];
}

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $status_id = $_POST['status_id'];

  // Check if the status has already been favorited by the current user
  $stmt = $db->prepare("SELECT COUNT(*) FROM favorites_status WHERE email = :email AND status_id = :status_id");
  $stmt->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
  $stmt->bindParam(':status_id', $status_id, PDO::PARAM_INT);
  $stmt->execute();
  $existing_fav = $stmt->fetchColumn();

  if ($existing_fav == 0) {
    $stmt = $db->prepare("INSERT INTO favorites_status (email, status_id) VALUES (:email, :status_id)");
    $stmt->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
    $stmt->bindParam(':status_id', $status_id, PDO::PARAM_INT);
    $stmt->execute();
  }

  // Redirect back to the image page
  $currentURL = $_SERVER['REQUEST_URI'];
  $redirectURL = $currentURL;
  header("Location: $redirectURL");
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $status_id = $_POST['status_id'];
  $stmt = $db->prepare("DELETE FROM favorites_status WHERE email = :email AND status_id = :status_id");
  $stmt->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
  $stmt->bindParam(':status_id', $status_id, PDO::PARAM_INT);
  $stmt->execute();

  // Redirect back to the image page
  $currentURL = $_SERVER['REQUEST_URI'];
  $redirectURL = $currentURL;
  header("Location: $redirectURL");
  exit();
}

// Handle the delete button
if(isset($_POST['delete'])) {
  $id = $_POST['id'];
  $delete_query = $db->prepare("DELETE FROM status WHERE id = :id AND email = :email");
  $delete_query->bindValue(':id', $id, PDO::PARAM_INT);
  $delete_query->bindValue(':email', $email, PDO::PARAM_STR);

  if (!$delete_query->execute()) {
    // Handle the error, you can output or log the error message
    die("Error executing delete query: " . implode(" ", $delete_query->errorInfo()));
  }

  // Redirect back to the image page
  $currentURL = $_SERVER['REQUEST_URI'];
  $redirectURL = $currentURL;
  header("Location: $redirectURL");
  exit();
}
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Status</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="dropdown container mt-2">
      <a href="status_send.php" type="button" class="btn btn-primary w-100 fw-bold mb-2"><i class="bi bi-send-fill"></i> write something</a>
      <button class="btn btn-sm fw-bold rounded-pill mb-2 btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=newest" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?by=oldest" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?by=top" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'top') echo 'active'; ?>">most liked</a></li>
        <li><a href="?by=least" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'least') echo 'active'; ?>">least liked</a></li>
      </ul> 
    </div> 
        <?php 
        if(isset($_GET['by'])){
          $sort = $_GET['by'];
 
          switch ($sort) {
            case 'newest':
            include "status_desc.php";
            break;
            case 'oldest':
            include "status_asc.php";
            break;
            case 'top':
            include "status_top.php";
            break;
            case 'least':
            include "status_least.php";
            break;
          }
        }
        else {
          include "status_desc.php";
        }
        
        ?>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>