<?php
require_once('auth.php');

$db = new SQLite3('database.sqlite');
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS comments (id INTEGER PRIMARY KEY, filename TEXT, email TEXT, comment TEXT, created_at TEXT)");
$stmt->execute();

$comment_id = $_GET['commentid'];

$stmt = $db->prepare("SELECT * FROM comments WHERE id=:comment_id");
$stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
$comment = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$comment) {
  header('Location: index.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
  $reply = filter_var($_POST['comment'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

  if (!empty(trim($reply))) {
    $stmt = $db->prepare("UPDATE comments SET comment=:reply WHERE id=:comment_id");
    $stmt->bindValue(':reply', $reply, SQLITE3_TEXT);
    $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
    $stmt->execute();
  }

  header("Location: comment.php?imageid={$comment['filename']}");
  exit();
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
    <?php include('backheader.php'); ?>
    <br><br>
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
    <div class="d-none-sm position-fixed top-50 start-0 translate-middle-y">
      <button class="btn btn-primary rounded-pill rounded-start-0 fw-bold btn-md ps-1" onclick="goBack()">
        <i class="bi bi-arrow-left-circle-fill"></i>
      </button>
    </div>
    <script>
      function goBack() {
        window.location.href = "comment.php?imageid=<?php echo htmlspecialchars($comment['filename']); ?>";
      }
    </script> 
  </body>
</html>
