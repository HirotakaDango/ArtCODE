<?php
// Join the users table and the status table to get the messages from the users that the current user is following
$status_query = $db->prepare("SELECT users.email, users.artist, users.pic, users.id AS userid, status.message, status.date, status.id, COUNT(favorites_status.id) AS like_count FROM users JOIN status ON users.email = status.email LEFT JOIN favorites_status ON status.id = favorites_status.status_id AND favorites_status.email = :current_user_email WHERE users.email IN (".implode(',', array_fill(0, count($following_emails), '?')).") GROUP BY status.id ORDER BY status.id ASC");

foreach ($following_emails as $i => $following_email) {
  $status_query->bindValue($i+1, $following_email, PDO::PARAM_STR);
}

if (!$status_query->execute()) {
  // Handle the error, you can output or log the error message
  die("Error executing query: " . implode(" ", $status_query->errorInfo()));
}

// Create an array to store the messages
$messages = array();
while ($row = $status_query->fetch(PDO::FETCH_ASSOC)) {
  $messages[] = $row;
}
?>

    <div class="container mt-2">
      <?php foreach ($messages as $message): ?>
        <?php include('status_card.php'); ?>
      <?php endforeach; ?>
    </div>
    <div class="mt-5"></div>