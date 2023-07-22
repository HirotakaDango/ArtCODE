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
 
// Create the reply_comments table if it doesn't exist
$db->exec('CREATE TABLE IF NOT EXISTS reply_comments (id INTEGER PRIMARY KEY AUTOINCREMENT, comment_id INTEGER, email TEXT, reply TEXT, FOREIGN KEY (comment_id) REFERENCES comments(id))');

// Get the image id from comment.php
$imageid = $_GET['imageid'];

// Get the id of the image
$stmt = $db->prepare("SELECT * FROM images WHERE id=:imageid");
$stmt->bindValue(':imageid', $imageid, SQLITE3_INTEGER);
$image = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

// Check if the reply form was submitted
if (isset($_POST['reply_comment_id'], $_POST['reply'])) {
  // Trim the reply text to remove leading and trailing spaces
  $reply = trim($_POST['reply']);

  // Check if the reply is empty after trimming
  if (!empty($reply)) {
    // Prepare the reply text by removing special characters and converting newlines to <br> tags
    $reply = nl2br(filter_var($reply, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));

    // Insert a new reply into the reply_comments table
    $stmt = $db->prepare('INSERT INTO reply_comments (comment_id, email, reply) VALUES (?, ?, ?)');
    $stmt->bindValue(1, $_POST['reply_comment_id'], SQLITE3_INTEGER);
    $stmt->bindValue(2, $_SESSION['email'], SQLITE3_TEXT);
    $stmt->bindValue(3, $reply, SQLITE3_TEXT);
    $stmt->execute();

    // Redirect back to the current page with the comment_id parameter
    header('Location: reply_comments_preview.php?imageid='.$imageid.'&comment_id=' . $_POST['reply_comment_id']);
    exit();
  } else {
    // Handle the case where the reply is empty
    // Display an error message or take appropriate action
  }
}

// Check if the "delete_reply_id" key is set in the $_GET superglobal
if (isset($_GET['delete_reply_id'])) {
  // Get the comment_id and image_id for the reply to be deleted
  $get_reply_info_stmt = $db->prepare('SELECT comment_id FROM reply_comments WHERE id = ?');
  $get_reply_info_stmt->bindValue(1, $_GET['delete_reply_id'], SQLITE3_INTEGER);
  $reply_info_result = $get_reply_info_stmt->execute()->fetchArray(SQLITE3_ASSOC);
  $imageid = $_GET['imageid'];
  

  if ($reply_info_result !== false) {
    $comment_id = $reply_info_result['comment_id'];
    // $image_id = $_GET['imageid'];
    // $image_id = $reply_info_result['image_id'];

    // Delete the selected reply from the reply_comments table
    $delete_reply_stmt = $db->prepare('DELETE FROM reply_comments WHERE id = ?');
    $delete_reply_stmt->bindValue(1, $_GET['delete_reply_id'], SQLITE3_INTEGER);
    $delete_reply_stmt->execute();

    // Redirect back to the current page with the imageid and comment_id parameters
    $redirect_url = 'reply_comments_preview.php?imageid=' . urlencode($imageid) . '&comment_id=' . urlencode($comment_id);
    header('Location: ' . $redirect_url);
    exit();
  } else {
    // Handle the case where the comment_id or image_id could not be retrieved
  }
}

// Get the selected comment based on its ID
$comment_id = isset($_GET['comment_id']) ? $_GET['comment_id'] : null;
if ($comment_id !== null) {
  $comment = $db->prepare('SELECT * FROM comments WHERE id = ?');
  $comment->bindValue(1, $comment_id, SQLITE3_INTEGER);
  $comment = $comment->execute()->fetchArray(SQLITE3_ASSOC);

  // Get all replies for the selected comment from the reply_comments table, along with the user information
  $replies = $db->prepare('SELECT rc.*, u.artist, u.pic, u.id as userid FROM reply_comments rc JOIN users u ON rc.email = u.email WHERE rc.comment_id = ? ORDER BY rc.id DESC');
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
    <div class="mb-2">
      <?php if ($comment_id !== null && $comment !== false): ?>
      <div class="modal-content border-3 border-bottom ">
        <div class="modal-body p-3">
          <h5 class="mb-0 fw-bold text-center">Comment Replies</h5>
          <div class="fw-bold mt-2">
            <small>
              <?php
                $messageText = $comment['comment'];
                $messageTextWithoutTags = strip_tags($messageText);
                $pattern = '/\bhttps?:\/\/\S+/i';

                $formattedText = preg_replace_callback($pattern, function ($matches) {
                  $url = htmlspecialchars($matches[0]);
                  return '<a href="' . $url . '">' . $url . '</a>';
                }, $messageTextWithoutTags);

                $formattedTextWithLineBreaks = nl2br($formattedText);
                echo $formattedTextWithLineBreaks;
              ?>
            </small>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid">
      <div>
        <?php
          // Display each reply and a delete button
          while ($reply = $replies->fetchArray(SQLITE3_ASSOC)):
        ?>
          <div class="card shadow border-0 mb-1">
            <div class="ms-1">
              <p class="text-dark fw-semibold mt-1">
                <img class="rounded-circle" src="<?php echo !empty($reply['pic']) ? $reply['pic'] : "icon/profile.svg"; ?>" alt="Profile Picture" width="32" height="32">
                <a class="text-dark text-decoration-none" href="artist.php?id=<?php echo $reply['userid']; ?>">@<?php echo $reply['artist']; ?></a>
              </p>
              <p class="text-dark container-fluid fw-semibold mb-2" style="word-break: break-word;" data-lazyload>
                <small>
                  <?php
                    $messageText = $reply['reply'];
                    $messageTextWithoutTags = strip_tags($messageText);
                    $pattern = '/\bhttps?:\/\/\S+/i';

                    $formattedText = preg_replace_callback($pattern, function ($matches) {
                      $url = htmlspecialchars($matches[0]);
                      return '<a href="' . $url . '">' . $url . '</a>';
                    }, $messageTextWithoutTags);

                    $formattedTextWithLineBreaks = nl2br($formattedText);
                    echo $formattedTextWithLineBreaks;
                  ?>
                </small>
              </p>
              <?php if ($_SESSION['email'] === $reply['email']): ?>
                <form action="" method="get">
                  <div class="btn-group position-absolute top-0 end-0 mt-1 me-1 opacity-50">
                    <a href="edit_reply_comments.php?reply_id=<?php echo $reply['id']; ?>&imageid=<?php echo $imageid; ?>" class="btn btn-sm btn-secondary"><i class="bi bi-pencil-fill"></i></a>
                    <input type="hidden" name="delete_reply_id" value="<?= $reply['id'] ?>">
                    <input type="hidden" name="imageid" value="<?= $imageid ?>" />
                    <button onclick="return confirm('Are you sure?')" class="btn btn-sm btn-secondary " type="submit">
                      <i class="bi bi-trash-fill"></i>
                    </button>
                  </div>
                </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else:
        // Display an error message if the comment ID is invalid
        echo "<p>Invalid comment ID.</p>";
      endif; ?>
      <div class="fixed-bottom w-100">
        <form action="" method="POST">
          <div class="input-group w-100">
            <textarea id="reply" name="reply" class="form-control fw-semibold" style="height: 40px; max-height: 150px;" placeholder="Type your comment..." aria-label="Type a message..." aria-describedby="basic-addon2" 
              onkeydown="if(event.keyCode == 13) { this.style.height = (parseInt(this.style.height) + 10) + 'px'; return true; }"
              onkeyup="this.style.height = '40px'; var newHeight = (this.scrollHeight + 10 * (this.value.split(/\r?\n/).length - 1)) + 'px'; if (parseInt(newHeight) > 150) { this.style.height = '150px'; } else { this.style.height = newHeight; }"></textarea>
            <input type="hidden" name="reply_comment_id" value="<?= $comment['id'] ?>">
            <button type="submit" class="btn btn-primary fw-bold">send</button>
          </div>
        </form> 
      </div>
    </div>
    <br><br><br>
    <div class="ms-2 d-none-sm position-absolute top-0 mt-2 start-0">
      <button class="btn btn-sm btn-secondary rounded-pill fw-bold opacity-50" onclick="goBack()">
        <i class="bi bi-arrow-left-circle-fill"></i> Back
      </button>
    </div>
    <script>
      function goBack() {
        window.location.href = "comment_preview.php?imageid=<?php echo $imageid; ?>";
        // window.location.href = "index.php";
      }
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>