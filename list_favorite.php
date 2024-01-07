<?php
require_once('auth.php');

// Connect to the database
$db = new PDO('sqlite:database.sqlite');

$email = $_SESSION['email'];

// Get the current user's ID
$current_user_id = $_GET['id'];

// Get the current user's email and artist
$query = $db->prepare('SELECT email, artist FROM users WHERE id = :id');
$query->bindParam(':id', $current_user_id);
$query->execute();
$current_user = $query->fetch();
$current_email = $current_user['email'];
$current_artist = $current_user['artist'];

// Get the total count of favorite images for the current user
$query = $db->prepare('SELECT COUNT(*) FROM images JOIN favorites ON images.id = favorites.image_id WHERE favorites.email = :email');
$query->bindParam(':email', $current_email);
$query->execute();
$total = $query->fetchColumn();

header("Location: favorites/?id=$current_user_id");
exit();
?>