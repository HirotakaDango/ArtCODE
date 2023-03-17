<?php
  // Start the session
  session_start();

  // Delete token from database
  $token = $_SESSION['token'];
  $sql = "DELETE FROM tokens WHERE token = '$token'";
  // execute SQL statement here

  // Destroy the session
  session_destroy();

  // Unset all cookies
  foreach($_COOKIE as $name => $value) {
    unset($_COOKIE[$name]);
    setcookie($name, '', time() - 3600, '/');
  }

  // Redirect the user to the login page
  header("Location: session.php");
  exit;
?>
