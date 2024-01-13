<?php
require_once('auth.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Connect to the database
  $db = new SQLite3('database.sqlite');

  // Get the email and message from the form
  $email = $_SESSION['email'];
  $message = $_POST['message'];

  // Sanitize the message
  $message = filter_var($message, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $message = nl2br($message);

  // Insert the status update into the database
  $insert_query = $db->prepare("INSERT INTO status (email, message, date) VALUES (:email, :message, :date)");
  $insert_query->bindValue(':email', $email, SQLITE3_TEXT);
  $insert_query->bindValue(':message', $message, SQLITE3_TEXT);
  $insert_query->bindValue(':date', date('Y-m-d'), SQLITE3_TEXT);
  $insert_query->execute();

  // Redirect the user to status.php
  header("Location: status.php");
  exit;
}
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Post your status</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container-fluid mt-2">
      <form method="post" action="status_send.php">
        <div class="form-floating mb-2">
          <textarea class="form-control" name="message" placeholder="Enter your status update" id="message" style="height: 400px"></textarea>
          <label class="text-secondary" for="message">Status Update</label>
        </div>
        <div class="btn-group w-100 gap-2">
          <button type="submit" class="btn btn-primary fw-bold w-50 rounded">send</button>
          <a href="status.php" class="btn btn-danger fw-bold w-50 rounded">cancel</a>
        </div>
      </form>
    </div>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>