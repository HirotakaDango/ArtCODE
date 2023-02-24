<?php
session_start();

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Create the users table if it doesn't exist
$db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT, password TEXT)");

// Check if the user is logging in or registering
if (isset($_POST['login'])) {
  $username = $_POST['username'];
  $password = $_POST['password'];

  // Check if the user exists in the database
  $stmt = $db->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
  $stmt->bindValue(':username', $username);
  $stmt->bindValue(':password', $password);
  $result = $stmt->execute();
  $user = $result->fetchArray();
  if ($user) {
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
  } else {
    echo "Incorrect username or password.";
  }
} elseif (isset($_POST['register'])) {
  $username = $_POST['username'];
  $password = $_POST['password'];

  // Check if the username is already taken
  $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
  $stmt->bindValue(':username', $username);
  $result = $stmt->execute();
  $user = $result->fetchArray();
  if ($user) {
    echo "Username already taken.";
  } else {
    // Add the new user to the database
    $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':password', $password);
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
    session_id($_COOKIE['session_id']);
  }
  if (isset($_COOKIE['username'])) {
    $_SESSION['username'] = $_COOKIE['username'];
  }
}
?>
