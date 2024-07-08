<?php
// Query to get statuses from users that the current user is following
$status_query = $db->prepare("
  SELECT users.email, users.artist, users.pic, users.id AS userid, status.message, status.date, status.id, 
         COALESCE(favorites_status.like_count, 0) AS like_count
  FROM users
  JOIN status ON users.email = status.email
  LEFT JOIN (
    SELECT status_id, COUNT(*) AS like_count
    FROM favorites_status
    GROUP BY status_id
  ) AS favorites_status ON status.id = favorites_status.status_id
  WHERE users.email IN (".implode(',', array_fill(0, count($following_emails), '?')).")
  ORDER BY like_count DESC
");

foreach ($following_emails as $i => $following_email) {
  $status_query->bindValue($i + 1, $following_email, PDO::PARAM_STR);
}

if (!$status_query->execute()) {
  die("Error executing query: " . implode(" ", $status_query->errorInfo()));
}

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