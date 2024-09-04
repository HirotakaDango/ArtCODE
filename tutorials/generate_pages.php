<?php
// Start session and check if the user is logged in
session_start();
require_once('../auth.php');

// Ensure the user is logged in
if (!isset($_SESSION['email'])) {
  die('User not logged in.');
}

// Get the user's email
$email = $_SESSION['email'];

// Connect to the database
$db = new PDO('sqlite:../database.sqlite');

// Check if 'numpage' column is empty for the user
$stmt = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// If 'numpage' is empty, set it to 12
if ($row === false || $row['numpage'] === null) {
  $stmt = $db->prepare('UPDATE users SET numpage = :numpage WHERE email = :email');
  $default_numpage = 12;
  $stmt->bindParam(':numpage', $default_numpage, PDO::PARAM_INT);
  $stmt->bindParam(':email', $email, PDO::PARAM_STR);
  $stmt->execute();
}

// Redirect to a confirmation page or wherever you need
header('Location: welcome.php');
exit();

$db = null; // Close the PDO connection
?>