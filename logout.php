<?php
  // Start the session
  session_start();

  // Delete token from database
  $token = isset($_SESSION['token']) ? $_SESSION['token'] : '';
  $db = new SQLite3('database.sqlite');
  $sql = "DELETE FROM users WHERE token = '$token'";
  $db->exec($sql);

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
