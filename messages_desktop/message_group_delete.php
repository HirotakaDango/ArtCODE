<?php
require_once('../auth.php');

// Verify session email is set
if (!isset($_SESSION['email'])) {
  die('You need to log in to access this page.');
}

$email = $_SESSION['email'];

// Connect to the SQLite database
$db = new SQLite3('../database.sqlite');

// Handle message deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $messageId = filter_input(INPUT_POST, 'messageId', FILTER_VALIDATE_INT);

  if ($messageId) {
    $stmt = $db->prepare("DELETE FROM chat_group WHERE id = :messageId AND email = :email");
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