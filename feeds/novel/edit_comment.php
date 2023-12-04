<?php
require_once('../../auth.php');

try {
  // Database connection using PDO
  $db = new PDO('sqlite:../../database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Create comments_novel table if it doesn't exist
  $createTableQuery = "CREATE TABLE IF NOT EXISTS comments_novel ( id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT, comment TEXT, date DATETIME, page_id TEXT)";
  $db->exec($createTableQuery);

  // Retrieve comment_id from GET parameter
  $comment_id = isset($_GET['commentid']) ? $_GET['commentid'] : null;

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
    header("Location: comments.php?id={$comment['page_id']}");
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
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container-fluid mt-2">
      <form method="post">
        <div class="mb-3">
          <textarea class="form-control" id="comment" name="comment" rows="10" oninput="stripHtmlTags(this)" required><?php echo $comment['comment']; ?></textarea>
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
        window.location.href = "comment.php?novelid=<?php echo htmlspecialchars($comment['filename']); ?>";
      }
    </script> 
  </body>
</html>
