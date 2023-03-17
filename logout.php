<?php
  // Start the session
  session_start();

  // Destroy the session
  session_destroy();

  // Unset the username cookie
  if(isset($_COOKIE['username'])) {
    unset($_COOKIE['username']);
    setcookie('username', '', time() - 3600, '/');
  }

  // Redirect the user to the login page
  header("Location: session.php");
  exit;
?>
