<?php
require_once('auth.php');

$db = new SQLite3('database.sqlite');
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS comments (id INTEGER PRIMARY KEY, imageid TEXT, email TEXT, comment TEXT, created_at TEXT)");
$stmt->execute();

$comment_id = $_GET['commentid'];
$sortUrl = $_GET['by'];
$pageUrl = $_GET['page'];

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

  // Append $sortUrl to the redirect URL
  $redirectUrl = "comments.php?by={$sortUrl}&imageid={$comment['imageid']}&page={$pageUrl}";
  header("Location: $redirectUrl");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <title>Edit Comment</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
    <style>
      /* For Webkit-based browsers */
      ::-webkit-scrollbar {
        width: 0;
        height: 0;
        border-radius: 10px;
      }

      ::-webkit-scrollbar-track {
        border-radius: 0;
      }

      ::-webkit-scrollbar-thumb {
        border-radius: 0;
      }
      
      .text-stroke {
        -webkit-text-stroke: 3px;
      }
    </style>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container mb-5">
      <form method="post">
        <div class="mb-2">
          <textarea class="form-control border-0 bg-body-tertiary rounded-4 shadow" id="comment" name="comment" rows="13" oninput="stripHtmlTags(this)" required><?php echo strip_tags($comment['comment']); ?></textarea>
        </div>
        <div class="btn-group w-100 gap-2">
          <button class="btn btn-secondary w-50 fw-bold rounded-4" onclick="goBack()">Cancel</button>
          <button type="submit" class="btn btn-primary w-50 fw-bold rounded-4">Save</button>
        </div>
      </form>
    </div>
    <script>
      function goBack() {
        window.location.href = "comments.php?by=<?php echo urlencode($sortUrl); ?>&imageid=<?php echo urlencode($comment['imageid']); ?>&page=<?php echo urlencode($pageUrl); ?>";
      }
    </script> 
  </body>
</html>
