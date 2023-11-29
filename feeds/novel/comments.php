<?php
require_once('../../auth.php');

try {
  // Database connection using PDO
  $db = new PDO('sqlite:../../database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // Fetch user information
    $query = "SELECT * FROM users WHERE email='$email'";
    $user = $db->query($query)->fetch();

    if (isset($_GET['id'])) {
      $id = $_GET['id'];
    }

    // Handle comment creation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
      $comment = nl2br(filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));

      if (!empty(trim($comment))) {
        $stmt = $db->prepare('INSERT INTO comments_novel (email, comment, date, page_id) VALUES (?, ?, ?, ?)');
        $stmt->execute([$email, $comment, date("Y-m-d H:i:s"), $id]);

        header("Location: comments.php?id=$id");
        exit();
      } else {
        echo "<script>alert('Comment cannot be empty.');</script>";
      }
    }

    // Handle comment deletion
    if (
      $_SERVER['REQUEST_METHOD'] === 'GET' &&
      isset($_GET['action']) &&
      $_GET['action'] === 'delete' &&
      isset($_GET['commentId']) &&
      isset($id) &&
      isset($user)
    ) {
      $stmt = $db->prepare('DELETE FROM comments_novel WHERE id = ? AND email = ?');
      $stmt->execute([$_GET['commentId'], $user['email']]);

      header("Location: comments.php?id=$id");
      exit();
    }
  }

  // Fetch post information with a JOIN on the "users" table
  $query = "SELECT novel.id, novel.title, novel.description, novel.content, novel.email, novel.tags, novel.date, users.email AS user_email, users.artist
            FROM novel
            JOIN users ON novel.email = users.email
            WHERE novel.id = '$id'";
  $post = $db->query($query)->fetch();

  // Get comments for the current page, ordered by id in descending order
  $query = "SELECT comments_novel.*, users.artist AS comment_artist
            FROM comments_novel
            JOIN users ON comments_novel.email = users.email
            WHERE comments_novel.page_id='$id'
            ORDER BY comments_novel.id DESC";
  $comments = $db->query($query)->fetchAll();
} catch (PDOException $e) {
  die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments at <?php echo $post['title']; ?></title>
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <main id="swup" class="transition-main">
    <div class="container mt-3 mb-5">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-3">
          <li class="breadcrumb-item">
            <a class="link-body-emphasis" href="index.php">
              <i class="bi bi-house-fill"></i>
              <span class="visually-hidden">News</span>
            </a>
          </li>
          <li class="breadcrumb-item">
            <a class="link-body-emphasis fw-semibold text-decoration-none text-white fw-medium" href="view.php?id=<?php echo $id; ?>"><?php echo $post['title']; ?></a>
          </li>
          <li class="breadcrumb-item active disabled" aria-current="page">
            Comments
          </li>
        </ol>
      </nav>

      <!-- Comment form, show only if the user is logged in -->
      <?php if ($user): ?>
        <form method="post" action="comments.php?id=<?php echo $id; ?>">
          <div class="mb-3">
            <h5 for="comment" class="form-label fw-bold">Add a comment:</h5>
            <textarea id="comment" name="comment" class="form-control border-top-0 border-start-0 border-end-0 border-4 rounded-0 focus-ring focus-ring-dark" rows="4" onkeydown="if(event.keyCode == 13) { document.execCommand('insertHTML', false, '<br><br>'); return false; }"></textarea>
          </div>
          <button type="submit" class="btn w-100 btn-primary">Submit</button>
        </form>
      <?php else: ?>
        <h5 class="text-center">You must <a href="../../session/login.php">login</a> or <a href="../../session/register.php">register</a> to send a comment!</h5>
      <?php endif; ?>

      <!-- Display comments -->
      <h5 class="mt-5 mb-2 fw-bold">Comments:</h5>
      <?php foreach ($comments as $comment): ?>
        <div class="card mt-2">
          <div class="card-body">
            <?php
            $displayartist = isset($post['artist']) ? htmlspecialchars($post['artist']) : 'Unknown';
            $messageText = isset($comment['comment']) ? $comment['comment'] : 'No comment available';
            $messageTextWithoutTags = strip_tags($messageText);
            $pattern = '/\bhttps?:\/\/\S+/i';

            $formattedText = preg_replace_callback($pattern, function ($matches) {
              $url = htmlspecialchars($matches[0]);
              return '<a href="' . $url . '">' . $url . '</a>';
            }, $messageTextWithoutTags);

            $formattedTextWithLineBreaks = nl2br($formattedText);

            $displayComment = $formattedTextWithLineBreaks;
            $displayDate = isset($comment['date']) ? htmlspecialchars($comment['date']) : 'No date available';
            ?>
            <div class="d-flex">
              <p class="fw-bold me-auto">User: <?php echo $displayartist; ?> | (<small><?php echo (new DateTime($displayDate))->format("Y/m/d | H:i:s"); ?></small>)</p>
              <?php if ($user && $comment['email'] == $user['email']): ?>
                <a href="comments.php?action=delete&commentId=<?php echo $comment['id']; ?>&id=<?php echo $id; ?>" style="max-height: 30px;" class="btn btn-danger btn-sm ms-auto">Delete</a>
              <?php endif; ?>
            </div>
            <p><?php echo $displayComment; ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    </main>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>