<?php
session_start();

if (!isset($_SESSION['email'])) {
  // Check if the "remember me" cookie exists
  if (isset($_COOKIE['remember'])) {
    // Retrieve the user's email and token from the cookie
    $cookieData = json_decode($_COOKIE['remember'], true);
        
    if ($cookieData && isset($cookieData['email']) && isset($cookieData['token'])) {
      $email = $cookieData['email'];
      $token = $cookieData['token'];

      // Validate the user's session
      $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
      $stmt->bindValue(':email', $email, SQLITE3_TEXT);
      $result = $stmt->execute();
      $user = $result->fetchArray();

      if ($user && $token === $user['token']) {
        // Set up the session
        $_SESSION['email'] = $email;

        // Generate a new token and update the database
        $newToken = bin2hex(random_bytes(32));
        $stmt = $db->prepare("UPDATE users SET token = :newToken WHERE email = :email");
        $stmt->bindValue(':newToken', $newToken, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->execute();

        // Set the new token cookie (optional)
        $cookieData['token'] = $newToken;
        setcookie('remember', json_encode($cookieData), time() + (7 * 24 * 60 * 60), '/');
      }
    }
  }

  // If the user is still not authenticated, redirect to the login page
  if (!isset($_SESSION['email'])) {
    $url = "http://" . $_SERVER['HTTP_HOST'] . "/session.php"; // Construct the absolute URL
    header("Location: $url");
    exit;
  }
}
?>
