<?php
require_once('auth.php');

// Open the SQLite database
$db = new SQLite3('../../database.sqlite');

$novelid = $_GET['novelid'];
$pageUrl = $_GET['page'];
$replySort = $_GET['sort'];
$sortUrl = $_GET['by'];
$commentId = $_GET['comment_id'];

// Check if the reply ID is provided
if (isset($_GET['reply_id'])) {
  $replyId = $_GET['reply_id'];

  // Get the reply details from the database
  $stmt = $db->prepare('SELECT * FROM reply_comments_novel WHERE id = :reply_id');
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
        $stmt = $db->prepare('UPDATE reply_comments_novel SET reply = :new_reply WHERE id = :reply_id');
        $stmt->bindValue(':new_reply', $newReply, SQLITE3_TEXT);
        $stmt->bindValue(':reply_id', $replyId, SQLITE3_INTEGER);
        $stmt->execute();

        // Redirect back to the reply preview page
        $redirectUrl = 'reply_comment_novel.php?sort=' . urlencode($replySort) . '&by=' . urlencode($sortUrl) . '&novelid=' . urlencode($novelid) . '&comment_id=' . urlencode($commentId) . '&page=' . urlencode($pageUrl);
        header('Location: ' . $redirectUrl);
        exit();
      } else {
        // Handle the case where the new reply is empty
        // Display an error message or take appropriate action
      }
    }
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <title>Edit Comment</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container mt-3 mb-5">
      <form method="post">
        <div class="mb-2">
          <textarea class="form-control border-0 bg-body-tertiary rounded-4 shadow" id="reply" name="reply" rows="13" oninput="stripHtmlTags(this)" required><?php echo strip_tags($reply['reply']); ?></textarea>
        </div>
        <div class="btn-group w-100 gap-2">
          <button class="btn btn-secondary w-50 fw-bold rounded-4" onclick="goBack()">Cancel</button>
          <button type="submit" class="btn btn-primary w-50 fw-bold rounded-4">Save</button>
        </div>
      </form>
    </div>
    <script>
      function goBack() {
        window.location.href = "reply_comment_novel.php?sort=<?= urlencode($replySort) ?>&by=<?= urlencode($sortUrl) ?>&novelid=<?= urlencode($novelid) ?>&comment_id=<?= urlencode($commentId) ?>&page=<?= urlencode($pageUrl) ?>";
      }
    </script>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
<?php
  }
}
?>
