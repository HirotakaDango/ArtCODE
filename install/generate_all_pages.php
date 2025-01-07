<?php
// Start session and check if the user is logged in
session_start();
require_once('../auth.php');

// Connect to the database
$db = new PDO('sqlite:../database.sqlite');

// Set default numpage value
$default_numpage = 12;

// Update 'numpage' to the default value for all users with an empty or null 'numpage'
$stmt = $db->prepare('
  UPDATE users 
  SET numpage = :numpage 
  WHERE numpage IS NULL OR numpage = ""
');
$stmt->bindParam(':numpage', $default_numpage, PDO::PARAM_INT);
$stmt->execute();

// Redirect to a confirmation page or wherever you need
header('Location: /');
exit();

$db = null; // Close the PDO connection
?>