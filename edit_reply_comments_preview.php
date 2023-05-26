<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  // Redirect to the login page if not
  header('Location: session.php');
  exit();
}

// Open the SQLite database
$db = new SQLite3('database.sqlite');

$imageid = $_GET['imageid'];

// Check if the reply ID is provided
if (isset($_GET['reply_id'])) {
  $replyId = $_GET['reply_id'];

  // Get the reply details from the database
  $stmt = $db->prepare('SELECT * FROM reply_comments WHERE id = :reply_id');
  $stmt->bindValue(':reply_id', $replyId, SQLITE3_INTEGER);
  $reply = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

  // Check if the reply exists and belongs to the current user
  if ($reply && $reply['email'] === $_SESSION['email']) {
    // Check if the form is submitted
    if (isset($_POST['reply'])) {
      $newReply = trim($_POST['reply']);

      // Check if the new reply is not empty
      if (!empty($newReply)) {
        // Prepare the reply text by removing special characters and converting newlines to <br> tags
        $newReply = nl2br(filter_var($newReply, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));

        // Update the reply in the database
        $stmt = $db->prepare('UPDATE reply_comments SET reply = :new_reply WHERE id = :reply_id');
        $stmt->bindValue(':new_reply', $newReply, SQLITE3_TEXT);
        $stmt->bindValue(':reply_id', $replyId, SQLITE3_INTEGER);
        $stmt->execute();

        // Redirect back to the reply preview page
        $redirectUrl = 'reply_comments_preview.php?imageid=' . urlencode($imageid) . '&comment_id=' . urlencode($reply['comment_id']);
        header('Location: ' . $redirectUrl);
        exit();
      } else {
        // Handle the case where the new reply is empty
        // Display an error message or take appropriate action
      }
    }
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Reply Section</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="modal-dialog" role="document">
      <div class="modal-content border-3 border-bottom">
        <div class="modal-body p-4">
          <h5 class="mb-0 fw-bold text-center">Edit Replies</h5>
        </div>
      </div>
    </div> 
    <div class="container-fluid mt-2">
      <form method="post">
        <div class="mb-3">
          <textarea class="form-control" id="reply" name="reply" rows="10" oninput="stripHtmlTags(this)" required><?php echo strip_tags($reply['reply']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-bold">Save</button>
      </form>
    </div>
    <div class="ms-2 d-none-sm position-absolute top-0 mt-2 start-0">
      <button class="btn btn-sm btn-secondary rounded-pill fw-bold opacity-50" onclick="goBack()">
        <i class="bi bi-arrow-left-circle-fill"></i> Back
      </button>
    </div>
    <script>
      function goBack() {
        window.location.href = "reply_comments_preview.php?imageid=<?php echo urlencode($imageid); ?>&comment_id=<?php echo urlencode($reply['comment_id']); ?>";
      }
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
<?php
  }
}
?>
