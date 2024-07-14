<?php
require_once('../auth.php');

// Verify session email is set
if (!isset($_SESSION['email'])) {
  die('You need to log in to access this page.');
}

$email = $_SESSION['email'];

// Connect to the SQLite database using parameterized query
$db = new SQLite3('../database.sqlite');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
  $message_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

  if (!$message_id) {
    die('Invalid message ID.');
  }
  
  $stmt = $db->prepare("DELETE FROM messages WHERE id = :id AND (email = :email1 OR to_user_email = :email2)");
  $stmt->bindValue(':id', $message_id, SQLITE3_INTEGER);
  $stmt->bindValue(':email1', $email, SQLITE3_TEXT);
  $stmt->bindValue(':email2', $email, SQLITE3_TEXT);
  $stmt->execute();
  
  echo "Message deleted successfully";
}
?>