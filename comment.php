<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  // Redirect to the login page if not
  header('Location: session.php');
  exit();
}

// Connect to the database
$db = new SQLite3('database.sqlite');
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS comments (id INTEGER PRIMARY KEY, filename TEXT, email TEXT, comment TEXT, created_at TEXT)");
$stmt->execute();

// Get the filename from the query string
$filename = $_GET['imageid'];

// Get the image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id=:filename");
$stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
$image = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

// Check if the image exists
if (!$image) {
  // Redirect to the homepage if not
  header('Location: index.php');
  exit();
}

// Check if the form was submitted for adding a new comment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
  // Get the comment from the form data
  $comment = filter_var($_POST['comment'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $comment = nl2br($comment);
  $email = $_SESSION['email'];

  // Check if the comment is not empty
  if (!empty(trim($comment))) {
    // Insert the comment into the database
    $stmt = $db->prepare("INSERT INTO comments (filename, email, comment, created_at) VALUES (:filename, :email, :comment, :created_at)");
    $stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':comment', $comment, SQLITE3_TEXT);
    $stmt->execute();
  }

  // Redirect back to the image page
  header("Location: comment.php?imageid=$filename");
  exit();
}

// Check if the form was submitted for updating or deleting a comment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
  $action = $_POST['action'];
  $comment_id = $_POST['comment_id'];

  // Get the email of the current user
  $email = $_SESSION['email'];

  // Check if the comment belongs to the current user
  $stmt = $db->prepare("SELECT * FROM comments WHERE id=:comment_id AND email=:email");
  $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $comment = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

  if ($comment) {
    if ($action == 'delete') {
      // Delete the comment from the comments table
      $stmt = $db->prepare("DELETE FROM comments WHERE id=:comment_id");
      $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
      $stmt->execute();

      // Delete the corresponding replies from the reply_comments table
      $stmt = $db->prepare("DELETE FROM reply_comments WHERE comment_id=:comment_id");
      $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
      $stmt->execute();
    }
  }

  // Redirect back to the image page
  header("Location: comment.php?imageid=$filename");
  exit();
}

// Set the number of comments to display per page
$comments_per_page = 300;

// Get the current page from the URL, or default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the starting offset for the current page
$offset = ($page - 1) * $comments_per_page;

// Get the total number of comments for the current image
$total_comments_stmt = $db->prepare("SELECT COUNT(*) FROM comments WHERE filename=:filename");
$total_comments_stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
$total_comments = $total_comments_stmt->execute()->fetchArray()[0];

// Calculate the total number of pages
$total_pages = ceil($total_comments / $comments_per_page);

// Get all comments for the current image for the current page
$stmt = $db->prepare("SELECT comments.*, users.artist, users.pic, users.id as iduser FROM comments JOIN users ON comments.email = users.email WHERE comments.filename=:filename ORDER BY comments.id DESC LIMIT :comments_per_page OFFSET :offset");
$stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
$stmt->bindValue(':comments_per_page', $comments_per_page, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$comments = $stmt->execute();
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Comment Section</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('backheader.php'); ?>
    <br><br>
    <div class="container-fluid mt-2">
      <?php
        while ($comment = $comments->fetchArray()) :
      ?>
        <div class="card border-0 shadow mb-1 position-relative">
          <div class="d-flex align-items-center mb-2 position-relative">
            <div class="position-absolute top-0 start-0 m-1">
              <img class="rounded-circle" src="<?php echo $comment['pic']; ?>" width="32" height="32">
              <a class="text-dark text-decoration-none fw-semibold" href="artist.php?id=<?php echo $comment['iduser'];?>" target="_blank">@<?php echo $comment['artist']; ?></a>
            </div>
            <?php if ($comment['email'] == $_SESSION['email']) : ?>
              <div class="dropdown ms-auto position-relative">
                <button class="btn btn-sm btn-secondary opacity-50 position-absolute top-0 end-0 m-1" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                  <form action="" method="POST">
                    <a href="edit_comment.php?commentid=<?php echo $comment['id']; ?>" class="dropdown-item fw-semibold">
                      <i class="bi bi-pencil-fill me-2"></i>Edit
                    </a>
                    <input type="hidden" name="filename" value="<?php echo $filename; ?>">
                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                    <button type="submit" name="action" onclick="return confirm('Are you sure?')" value="delete" class="dropdown-item fw-semibold">
                      <i class="bi bi-trash-fill me-2"></i>Delete
                    </button>
                  </form>
                </div>
              </div>
            <?php endif; ?>
          </div>
          <div class="mt-5 container-fluid">
            <p class="fw-semibold" style="word-break: break-word;">
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
            </p>
          </div>
          <div class="m-2 ms-auto">
            <a class="btn btn-sm fw-semibold" href="reply_comments.php?imageid=<?php echo $filename; ?>&comment_id=<?php echo $comment['id']; ?>"><i class="bi bi-reply-fill"></i> Reply</a>
          </div>
        </div>
      <?php
        endwhile;
      ?>
    </div>
    <div class="pagination justify-content-center mb-2">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm fw-bold btn-primary me-1" href="?filename=<?php echo $filename; ?>&page=<?php echo $page - 1 ?>">Prev</a>
      <?php endif ?>
      <?php if ($page < $total_pages): ?>
        <a class="btn btn-sm fw-bold btn-primary ms-1" href="?filename=<?php echo $filename; ?>&page=<?php echo $page + 1 ?>">Next</a>
      <?php endif ?>
    </div>
    <nav class="navbar fixed-bottom navbar-expand justify-content-center">
      <div class="container-fluid">
        <button type="button" class="w-100 btn btn-primary fw-bold rounded-3" data-bs-toggle="modal" data-bs-target="#comments">send your comment</button>
      </div>
    </nav>
    <div class="modal fade" id="comments" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Type something else...</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div>
            <form class="form-control border-0" action="" method="POST">
              <textarea type="text" class="form-control fw-semibold rounded-3 mb-2" style="height: 200px; max-height: 800px;" name="comment" placeholder="Type something..." aria-label="Type a message..." aria-describedby="basic-addon2" 
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
        window.location.href = "image.php?artworkid=<?php echo $image['id']; ?>";
      }
    </script>
    <script>
      var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
          if (entry.isIntersecting) {
            // Load the content dynamically here
            entry.target.innerHTML = entry.target.querySelector('p').innerHTML;
            // Unobserve the target to prevent further callbacks
            observer.unobserve(entry.target);
          }
        });
      });

      document.querySelectorAll('[data-lazyload]').forEach(function(target) {
        observer.observe(target);
      });
    </script>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=IntersectionObserver"></script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
