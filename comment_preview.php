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
  $currentURL = $_SERVER['REQUEST_URI'];
  $redirectURL = $currentURL;
  header("Location: $redirectURL");
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
  $currentURL = $_SERVER['REQUEST_URI'];
  $redirectURL = $currentURL;
  header("Location: $redirectURL");
  exit();
}

// Set the number of comments to display per page
$comments_per_page = 100;

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
    <div class="container-fluid mt-2">
      <?php
        while ($comment = $comments->fetchArray()) :
      ?>
        <div class="card border-0 shadow mb-1 position-relative">
          <div class="d-flex align-items-center mb-2 position-relative">
            <div class="position-absolute top-0 start-0 m-1">
              <img class="rounded-circle" src="<?php echo !empty($comment['pic']) ? $comment['pic'] : "icon/profile.svg"; ?>" alt="Profile Picture" width="32" height="32">
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
            <a class="btn btn-sm fw-semibold" href="reply_comments_preview.php?imageid=<?php echo $filename; ?>&comment_id=<?php echo $comment['id']; ?>"><i class="bi bi-reply-fill"></i> Reply</a>
          </div>
        </div>
      <?php
        endwhile;
      ?>
      <?php
        $totalPages = ceil($total_comments / $comments_per_page);
        $prevPage = $page - 1;
        $nextPage = $page + 1;
      ?>
      <div class="pagination d-flex gap-1 justify-content-center mt-3">
        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?imageid=<?php echo $filename; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
        <?php endif; ?>

        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?imageid=<?php echo $filename; ?>&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
        <?php endif; ?>

        <?php
          // Calculate the range of page numbers to display
          $startPage = max($page - 2, 1);
          $endPage = min($page + 2, $totalPages);

          // Display page numbers within the range
          for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i === $page) {
              echo '<span class="btn btn-sm btn-primary active fw-bold">' . $i . '</span>';
            } else {
              echo '<a class="btn btn-sm btn-primary fw-bold" href="?imageid=' . $filename . '&page=' . $i . '">' . $i . '</a>';
            }
          }
        ?>

        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?imageid=<?php echo $filename; ?>&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?imageid=<?php echo $filename; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
        <?php endif; ?>
      </div>
      <div class="fixed-bottom w-100">
        <form action="" method="POST">
          <div class="input-group w-100 rounded-bottom-2">
            <textarea id="message-input" name="comment" class="form-control fw-semibold" style="height: 40px; max-height: 150px;" placeholder="Type your comment..." aria-label="Type a message..." aria-describedby="basic-addon2" 
              onkeydown="if(event.keyCode == 13) { this.style.height = (parseInt(this.style.height) + 10) + 'px'; return true; }"
              onkeyup="this.style.height = '40px'; var newHeight = (this.scrollHeight + 10 * (this.value.split(/\r?\n/).length - 1)) + 'px'; if (parseInt(newHeight) > 150) { this.style.height = '150px'; } else { this.style.height = newHeight; }"></textarea>
            <button type="submit" class="btn btn-primary fw-bold">send</button>
          </div>
        </form> 
      </div>
    </div>
    <br><br><br>
    <style>
      .text-stroke {
        -webkit-text-stroke: 1px;
      }
    </style>
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