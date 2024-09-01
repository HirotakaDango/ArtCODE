<?php
session_start();

// Connect to the SQLite database
$defaultDB = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/database.sqlite');

$toUrl = 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

if (!isset($_SESSION['email'])) {
  // Check if the "remember me" cookie exists
  if (isset($_COOKIE['remember'])) {
    // Retrieve the user's email and token from the cookie
    $cookieData = json_decode($_COOKIE['remember'], true);
    
    if ($cookieData && isset($cookieData['email']) && isset($cookieData['token'])) {
      $email = $cookieData['email'];
      $token = $cookieData['token'];

      // Validate the user's session
      $stmt = $defaultDB->prepare("SELECT * FROM users WHERE email = :email");
      $stmt->bindValue(':email', $email, SQLITE3_TEXT);
      $result = $stmt->execute();
      $user = $result->fetchArray();

      if ($user && $token === $user['token']) {
        // Set up the session
        $_SESSION['email'] = $email;

        // Generate a new token and update the database
        $newToken = bin2hex(random_bytes(32));
        $stmt = $defaultDB->prepare("UPDATE users SET token = :newToken WHERE email = :email");
        $stmt->bindValue(':newToken', $newToken, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->execute();

        // Set the new token cookie (optional)
        $cookieData['token'] = $newToken;
        setcookie('remember', json_encode($cookieData), time() + (7 * 24 * 60 * 60), '/');
      }
    }
  }

  // Check if the user is trying to access a protected page
  $protectedPages = [
    $_SERVER['DOCUMENT_ROOT'] . '/full_view.php',
    $_SERVER['DOCUMENT_ROOT'] . '/simple_view.php',
    $_SERVER['DOCUMENT_ROOT'] . '/view.php',
    $_SERVER['DOCUMENT_ROOT'] . '/simplest_view.php'
  ];
  $currentFilePath = realpath(__FILE__);
  $requestedFilePath = realpath($_SERVER['DOCUMENT_ROOT'] . parse_url($toUrl, PHP_URL_PATH));

  if (in_array($requestedFilePath, $protectedPages)) {
    // Extract the artwork ID from the query parameters
    parse_str(parse_url($toUrl, PHP_URL_QUERY), $queryParams);
    $artworkId = isset($queryParams['artworkid']) ? $queryParams['artworkid'] : '';

    // Redirect to the preview image page
    $redirectUrl = "preview/image.php?artworkid=" . urlencode($artworkId);
    header("Location: $redirectUrl");
    exit;
  } else {
    // Redirect to the login page with the current URL
    $loginUrl = "http://" . $_SERVER['HTTP_HOST'] . "/session.php?tourl=" . urlencode($toUrl);
    header("Location: $loginUrl");
    exit;
  }
}
?>