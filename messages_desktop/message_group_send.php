<?php
require_once('../auth.php');

// Verify session email is set
if (!isset($_SESSION['email'])) {
  die('You need to log in to access this page.');
}

$email = $_SESSION['email'];

// Connect to the SQLite database
$db = new SQLite3('../database.sqlite');

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

  if ($message) {
    $stmt = $db->prepare("INSERT INTO chat_group (email, group_message, date) VALUES (:email, :message, datetime('now'))");
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':message', $message, SQLITE3_TEXT);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
  } else {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty.']);
    exit;
  }
}
?>