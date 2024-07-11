<?php
require_once('../auth.php');

$email = $_SESSION['email'];

// Connect to the SQLite database using parameterized query
$db = new SQLite3('../database.sqlite');
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS images (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, email TEXT, tags TEXT, title TEXT, imgdesc TEXT, link TEXT, date DATETIME, view_count INT DEFAULT 0, type TEXT, episode_name TEXT, artwork_type TEXT, `group` TEXT, categories TEXT, language TEXT, parodies TEXT, characters TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS image_child (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT NOT NULL, image_id INTEGER NOT NULL, email TEXT NOT NULL, FOREIGN KEY (image_id) REFERENCES images (id))");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS comments (id INTEGER PRIMARY KEY, filename TEXT, email TEXT, comment TEXT, created_at DATETIME)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS reply_comments (id INTEGER PRIMARY KEY AUTOINCREMENT, comment_id INTEGER, email TEXT, reply TEXT, date DATETIME, FOREIGN KEY (comment_id) REFERENCES comments(id))");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS favorites (id INTEGER PRIMARY KEY AUTOINCREMENT, image_id INTEGER, email TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS following (id INTEGER PRIMARY KEY AUTOINCREMENT, follower_email TEXT NOT NULL, following_email TEXT NOT NULL)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS news (id INTEGER PRIMARY KEY, title TEXT, description TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, ver TEXT, verlink TEXT, preview TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS status (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT, message TEXT, date DATETIME)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS image_album (id INTEGER PRIMARY KEY AUTOINCREMENT, image_id INTEGER NOT NULL, email TEXT NOT NULL, album_id INTEGER NOT NULL, FOREIGN KEY (image_id) REFERENCES image(id), FOREIGN KEY (album_id) REFERENCES album(id))");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS album (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, album_name TEXT NOT NULL);");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS episode (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, episode_name TEXT);");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS posts (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, content TEXT NOT NULL, email INTEGER NOT NULL, tags TEXT NOT NULL, date DATETIME, category TEXT, FOREIGN KEY (email) REFERENCES users(id))");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS category (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT, category_name TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS starred (id INTEGER PRIMARY KEY AUTOINCREMENT, note_id INTEGER, email TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS novel (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, email TEXT, title TEXT, description TEXT, content TEXT, tags TEXT, date DATETIME, view_count INT DEFAULT 0)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS comments_novel (id INTEGER PRIMARY KEY, filename TEXT, email TEXT, comment TEXT, created_at TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS favorites_novel (id INTEGER PRIMARY KEY AUTOINCREMENT, novel_id INTEGER, email TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS comments_novel (id INTEGER PRIMARY KEY, filename TEXT, email TEXT, comment TEXT, created_at TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS chapter (id INTEGER PRIMARY KEY AUTOINCREMENT, novel_id TEXT, email TEXT, title TEXT, content TEXT, FOREIGN KEY (novel_id) REFERENCES novel(id), FOREIGN KEY (email) REFERENCES users(email));");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS comments_novel (id INTEGER PRIMARY KEY, filename TEXT, email TEXT, comment TEXT, created_at TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS reply_comments_novel (id INTEGER PRIMARY KEY AUTOINCREMENT, comment_id INTEGER, email TEXT, reply TEXT, date DATETIME, FOREIGN KEY (comment_id) REFERENCES comments(id))");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS favorites_videos (id INTEGER PRIMARY KEY AUTOINCREMENT, video_id INTEGER, email TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS videos (id INTEGER PRIMARY KEY AUTOINCREMENT, video TEXT, email TEXT, thumb TEXT, title TEXT, description TEXT, date DATETIME, view_count INT DEFAULT 0)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS comments_minutes (id INTEGER PRIMARY KEY, minute_id TEXT, email TEXT, comment TEXT, created_at TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS reply_comments_minutes (id INTEGER PRIMARY KEY AUTOINCREMENT, comment_id INTEGER, email TEXT, reply TEXT, date DATETIME, FOREIGN KEY (comment_id) REFERENCES comments(id))");
$stmt->execute();

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $existing_fav = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = $image_id");

  if ($existing_fav == 0) {
    $db->exec("INSERT INTO favorites (email, image_id) VALUES ('$email', $image_id)");
  }
  
  // Redirect to the same page with the appropriate sorting parameter
  $page = isset($_GET['page']) ? $_GET['page'] : 1; // check if page is set, default to 1
  $by = isset($_GET['by']) ? $_GET['by'] : 'newest'; // check if by is set, default to newest
  header('Location: ?by=' . $by . '&page=' . $page);
  exit(); 
  
} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $db->exec("DELETE FROM favorites WHERE email = '$email' AND image_id = $image_id");

  // Redirect to the same page with the appropriate sorting parameter
  $page = isset($_GET['page']) ? $_GET['page'] : 1; // check if page is set, default to 1
  $by = isset($_GET['by']) ? $_GET['by'] : 'newest'; // check if by is set, default to newest
  header('Location: ?by=' . $by . '&page=' . $page);
  exit();
}

// Create the "visit" table if it doesn't exist
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS visit (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  visit_count INTEGER,
  visit_date DATE DEFAULT CURRENT_DATE,
  UNIQUE(visit_date)
)");
$stmt->execute();

// Process any visit requests
$stmt = $db->prepare("SELECT id, visit_count FROM visit WHERE visit_date = CURRENT_DATE");
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);

if ($row) {
  // If the record for the current date exists, increment the visit_count
  $visitCount = $row['visit_count'] + 1;
  $stmt = $db->prepare("UPDATE visit SET visit_count = :visitCount WHERE id = :id");
  $stmt->bindValue(':visitCount', $visitCount, SQLITE3_INTEGER);
  $stmt->bindValue(':id', $row['id'], SQLITE3_INTEGER);
  $stmt->execute();
} else {
  // If the record for the current date doesn't exist, insert a new record
  $stmt = $db->prepare("INSERT INTO visit (visit_count) VALUES (:visitCount)");
  $stmt->bindValue(':visitCount', 1, SQLITE3_INTEGER);
  $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <link rel="../manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <link rel="stylesheet" href="../style.css">
    <?php include('../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <div class="dropdown">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=newest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?by=oldest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?by=popular&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">popular</a></li>
        <li><a href="?by=view&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'view') echo 'active'; ?>">most viewed</a></li>
        <li><a href="?by=least&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'least') echo 'active'; ?>">least viewed</a></li>
        <li><a href="?by=liked&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'liked') echo 'active'; ?>">liked</a></li>
        <li><a href="?by=order_asc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'order_asc') echo 'active'; ?>">from A to Z</a></li>
        <li><a href="?by=order_desc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'order_desc') echo 'active'; ?>">from Z to A</a></li>
        <li><a href="?by=top" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'top') echo 'active'; ?>">top images</a></li>
      </ul> 
    </div> 
    <?php include '../contents/home/tags_group.php'; ?>
        <?php 
        if(isset($_GET['by'])){
          $sort = $_GET['by'];
 
          switch ($sort) {
            case 'newest':
            include "index_desc.php";
            break;
            case 'oldest':
            include "index_asc.php";
            break;
            case 'popular':
            include "index_pop.php";
            break;
            case 'view':
            include "index_view.php";
            break;
            case 'least':
            include "index_least.php";
            break;
            case 'liked':
            include "index_like.php";
            break;
            case 'top':
            include "top.php";
            break;
            case 'order_asc':
            include "index_order_asc.php";
            break;
            case 'order_desc':
            include "index_order_desc.php";
            break;
          }
        }
        else {
          include "index_desc.php";
        }
        
        ?>
    <script>
      function shareImage(userId) {
        // Compose the share URL
        var shareUrl = '../image.php?artworkid=' + userId;

        // Check if the Share API is supported by the browser
        if (navigator.share) {
          navigator.share({
          url: shareUrl
        })
          .then(() => console.log('Shared successfully.'))
          .catch((error) => console.error('Error sharing:', error));
        } else {
          console.log('Share API is not supported in this browser.');
          // Provide an alternative action for browsers that do not support the Share API
          // For example, you can open a new window with the share URL
          window.open(shareUrl, '_blank');
        }
      }
    </script>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>