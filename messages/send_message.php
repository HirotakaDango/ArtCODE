<?php
require_once('../auth.php');

// Verify session email is set
if (!isset($_SESSION['email'])) {
    die('You need to log in to access this page.');
}

$email = $_SESSION['email'];

// Connect to the SQLite database using parameterized query
$db = new SQLite3('../database.sqlite');

// Validate and sanitize the user_id and message from the POST request
$user_id = filter_input(INPUT_POST, 'userid', FILTER_VALIDATE_INT);
$message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

if (!$user_id || !$message) {
    die('Invalid request.');
}

// Insert message into the database
$stmt = $db->prepare("INSERT INTO messages (email, message, date, to_user_email) VALUES (:from_email, :message, datetime('now'), (SELECT email FROM users WHERE id = :to_user_id))");
$stmt->bindValue(':from_email', $email, SQLITE3_TEXT);
$stmt->bindValue(':message', $message, SQLITE3_TEXT);
$stmt->bindValue(':to_user_id', $user_id, SQLITE3_INTEGER);
$stmt->execute();

// Respond with success
echo json_encode(['success' => true]);
?>