<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
  // Redirect to the login page if not
  header('Location: session.php');
  exit();
}

// Connect to the database
$db = new SQLite3('database.sqlite');
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS comments (id INTEGER PRIMARY KEY, filename TEXT, username TEXT, comment TEXT)");
$stmt->execute();

// Get the filename from the query string
$filename = htmlspecialchars($_GET['filename']);

// Get the image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE filename=:filename");
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
  $comment = htmlspecialchars($_POST['comment']);
  $username = $_SESSION['username'];

  // Insert the comment into the database
  $stmt = $db->prepare("INSERT INTO comments (filename, username, comment) VALUES (:filename, :username, :comment)");
  $stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
  $stmt->bindValue(':username', $username, SQLITE3_TEXT);
  $stmt->bindValue(':comment', $comment, SQLITE3_TEXT);
  $stmt->execute();

  // Redirect back to the image page
  header("Location: comment.php?filename=$filename");
  exit();
}

// Check if the form was submitted for updating or deleting a comment
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
  $action = $_POST['action'];
  $comment_id = $_POST['comment_id'];

  // Get the username of the current user
  $username = $_SESSION['username'];

  // Check if the comment belongs to the current user
  $stmt = $db->prepare("SELECT * FROM comments WHERE id=:comment_id AND username=:username");
  $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
  $stmt->bindValue(':username', $username, SQLITE3_TEXT);
  $comment = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

  if ($comment) {
    if ($action == 'update') {
      $new_comment = htmlspecialchars($_POST['new_comment']);
      $stmt = $db->prepare("UPDATE comments SET comment=:new_comment WHERE id=:comment_id");
      $stmt->bindValue(':new_comment', $new_comment, SQLITE3_TEXT);
      $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
      $stmt->execute();
    } elseif ($action == 'delete') {
      $stmt = $db->prepare("DELETE FROM comments WHERE id=:comment_id");
      $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
      $stmt->execute();
    }
  }

  // Redirect back to the image page
  header("Location: comment.php?filename=$filename");
  exit();
}

// Get all comments for the current image
$stmt = $db->prepare("SELECT comments.*, users.artist FROM comments JOIN users ON comments.username = users.username WHERE comments.filename=:filename ORDER BY comments.id DESC");
$stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
$comments = $stmt->execute(); 
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Comment Section</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <div class="container mt-2">
      <div class="row">
        <div class="col-md-8 mx-auto">
          <button class="btn btn-sm btn-secondary rounded-pill float-start" onclick="goBack()">Go Back</button><br>
          <h3 class="text-center text-secondary fw-bold mb-2 mt-2">Comment Section</h3>
          <?php
          while ($comment = $comments->fetchArray()) :
          ?>
          <div class="card mb-3">
            <div class="card-body text-secondary fw-bold">
              <p><?php echo $comment['artist']; ?> :</p>
              <p><?php echo $comment['comment']; ?></p>
              <?php if ($comment['username'] == $_SESSION['username']) : ?>
                <form action="" method="POST">
                  <input type="hidden" name="filename" value="<?php echo $filename; ?>">
                  <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                  <?php
                    // Only show textarea when edit button is clicked
                    $showTextarea = isset($_POST['action']) && $_POST['action'] === 'update' && $_POST['comment_id'] === $comment['id'];
                  ?>
                  <div class="form-group <?php echo $showTextarea ? '' : 'd-none'; ?>">
                    <textarea class="form-control" name="new_comment" rows="3"><?php echo $comment['comment']; ?></textarea>
                  </div>
                  <div class="btn-group">
                 <?php if (!$showTextarea) : ?>
                   <button type="button" onclick="this.closest('form').querySelector('.form-group').classList.remove('d-none');" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></button>
                 <?php endif; ?>
                    <button type="submit" name="action" value="update" class="btn btn-sm btn-outline-secondary"><i class="bi bi-check"></i></button>
                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-secondary"><i class="bi bi-trash"></i></button>
                  </div>
                </form>
              <?php endif; ?>
            </div>
          </div>
          <?php
          endwhile;
          ?>
          <div style="position: absolute; bottom: 0; left: 0; width: 100%;" class="random-class-name">
            <form class="" action="" method="POST">
              <input type="hidden" name="filename" value="<?php echo $filename; ?>">
              <div class="form-group d-flex container mb-2" style="height: 40px;">
                <textarea class="form-control me-1 flex-grow-1" name="comment" id="comment" maxlength="400" required></textarea>
                <button type="submit" class="btn btn-primary ms-2">Submit</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <script>
      function goBack() {
        window.location.href = "image.php?filename=<?php echo $filename; ?>";
      }
    </script>
  </body>
</html>
