<?php
require_once('auth.php');

$email = $_SESSION['email'];

// Connect to SQLite database
$db = new PDO('sqlite:database.sqlite');

// Get the ID of the selected user from the URL
$id = $_GET['id'];
$query = $db->prepare('SELECT artist, `desc`, `bgpic`, pic, twitter, pixiv, other, region, joined, born, email FROM users WHERE id = :id');
$query->bindParam(':id', $id);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);
$artist = $user['artist'];
$desc = $user['desc'];
$pic = $user['pic'];
$bgpic = $user['bgpic'];
$twitter = $user['twitter'];
$pixiv = $user['pixiv'];
$other = $user['other'];
$region = $user['region'];
$joined = $user['joined'];
$born = $user['born'];

header("Location: artist/?id=$id&by=newest");
exit();
?>