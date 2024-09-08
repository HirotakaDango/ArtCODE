<?php
// Fetch emails with pagination and search, only where 'read' is 'yes'
$query = "
  SELECT * FROM inboxes
  WHERE to_email = :email
    AND title LIKE :search
    AND read = 'yes'
  ORDER BY id DESC
  LIMIT :perPage OFFSET :offset
";

$stmt = $db->prepare($query);
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$stmt->bindValue(':search', '%' . $search . '%', SQLITE3_TEXT);
$stmt->bindValue(':perPage', $perPage, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$emails = $stmt->execute();
?>

<?php include('inbox_card.php'); ?>