<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  // Redirect to the login page if not
  header('Location: session.php');
  exit();
}

// Open the SQLite database
$db = new SQLite3('database.sqlite');

// Create the reply_forum table if it doesn't exist
$db->exec('CREATE TABLE IF NOT EXISTS reply_forum (id INTEGER PRIMARY KEY AUTOINCREMENT, comment_id INTEGER, email TEXT, reply TEXT, FOREIGN KEY (comment_id) REFERENCES comments(id))');

// Check if the reply form was submitted
if (isset($_POST['reply_comment_id'], $_POST['reply'])) {
  // Trim the reply text to remove leading and trailing spaces
  $reply = trim($_POST['reply']);

  // Check if the reply is empty after trimming
  if (!empty($reply)) {
    // Prepare the reply text by removing special characters and converting newlines to <br> tags
    $reply = nl2br(filter_var($reply, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));

    // Insert a new reply into the reply_comments table
    $stmt = $db->prepare('INSERT INTO reply_forum (comment_id, email, reply) VALUES (?, ?, ?)');
    $stmt->bindValue(1, $_POST['reply_comment_id'], SQLITE3_INTEGER);
    $stmt->bindValue(2, $_SESSION['email'], SQLITE3_TEXT);
    $stmt->bindValue(3, $reply, SQLITE3_TEXT);
    $stmt->execute();

    // Redirect back to the current page with the comment_id parameter
    header('Location: reply_forum.php?comment_id=' . $_POST['reply_comment_id']);
    exit();
  } else {
    // Handle the case where the reply is empty
    // Display an error message or take appropriate action
  }
}

// Check if the "delete_reply_id" key is set in the $_GET superglobal
if (isset($_GET['delete_reply_id'])) {
  // Get the comment_id for the reply to be deleted
  $get_comment_id_stmt = $db->prepare('SELECT comment_id FROM reply_forum WHERE id = ?');
  $get_comment_id_stmt->bindValue(1, $_GET['delete_reply_id'], SQLITE3_INTEGER);
  $comment_id_result = $get_comment_id_stmt->execute()->fetchArray(SQLITE3_ASSOC);

  if ($comment_id_result !== false) {
    $comment_id = $comment_id_result['comment_id'];

    // Delete the selected reply from the reply_forum table
    $delete_reply_stmt = $db->prepare('DELETE FROM reply_forum WHERE id = ?');
    $delete_reply_stmt->bindValue(1, $_GET['delete_reply_id'], SQLITE3_INTEGER);
    $delete_reply_stmt->execute();

    // Redirect back to the current page with the comment_id parameter
    header('Location: reply_forum.php?comment_id=' . $comment_id);
    exit();
  } else {
    // Handle the case where the comment_id could not be retrieved
  }
}

// Get the selected comment based on its ID
$comment_id = isset($_GET['comment_id']) ? $_GET['comment_id'] : null;
if ($comment_id !== null) {
  $comment = $db->prepare('SELECT * FROM forum WHERE id = ?');
  $comment->bindValue(1, $comment_id, SQLITE3_INTEGER);
  $comment = $comment->execute()->fetchArray(SQLITE3_ASSOC);

  // Get all replies for the selected comment from the reply_forum table, along with the user information
  $replies = $db->prepare('SELECT rc.*, u.artist, u.id as userid FROM reply_forum rc JOIN users u ON rc.email = u.email WHERE rc.comment_id = ? ORDER BY rc.id DESC');
  $replies->bindValue(1, $comment_id, SQLITE3_INTEGER);
  $replies = $replies->execute();
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
    <?php include('backheader.php'); ?>
    <div class="container-fluid">
      <br>
      <?php if ($comment_id !== null && $comment !== false): ?>
       
        <div class="modal-dialog mt-5 mb-5" role="document">
          <div class="modal-content rounded-3 shadow border-4 border">
            <div class="modal-body p-4">
              <h5 class="mb-0 fw-bold text-center">Comment Replies</h5>
              <div class="fw-bold mt-2"><small><?php echo preg_replace('/\b(https?:\/\/\S+)/i', '<a class="text-decoration-none" target="_blank" href="$1">$1</a>', $comment['comment']); ?></small></div>
            </div>
          </div>
        </div>
        <?php
          // Display each reply and a delete button
          while ($reply = $replies->fetchArray(SQLITE3_ASSOC)):
        ?>
          <div class="card mb-2">
            <div class="ms-1">
              <p class="text-dark fw-bold mt-1">
                <i class="bi bi-person-circle"></i>
                <a class="text-dark text-decoration-none" href="artist.php?id=<?php echo $reply['userid']; ?>">@<?php echo $reply['artist']; ?></a>
              </p>
              <div class="text-secondary fw-bold mb-2" style="word-break: break-word;" data-lazyload>
                <small><?php echo preg_replace('/\b(https?:\/\/\S+)/i', '<a class="text-decoration-none" target="_blank" href="$1">$1</a>', $reply['reply']); ?></small>
              </div>
              <?php if ($_SESSION['email'] === $reply['email']): ?>
                <form action="" method="get">
                  <input type="hidden" name="delete_reply_id" value="<?= $reply['id'] ?>">
                  <button onclick="return confirm('Are you sure?')" class="btn btn-sm btn-secondary opacity-50 float-end position-absolute top-0 end-0 mt-1 me-1" type="submit">
                    <i class="bi bi-trash-fill"></i>
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else:
        // Display an error message if the comment ID is invalid
        echo "<p>Invalid comment ID.</p>";
      endif; ?>
      <nav class="navbar fixed-bottom navbar-expand justify-content-center">
        <div class="container-fluid">
          <button type="button" class="w-100 btn btn-primary fw-bold rounded-3" data-bs-toggle="modal" data-bs-target="#comments">send your comment</button>
        </div>
      </nav>
    </div>
    <div class="modal fade" id="comments" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Type something else...</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div>
            <form class="form-control border-0" action="" method="POST">
              <input type="hidden" name="reply_comment_id" value="<?= $comment['id'] ?>">
              <textarea type="text" class="form-control fw-bold rounded-3 mb-2" style="height: 200px; max-height: 800px;" name="reply" id="reply" placeholder="Type something..." aria-label="Type a message..." aria-describedby="basic-addon2" 
                onkeydown="if(event.keyCode == 13) { this.style.height = (parseInt(this.style.height) + 10) + 'px'; return true; }"
                onkeyup="this.style.height = '40px'; var newHeight = (this.scrollHeight + 10 * (this.value.split(/\r?\n/).length - 1)) + 'px'; if (parseInt(newHeight) > 800) { this.style.height = '800px'; } else { this.style.height = newHeight; }" required></textarea>
              <button class="w-100 btn btn-primary rounded-3" type="submit"><i class="bi bi-send-fill"></i></button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <br><br><br>
    <div class="ms-2 d-none-sm position-fixed top-50 start-0 translate-middle-y">
      <button class="btn btn-primary rounded-pill fw-bold btn-md" onclick="goBack()">
        <i class="bi bi-arrow-left-circle-fill"></i> Back
      </button>
    </div>
    <script>
      function goBack() {
        window.location.href = "forum.php";
      }
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
