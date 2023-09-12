<?php
require_once('auth.php');

// Check if the forum ID is provided in the URL
if (!isset($_GET['forumid'])) {
  header('Location: forum.php'); // Redirect to forum.php if forumid is not provided
  exit();
}

// Assuming you have already connected to the database
// Replace 'your_connection_code_here' with your actual database connection code
$connection = new SQLite3('database.sqlite'); // Replace 'database.sqlite' with your actual SQLite database file name

// Get the forum ID from the URL
$forum_id = $_GET['forumid'];

// Retrieve the comment from the database based on the forum ID
$statement = $connection->prepare('SELECT * FROM forum WHERE id = :forum_id');
$statement->bindValue(':forum_id', $forum_id, SQLITE3_INTEGER);
$result = $statement->execute();

if ($result) {
  $comment = $result->fetchArray(SQLITE3_ASSOC);

  // Check if the forum with the given ID exists
  if (!$comment) {
    header('Location: forum.php'); // Redirect to forum.php if forum with given ID doesn't exist
    exit();
  }
} else {
  // Redirect to forum.php if there was an error fetching the comment from the database
  header('Location: forum.php');
  exit();
}

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get the updated comment from the form submission
  $updated_comment = $_POST['comment'];

  // Update the comment in the database
  $update_statement = $connection->prepare('UPDATE forum SET comment = :updated_comment WHERE id = :forum_id');
  $update_statement->bindValue(':updated_comment', $updated_comment, SQLITE3_TEXT);
  $update_statement->bindValue(':forum_id', $forum_id, SQLITE3_INTEGER);
  $update_result = $update_statement->execute();

  if ($update_result) {
    // Redirect back to the forum.php page after successful update
    header('Location: forum.php');
    exit();
  } else {
    // Handle the error if the update fails (you can add more error handling if required)
    echo "Error updating the comment.";
  }
}

?>

<!DOCTYPE html>
<html>
  <head>
    <title>Edit Comment</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="modal-dialog" role="document">
      <div class="modal-content border-3 border-bottom">
        <div class="modal-body p-4">
          <h5 class="mb-0 fw-bold text-center">Edit Comment</h5>
        </div>
      </div>
    </div> 
    <div class="container-fluid mt-2">
      <form method="post">
        <div class="mb-3">
          <textarea class="form-control" id="comment" name="comment" rows="10" oninput="stripHtmlTags(this)" required><?php echo htmlspecialchars($comment['comment']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-bold">Save</button>
      </form>
    </div>
    <div class="ms-2 d-none-sm position-fixed top-50 start-0 translate-middle-y">
      <button class="btn btn-primary rounded-pill fw-bold btn-md" onclick="goBack()">
        <i class="bi bi-arrow-left-circle-fill"></i> Back
      </button>
    </div>
    <script>
      function goBack() {
        window.location.href = "forum.php"; // Redirect back to forum.php
      }
    </script> 
  </body>
</html>
