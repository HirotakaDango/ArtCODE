<?php
session_start();

// Get the ID of the selected user from the URL
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Validate the ID (e.g., ensure it's a numeric value)
if (!is_numeric($id)) {
  // Handle invalid ID
  die('Invalid ID');
}

// Check if the user is logged in
if (isset($_SESSION['email'])) {
  // If logged in, redirect to /artist/?id=id
  header("Location: /artist/?id=$id&by=newest");
} else {
  // If not logged in, redirect to /preview/artist/?id=id
  header("Location: /preview/artist/?id=$id&by=newest");
}

// Ensure no further code is executed after redirection
exit();
?>