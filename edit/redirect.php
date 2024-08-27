<?php
// Check if 'back' parameter is set and not empty
if (isset($_GET['back']) && !empty($_GET['back'])) {
  $backUrl = $_GET['back'];
} else {
  // Determine the current URL scheme
  $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
  $host = $_SERVER['HTTP_HOST'];
  $backUrl = $scheme . '://' . $host . '/profile.php';
}

// Redirect to the determined URL
header("Location: " . $backUrl);
exit();
?>