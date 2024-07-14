<?php
require_once('../auth.php');

// Verify session email is set
if (!isset($_SESSION['email'])) {
  die('You need to log in to access this page.');
}

$email = $_SESSION['email'];

// Connect to the SQLite database using parameterized query
$db = new SQLite3('../database.sqlite');

// Query to fetch latest message for each user who has messaged or been messaged by the current user
$stmt = $db->prepare("SELECT u.id, u.artist, u.pic, m.message, m.date, m.email as sender_email, su.artist as sender_artist
                      FROM users u
                      JOIN (
                          SELECT MAX(id) as max_id, CASE
                                      WHEN email = :email THEN to_user_email
                                      ELSE email
                                  END as other_user
                          FROM messages
                          WHERE email = :email OR to_user_email = :email
                          GROUP BY other_user
                      ) latest_msg ON u.email = latest_msg.other_user
                      LEFT JOIN messages m ON latest_msg.max_id = m.id
                      LEFT JOIN users su ON m.email = su.email
                      WHERE m.email = :email OR m.to_user_email = :email
                      ORDER BY m.date DESC");  // Order by latest message date in descending order
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();

// Prepare an array to hold latest messages
$latest_messages = [];

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  // Format the date
  $formatted_date = date('l, j F Y | H:i', strtotime($row['date']));
  
  // Build each latest message entry with a clickable link
  $latest_message = [
    'id' => $row['id'],
    'artist' => htmlspecialchars($row['artist']),
    'pic' => htmlspecialchars($row['pic']),  // Include profile picture
    'message' => htmlspecialchars($row['message']),
    'date' => $formatted_date,  // Use formatted date
    'sender_artist' => htmlspecialchars($row['sender_artist']) // Include sender's artist name
  ];

  $latest_messages[] = $latest_message;
}

// Unset sender_email to hide it from the JSON response
foreach ($latest_messages as &$message) {
  unset($message['sender_email']);
}
unset($message); // unset the reference

// Output latest messages as JSON
header('Content-Type: application/json');
echo json_encode($latest_messages);
?>