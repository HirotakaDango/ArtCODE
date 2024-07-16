<?php
require_once('../auth.php');

// Connect to the SQLite database
$db = new SQLite3('../database.sqlite');

// Query to get the latest message from the group chat
$query = "SELECT email, group_message, date FROM chat_group ORDER BY date DESC LIMIT 1";
$result = $db->query($query);

$latestMessage = $result->fetchArray(SQLITE3_ASSOC);

if ($latestMessage) {
  // Fetch the artist name and profile picture from the users table
  $stmt = $db->prepare("SELECT artist, pic FROM users WHERE email = :email");
  $stmt->bindValue(':email', $latestMessage['email'], SQLITE3_TEXT);
  $userResult = $stmt->execute();
  $user = $userResult->fetchArray(SQLITE3_ASSOC);

  if ($user) {
    $latestMessage['artist'] = $user['artist'];
    $latestMessage['pic'] = $user['pic'] ? $user['pic'] : '../icon/propic.png'; // Default image if no picture
  } else {
    $latestMessage['artist'] = 'Unknown';
    $latestMessage['pic'] = '../icon/propic.png'; // Default image
  }

  // Format the date
  $latestMessage['date'] = date('l, j F Y | H:i', strtotime($latestMessage['date']));

  echo json_encode($latestMessage);
} else {
  echo json_encode([]);
}
?>