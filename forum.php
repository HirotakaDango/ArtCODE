<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  // Redirect to the login page if not
  header('Location:session.php');
  exit();
}

// Connect to the database
$db = new SQLite3('database.sqlite');
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS forum (id INTEGER PRIMARY KEY, email TEXT, comment TEXT, created_at TEXT)");
$stmt->execute();

// Function to get the elapsed time since a certain date in a human-readable format
function time_elapsed_string($datetime, $full = false) {
  $now = new DateTime;
  $ago = new DateTime($datetime);
  $diff = $now->diff($ago);
  $diff->w = floor($diff->d / 7);
  $diff->d -= $diff->w * 7;
  $string = array(
    'y' => 'year',
    'm' => 'month',
    'w' => 'week',
    'd' => 'day',
    'h' => 'hour',
    'i' => 'minute',
    's' => 'second',
  );
  foreach ($string as $k => &$v) {
    if ($diff->$k) {
      $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
    } else {
      unset($string[$k]);
    }
  }
  if (!$full) $string = array_slice($string, 0, 1);
  return $string ? implode(', ', $string) . ' ago' : 'just now';
}

// Check if the form was submitted for adding a new comment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
  // Get the comment from the form data
  $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $comment = nl2br($comment);
  $email = $_SESSION['email'];

  // Get the current time
  $now = date('Y-m-d');

  // Insert the comment into the database
  $stmt = $db->prepare("INSERT INTO forum (email, comment, created_at) VALUES (:email, :comment, :created_at)");
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':comment', $comment, SQLITE3_TEXT);
  $stmt->bindValue(':created_at', $now, SQLITE3_TEXT);
  $stmt->execute();

  // Redirect back to the image page
  header("Location:forum.php");
  exit();
}

// Check if the form was submitted for updating or deleting a comment
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
  $action = $_POST['action'];
  $comment_id = $_POST['comment_id'];

  // Get the email of the current user
  $email = $_SESSION['email'];

  // Check if the comment belongs to the current user
  $stmt = $db->prepare("SELECT * FROM forum WHERE id=:comment_id AND email=:email");
  $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $comment = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

  if ($comment) {
    if ($action == 'update') {
      $new_comment = filter_input(INPUT_POST, 'new_comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
      $new_comment = nl2br($new_comment);
      $stmt = $db->prepare("UPDATE forum SET comment=:new_comment WHERE id=:comment_id");
      $stmt->bindValue(':new_comment', $new_comment, SQLITE3_TEXT);
      $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
      $stmt->execute();
    } elseif ($action == 'delete') {
      $stmt = $db->prepare("DELETE FROM forum WHERE id=:comment_id");
      $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
      $stmt->execute();
    }
  }

  // Redirect back to the image page
  header("Location:forum.php");
  exit();
}

// Set the number of items to display per page
$items_per_page = 300;

// Get the current page from the URL, or default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the starting offset for the current page
$offset = ($page - 1) * $items_per_page;

// Get the total number of forum items
$total_items_stmt = $db->prepare("SELECT COUNT(*) FROM forum");
$total_items = $total_items_stmt->execute()->fetchArray()[0];

// Calculate the total number of pages
$total_pages = ceil($total_items / $items_per_page);

// Get all forum items for the current page
$stmt = $db->prepare("SELECT forum.*, users.artist FROM forum JOIN users ON forum.email = users.email ORDER BY forum.id DESC LIMIT :items_per_page OFFSET :offset");
$stmt->bindValue(':items_per_page', $items_per_page, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$forum = $stmt->execute();
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Forum</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
    <style>
      .random-class-name {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
      }
    </style>  
  </head>
  <body>
    <br><br>
    <div class="container-fluid mt-2">
      <div class="row">
        <div class="col-md-8 mb-5 mx-auto">
          <?php
          while ($comment = $forum->fetchArray()) :
          ?>
          <div class="card mb-2">
            <div class="me-1 ms-1 mt-1 text-secondary fw-bold">
              <p class="text-dark"><i class="bi bi-person-circle"></i> <a class="text-dark text-decoration-none" href="artist.php?id=<?php echo $comment['id'];?>">@<?php echo $comment['artist']; ?></a></p>
              <div style="word-break: break-word;" data-lazyload><small><?php echo preg_replace('/\b(https?:\/\/\S+)/i', '<a class="text-decoration-none" target="_blank" href="$1">$1</a>', $comment['comment']); ?></small></div>
              <small style="font-size: 12px;">sent: <?php echo $comment['created_at']; ?></small>
              <?php if ($comment['email'] == $_SESSION['email']) : ?>
                <form action="" method="POST">
                  <input type="hidden" name="filename" value="<?php echo $filename; ?>">
                  <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                  <?php
                    // Only show textarea when edit button is clicked
                    $showTextarea = isset($_POST['action']) && $_POST['action'] === 'update' && $_POST['comment_id'] === $comment['id'];
                  ?>
                  <div class="form-group mb-1 <?php echo $showTextarea ? '' : 'd-none'; ?>">
                    <textarea class="form-control" style="font-size: 14px;" oninput="stripHtmlTags(this)" name="new_comment" rows="3"><?php echo strip_tags($comment['comment']); ?></textarea>
                    <button type="submit" name="action" value="update" class="form-control bg-primary mt-1 fw-bold text-white">Update</button> 
                  </div>
                  <div class="btn-group comment-buttons mt-1 me-1 opacity-50">
                    <?php if (!$showTextarea) : ?>
                      <button type="button" onclick="this.closest('form').querySelector('.form-group').classList.remove('d-none');" class="btn btn-sm btn-secondary"><i class="bi bi-pencil-fill"></i></button>
                    <?php endif; ?>
                    <button type="submit" name="action" onclick="return confirm('Are you sure?')" value="delete" class="btn btn-sm btn-secondary"><i class="bi bi-trash-fill"></i></button>
                  </div>
                </form>
              <?php endif; ?>
            </div>
          </div>
          <?php
          endwhile;
          ?>
        </div>
        <div class="pagination justify-content-center" style="margin-bottom: 80px;">
          <?php if ($page > 1): ?>
            <a class="btn btn-sm fw-bold btn-primary me-1" href="?page=<?php echo $page - 1 ?>">Next</a>
          <?php endif ?>
          <?php if ($page < $total_pages): ?>
            <a class="btn btn-sm fw-bold btn-primary ms-1" href="?page=<?php echo $page + 1 ?>">Prev</a>
          <?php endif ?>
        </div>
        <nav class="navbar fixed-bottom navbar-expand">
          <form class="form-control border-0 container" action="" method="POST">
            <div class="input-group mb-3">
              <textarea type="text" class="form-control fw-bold rounded-3" style="height: 40px; max-height: 130px;" name="comment" placeholder="Type something..." aria-label="Type a message..." aria-describedby="basic-addon2" 
                onkeydown="if(event.keyCode == 13) { this.style.height = (parseInt(this.style.height) + 10) + 'px'; return true; }"
                onkeyup="this.style.height = '40px'; var newHeight = (this.scrollHeight + 10 * (this.value.split(/\r?\n/).length - 1)) + 'px'; if (parseInt(newHeight) > 130) { this.style.height = '130px'; } else { this.style.height = newHeight; }"></textarea>
              <button class="btn btn-primary ms-1 rounded-3" type="submit" style="width: 40px; height: 40px;"><i class="bi bi-send-fill"></i></button>
            </div>
          </form>
        </nav> 
      </div>
    </div>
  <?php include('header.php'); ?>
    <style>
      .comment-buttons {
        position: absolute;
        top: 0;
        right: 0;
      }

      .comment-buttons button {
        margin-left: 5px; /* optional: add some margin between the buttons */
      }
      
      @media (min-width: 768px) {
        .width-vw {
          width: 89.5vw;
        }
      }

      @media (max-width: 767px) {
        .width-vw {
          width: 75vw;
        }
      }
    </style> 
    <script>
      function goBack() {
        window.location.href = "../index.php";
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