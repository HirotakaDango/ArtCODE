<?php
require_once('../auth.php');

// Verify session email is set
if (!isset($_SESSION['email'])) {
  die('You need to log in to access this page.');
}

$email = $_SESSION['email'];

// Connect to the SQLite database
$db = new SQLite3('../database.sqlite');

// Handle message editing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $messageId = filter_input(INPUT_POST, 'editMessageId', FILTER_VALIDATE_INT);
  $newMessage = filter_input(INPUT_POST, 'editMessageText', FILTER_SANITIZE_STRING);

  if ($messageId && $newMessage) {
    $stmt = $db->prepare("UPDATE chat_group SET group_message = :newMessage WHERE id = :messageId AND email = :email");
    $stmt->bindValue(':newMessage', $newMessage, SQLITE3_TEXT);
    $stmt->bindValue(':messageId', $messageId, SQLITE3_INTEGER);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->execute();

    echo json_encode(['success' => true]);
    exit;
  } else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
  }
}
?>