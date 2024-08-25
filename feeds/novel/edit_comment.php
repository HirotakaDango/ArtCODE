<?php
require_once('auth.php');

try {
  // Database connection using PDO
  $db = new PDO('sqlite:../../database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Create comments_novel table if it doesn't exist
  $createTableQuery = "CREATE TABLE IF NOT EXISTS comments_novel (id INTEGER PRIMARY KEY, filename TEXT, email TEXT, comment TEXT, created_at TEXT)";
  $db->exec($createTableQuery);

  // Retrieve comment_id from GET parameter
  $comment_id = isset($_GET['commentid']) ? $_GET['commentid'] : null;
  $sortUrl  = isset($_GET['by']) ? $_GET['by'] : null;
  $pageUrl = isset($_GET['page']) ? $_GET['page'] : null;

  // Fetch comment based on comment_id
  $stmt = $db->prepare("SELECT * FROM comments_novel WHERE id = :comment_id");
  $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
  $stmt->execute();
  $comment = $stmt->fetch(PDO::FETCH_ASSOC);

  // Check if the comment was fetched successfully
  if (!$comment) {
    // Handle the case where the comment doesn't exist
    die("Error: Comment not found");
  }

  // Handle comment update
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    $reply = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

    if (!empty(trim($reply))) {
      $updateStmt = $db->prepare("UPDATE comments_novel SET comment = :reply WHERE id = :comment_id");
      $updateStmt->bindParam(':reply', $reply, PDO::PARAM_STR);
      $updateStmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
      $updateStmt->execute();
    }

    // Redirect to the original comment page
    header("Location: comments.php?by={$sortUrl}&novelid={$comment['filename']}&page={$pageUrl}");
    exit();
  }
} catch (PDOException $e) {
  die("Error: " . $e->getMessage());
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
        window.location.href = "comments.php?by=<?php echo urlencode($sortUrl); ?>&novelid=<?php echo htmlspecialchars($comment['filename']); ?>&page=<?php echo urlencode($pageUrl); ?>";
      }
    </script> 
  </body>
</html>
