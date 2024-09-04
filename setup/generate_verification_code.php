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

// Function to generate a unique verification code
function generateVerificationCode($length = 20) {
  return substr(bin2hex(random_bytes($length)), 0, $length);
}

// Function to check if the verification code is unique
function isCodeUnique($db, $code) {
  $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE verification_code = :verification_code');
  $stmt->bindParam(':verification_code', $code, PDO::PARAM_STR);
  $stmt->execute();
  $count = $stmt->fetchColumn();
  return $count == 0;
}

// Generate and assign a unique verification code
do {
  $verificationCode = generateVerificationCode();
} while (!isCodeUnique($db, $verificationCode));

// Update the user's verification_code
$stmt = $db->prepare('UPDATE users SET verification_code = :verification_code WHERE email = :email');
$stmt->bindParam(':verification_code', $verificationCode, PDO::PARAM_STR);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->execute();

// Redirect to a confirmation page or wherever you need
header('Location: set_region.php'); // Adjust this to your desired redirect
exit();

$db = null; // Close the PDO connection
?>