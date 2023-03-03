<?php
session_start();

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Create the users table if it doesn't exist
$db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT, password TEXT, artist TEXT, pic TEXT, desc TEXT, bgpic TEXT, token TEXT)");

// Check if the user is logging in or registering
if (isset($_POST['login'])) {
  $username = substr(htmlspecialchars($_POST['username']), 0, 40);
  $password = substr(htmlspecialchars($_POST['password']), 0, 40);

  // Check if the user exists in the database
  $stmt = $db->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
  $stmt->bindValue(':username', $username, SQLITE3_TEXT);
  $stmt->bindValue(':password', $password, SQLITE3_TEXT);
  $result = $stmt->execute();
  $user = $result->fetchArray();
  if ($user) {
    // Generate a new token and store it in the database
    $token = bin2hex(random_bytes(16));
    $stmt = $db->prepare("UPDATE users SET token = :token WHERE username = :username");
    $stmt->bindValue(':token', $token, SQLITE3_TEXT);
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->execute();
    
    // Store the token in a cookie
    setcookie('token', $token, time() + (7 * 24 * 60 * 60), '/');
    
    // Store the username in a cookie
    setcookie('username', $username, time() + (7 * 24 * 60 * 60), '/');
    
    // Store the username in the session for future use
    $_SESSION['username'] = $username;
    
    // Redirect the user to the homepage
    header("Location: index.php");
    exit;
  } else {
    echo "Incorrect username or password.";
  }
} elseif (isset($_POST['register'])) {
  $username = substr(htmlspecialchars($_POST['username']), 0, 40);
  $password = substr(htmlspecialchars($_POST['password']), 0, 40);
  $artist = substr(htmlspecialchars($_POST['artist']), 0, 40);

  // Check if the username is already taken
  $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
  $stmt->bindValue(':username', $username, SQLITE3_TEXT);
  $result = $stmt->execute();
  $user = $result->fetchArray();
  if ($user) {
    echo "Username already taken.";
  } else {
    // Add the new user to the database
    $stmt = $db->prepare("INSERT INTO users (username, password, artist) VALUES (:username, :password, :artist)");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':password', $password, SQLITE3_TEXT);
    $stmt->bindValue(':artist', $artist, SQLITE3_TEXT);
    $stmt->execute();
    
    // Generate a unique session ID and store it in a cookie
    $session_id = uniqid();
    setcookie('session_id', $session_id, time() + (7 * 24 * 60 * 60), '/');
    
    // Store the username in a cookie
    setcookie('username', $username, time() + (7 * 24 * 60 * 60), '/');
    
    // Store the username in the session for future use
    $_SESSION['username'] = $username;
    
    // Redirect the user to the homepage
    header("Location: index.php");
    exit;
  }
} else {
  // Check if the session ID cookie exists and restore the session if it does
  if (isset($_COOKIE['session_id'])) {
    session_id(htmlspecialchars($_COOKIE['session_id']));
  }
  if (isset($_COOKIE['username'])) {
    $_SESSION['username'] = htmlspecialchars($_COOKIE['username']);
  }
}
?>
