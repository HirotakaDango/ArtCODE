<?php
require_once('../auth.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['messageId'])) {
  $messageId = $_GET['messageId'];

  // Connect to the SQLite database
  $db = new SQLite3('../database.sqlite');

  // Fetch message content for given messageId
  $stmt = $db->prepare("SELECT message FROM messages WHERE id = :messageId");
  $stmt->bindValue(':messageId', $messageId, SQLITE3_INTEGER);
  $result = $stmt->execute();

  if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $messageText = $row['message'];
    echo json_encode(['success' => true, 'messageText' => $messageText]);
  } else {
    echo json_encode(['success' => false, 'messageText' => 'Message not found.']);
  }
} else {
  echo json_encode(['success' => false, 'messageText' => 'Invalid request.']);
}
?>