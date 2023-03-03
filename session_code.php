<?php
session_start();

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Create the users table if it doesn't exist
$db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT, password TEXT, artist TEXT, pic TEXT, desc TEXT, bgpic TEXT)");

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
  
    // Store the username in the session for future use
    $_SESSION['username'] = $username;
  
    // Redirect the user to the homepage
    header("Location: index.php");
    exit;
  }
}

// The session ID cookie is not valid, so do not restore the session
unset($_SESSION['username']);
?>
