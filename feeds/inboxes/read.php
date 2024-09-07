<?php
require_once('../../auth.php');

// Connect to the SQLite database
$db = new SQLite3('../../database.sqlite');

$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT) : 0;

if ($id) {
  // Fetch the full email content
  $stmt = $db->prepare("SELECT * FROM inboxes WHERE id = :id");
  $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
  $result = $stmt->execute();
  $email = $result->fetchArray(SQLITE3_ASSOC);

  if ($email) {
    // Update the email's read status to 'yes'
    $updateStmt = $db->prepare("UPDATE inboxes SET read = 'yes' WHERE id = :id");
    $updateStmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $updateStmt->execute();
  }
} else {
  // Redirect if no ID is provided
  header('Location: index.php');
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Read Email</title>
  <?php include('../../bootstrapcss.php'); ?>
</head>
<body>
  <div class="container mt-4">
    <h1 class="mb-4">Read Email</h1>
    
    <div class="card">
      <div class="card-body">
        <h5 class="card-title"><?php echo htmlspecialchars($email['title'], ENT_QUOTES, 'UTF-8'); ?></h5>
        <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($email['email'], ENT_QUOTES, 'UTF-8'); ?> → <?php echo htmlspecialchars($email['to_email'], ENT_QUOTES, 'UTF-8'); ?></h6>
        <p class="card-text"><?php echo nl2br(htmlspecialchars($email['post'], ENT_QUOTES, 'UTF-8')); ?></p>
        <p class="card-text"><small class="text-muted">Date: <?php echo htmlspecialchars($email['date'], ENT_QUOTES, 'UTF-8'); ?></small></p>
        <a href="/feeds/inboxes/" class="btn btn-primary">Back to Inbox</a>
      </div>
    </div>
  </div>
  <?php include('../../bootstrapjs.php'); ?>
</body>
</html>
