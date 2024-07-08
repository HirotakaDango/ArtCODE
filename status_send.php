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
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post your status</title>
    <?php include('bootstrapcss.php'); ?>
    <link rel="icon" type="image/png" href="icon/favicon.png">
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container mb-5 mt-4">
      <div class="card bg-body-tertiary shadow rounded-4 border-0">
        <div class="card-body">
          <h5 class="card-title fw-bold">Post Your Status</h5>
          <div class="card mb-3 border-0 shadow rounded bg-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
            <div class="card-body">
              <p class="card-text text-muted fw-medium">
                Share your current thoughts, activities, or updates with your friends and followers. Keep it concise and engaging!
              </p>
            </div>
          </div>
          <form method="post" action="status_send.php">
            <div class="form-floating mb-2">
              <textarea class="form-control rounded-3 border-0 fw-medium" id="desc" name="message" rows="5" style="height: 400px;" oninput="stripHtmlTags(this)" placeholder="Enter your status update" maxlength="44000"></textarea>
              <label for="desc" class="fw-medium">Enter your status update</label>
            </div>
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary fw-bold">Post</button>
              <a href="status.php" class="btn btn-secondary fw-bold">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
