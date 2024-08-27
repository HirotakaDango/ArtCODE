<?php
require_once('auth.php');

// Connect to SQLite database
$db = new PDO('sqlite:database.sqlite');

// Retrieve image details
$id = $_GET['id'];
$back = $_GET['back'];

header("Location: edit/?id=" . $id . "&back=" . $back);
exit();
?>