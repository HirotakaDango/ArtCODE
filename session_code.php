<?php
session_start();

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Create the users table if it doesn't exist
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT, password TEXT, artist TEXT, pic TEXT, desc TEXT, bgpic TEXT, token TEXT, twitter TEXT, pixiv TEXT, other TEXT)");
$stmt->execute();
 
if (isset($_POST['login'])) {
  $username = filter_var($_POST['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $password = filter_var($_POST['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

  // Check if the email and password fields are not empty
  if (empty($username) || empty($password)) {
    echo "Please enter both username and password.";
  } else {
    // Check if the user exists in the database
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':password', $password, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray();
    if ($user) {
      // Generate a unique session ID and token and store them in cookies
      $session_id = uniqid();
      $token = bin2hex(random_bytes(32));
      setcookie('session_id', $session_id, time() + (7 * 24 * 60 * 60), '/');
      setcookie('token', $token, time() + (7 * 24 * 60 * 60), '/');
    
      // Store the session ID and username in the session for future use
      $_SESSION['session_id'] = $session_id;
      $_SESSION['username'] = $username;
    
      // Update the user's token in the database
      $stmt = $db->prepare("UPDATE users SET token = :token WHERE username = :username");
      $stmt->bindValue(':token', $token, SQLITE3_TEXT);
      $stmt->bindValue(':username', $username, SQLITE3_TEXT);
      $stmt->execute();
    
      // Redirect the user to the homepage
      header("Location: index.php");
      exit;
    } else {
      echo "Incorrect username or password.";
    }
  }
} elseif (isset($_POST['register'])) {
  $username = substr(htmlspecialchars(trim($_POST['username'])), 0, 40);
  $password = substr(htmlspecialchars(trim($_POST['password'])), 0, 40);
  $artist = substr(htmlspecialchars(trim($_POST['artist'])), 0, 40);

  // Check if the username and password fields are empty
  if (empty($username) || empty($password)) {
    echo "Username and password are required.";
    exit;
  }

  // Sanitize the user input
  $username = filter_var($username, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $password = filter_var($password, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $artist = filter_var($artist, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

  // Check if the username is already taken
  $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
  $stmt->bindValue(':username', $username, SQLITE3_TEXT);
  $result = $stmt->execute();
  $user = $result->fetchArray();
  if ($user) {
    echo "Username already taken.";
    exit;
  } else {
    // Add the new user to the database
    $stmt = $db->prepare("INSERT INTO users (username, password, artist) VALUES (:username, :password, :artist)");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':password', $password, SQLITE3_TEXT);
    $stmt->bindValue(':artist', $artist, SQLITE3_TEXT);
    $stmt->execute();

    // Generate a unique token and store it in the database for the user
    $token = bin2hex(random_bytes(16));
    $stmt = $db->prepare("UPDATE users SET token = :token WHERE username = :username");
    $stmt->bindValue(':token', $token, SQLITE3_TEXT);
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->execute();

    // Store the token in a cookie
    setcookie('token', $token, time() + (7 * 24 * 60 * 60), '/');

    // Generate a unique session ID and store it in a cookie
    $session_id = uniqid();
    setcookie('session_id', $session_id, time() + (7 * 24 * 60 * 60), '/');

    // Store the username in a cookie
    setcookie('username', $username, time() + (7 * 24 * 60 * 60), '/');

    // Store the username in the session for future use
    $_SESSION['username'] = $username;

    // Redirect the user to the homepage
    header("Location: regpic.php");
    exit;
  }
} else {
  // Check if the session ID cookie exists and restore the session if it does
  if (isset($_COOKIE['session_id'])) {
    $session_id = substr(htmlspecialchars($_COOKIE['session_id']), 0, 13);
    $session_id = filter_var($session_id, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
    session_id($session_id);

    // Check if the user has a valid session token
    if (isset($_SESSION['username'])) {
      $username = $_SESSION['username'];
      $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
      $stmt->bindValue(':username', $username, SQLITE3_TEXT);
      $result = $stmt->execute();
      $user = $result->fetchArray();
      if ($user && isset($_COOKIE['token']) && $_COOKIE['token'] === $user['token']) {
        // If the user has a valid token, update the session and token cookies
        $session_id = session_id();
        $token = $_COOKIE['token'];
        setcookie('session_id', $session_id, time() + (7 * 24 * 60 * 60), '/');
        setcookie('token', $token, time() + (7 * 24 * 60 * 60), '/');
      } else {
        // If the user does not have a valid token, clear the session and redirect to login
        session_unset();
        session_destroy();
        setcookie('session_id', '', time() - 3600, '/');
        setcookie('token', '', time() - 3600, '/');
        header("Location: session.php");
        exit;
      }
    }
  }
}
?>