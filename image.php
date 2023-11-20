<?php
require_once('auth.php');

// Connect to the database using PDO
$db = new PDO('sqlite:database.sqlite');

// Get the artworkid from the query string
$artworkid = $_GET['artworkid'];

// Get the current image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id = :artworkid ");
$stmt->bindParam(':artworkid', $artworkid);
$stmt->execute();
$image = $stmt->fetch();

// Check if the image exists in the database
if (!$image) {
  header("Location: error.php");
  exit; // Stop further execution
}

// Get the ID of the current image and the email of the owner
$image_id = $image['id'];
$email = $_SESSION['email'];

// Get the display name based on the email from the users table
$stmt = $db->prepare("SELECT display FROM users WHERE email = :email ");
$stmt->bindParam(':email', $email);
$stmt->execute();
$user = $stmt->fetch();

// Check if the user exists in the database
if (!$user) {
  header("Location: error.php");
  exit; // Stop further execution
}

// Get the display name or set a default value if it's empty
$display = empty($user['display']) ? 'view' : $user['display'];

// Redirect to the corresponding display.php page with the artworkid
header("Location: $display.php?artworkid=$artworkid");
exit;
?>
