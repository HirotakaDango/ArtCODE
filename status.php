<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: session.php");
  exit;
}

// Connect to the database
$db = new SQLite3('database.sqlite');

// Get the username of the current user
$username = $_SESSION['username'];

// Get the usernames of the users that the current user is following
$following_query = $db->prepare("SELECT following_username FROM following WHERE follower_username = :username");
$following_query->bindValue(':username', $username, SQLITE3_TEXT);
$following_result = $following_query->execute();

// Create an array to store the usernames of the users that the current user is following
$following_usernames = array();
while ($row = $following_result->fetchArray(SQLITE3_ASSOC)) {
  $following_usernames[] = $row['following_username'];
}

// Join the users table and the status table to get the messages from the users that the current user is following
$status_query = $db->prepare("SELECT users.username, users.artist, status.message, status.date, status.id FROM users JOIN status ON users.username = status.username WHERE users.username IN (".implode(',', array_fill(0, count($following_usernames), '?')).") ORDER BY status.date DESC");
foreach ($following_usernames as $i => $following_username) {
  $status_query->bindValue($i+1, $following_username, SQLITE3_TEXT);
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
  $delete_query = $db->prepare("DELETE FROM status WHERE id = :id AND username = :username");
  $delete_query->bindValue(':id', $id, SQLITE3_INTEGER);
  $delete_query->bindValue(':username', $username, SQLITE3_TEXT);
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
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container mt-2">
    <center><a href="status_send.php" type="button" class="form-control btn-sm text-decoration-none text-white bg-primary fw-bold mb-2"><i class="bi bi-send-fill"></i> write something</a></center>
    <div class="messages">
      <?php foreach ($messages as $message): ?>
        <div class="card mb-3">
          <div class="card-header fw-bold"><?php echo $message['artist']; ?> <span class="text-muted"><?php echo $message['date']; ?></span></div>
            <div class="card-body">
              <p class="card-text text-secondary fw-bold"><?php echo $message['message']; ?></p>
              <?php if ($message['username'] == $username): ?>
                <form method="post" action="">
                  <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                  <button type="submit" name="delete" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>