<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}

// Connect to the database
$db = new SQLite3('database.sqlite');

// Get the email of the current user
$email = $_SESSION['email'];

// Get the emails of the users that the current user is following
$following_query = $db->prepare("SELECT following_email FROM following WHERE follower_email = :email");
$following_query->bindValue(':email', $email, SQLITE3_TEXT);
$following_result = $following_query->execute();

// Create an array to store the emails of the users that the current user is following
$following_emails = array();
while ($row = $following_result->fetchArray(SQLITE3_ASSOC)) {
  $following_emails[] = $row['following_email'];
}

// Join the users table and the status table to get the messages from the users that the current user is following
$status_query = $db->prepare("SELECT users.email, users.artist, status.message, status.date, status.id FROM users JOIN status ON users.email = status.email WHERE users.email IN (".implode(',', array_fill(0, count($following_emails), '?')).") ORDER BY status.date DESC");
foreach ($following_emails as $i => $following_email) {
  $status_query->bindValue($i+1, $following_email, SQLITE3_TEXT);
}
$status_result = $status_query->execute();

// Create an array to store the messages
$messages = array();
while ($row = $status_result->fetchArray(SQLITE3_ASSOC)) {
  $messages[] = $row;
}

// Handle the delete button
if(isset($_POST['delete'])) {
  $id = $_POST['id'];
  $delete_query = $db->prepare("DELETE FROM status WHERE id = :id AND email = :email");
  $delete_query->bindValue(':id', $id, SQLITE3_INTEGER);
  $delete_query->bindValue(':email', $email, SQLITE3_TEXT);
  $delete_query->execute();

  // Refresh the page after deleting the message
  header('Location: ' . $_SERVER['PHP_SELF']);
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Status</title>
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container-fluid mt-2">
    <center><a href="status_send.php" type="button" class="form-control btn-sm text-decoration-none text-white bg-primary fw-bold mb-2"><i class="bi bi-send-fill"></i> write something</a></center>
    <div class="messages">
      <?php foreach ($messages as $message): ?>
        <div class="card mb-3">
          <div class="card-header fw-bold"><?php echo $message['artist']; ?>, <span class="text-muted"><?php echo $message['date']; ?></span></div>
            <div class="card-body">
              <p class="card-text text-secondary fw-bold"><?php echo $message['message']; ?></p>
              <?php if ($message['email'] == $email): ?>
                <form method="post" action="">
                  <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                  <button type="submit" name="delete" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i></button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>