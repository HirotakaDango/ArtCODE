<?php
require_once('../auth.php');

$email = $_SESSION['email'];

// Connect to the SQLite database using parameterized query
$db = new SQLite3('../database.sqlite');
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS daily (id INTEGER PRIMARY KEY AUTOINCREMENT, image_id TEXT NOT NULL, views INT DEFAULT 0, date DATETIME)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS images (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, email TEXT, tags TEXT, title TEXT, imgdesc TEXT, link TEXT, date DATETIME, view_count INT DEFAULT 0, type TEXT, episode_name TEXT, artwork_type TEXT, `group` TEXT, categories TEXT, language TEXT, parodies TEXT, characters TEXT, original_filename TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS messages (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, message TEXT, date DATETIME, to_user_email TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS chat_group (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, group_message TEXT, date DATETIME)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS image_child (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT NOT NULL, image_id INTEGER NOT NULL, email TEXT NOT NULL, original_filename TEXT NOT NULL, FOREIGN KEY (image_id) REFERENCES images (id))");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS comments (id INTEGER PRIMARY KEY AUTOINCREMENT, imageid TEXT, email TEXT, comment TEXT, created_at DATETIME)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS reply_comments (id INTEGER PRIMARY KEY AUTOINCREMENT, comment_id INTEGER, email TEXT, reply TEXT, date DATETIME, FOREIGN KEY (comment_id) REFERENCES comments(id))");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS favorites (id INTEGER PRIMARY KEY AUTOINCREMENT, image_id INTEGER, email TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS following (id INTEGER PRIMARY KEY AUTOINCREMENT, follower_email TEXT NOT NULL, following_email TEXT NOT NULL)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS news (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, description TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, ver TEXT, verlink TEXT, preview TEXT)");
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
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS comments_novel (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, email TEXT, comment TEXT, created_at TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS favorites_novel (id INTEGER PRIMARY KEY AUTOINCREMENT, novel_id INTEGER, email TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS comments_novel (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, email TEXT, comment TEXT, created_at TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS chapter (id INTEGER PRIMARY KEY AUTOINCREMENT, novel_id TEXT, email TEXT, title TEXT, content TEXT, FOREIGN KEY (novel_id) REFERENCES novel(id), FOREIGN KEY (email) REFERENCES users(email));");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS comments_novel (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, email TEXT, comment TEXT, created_at TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS reply_comments_novel (id INTEGER PRIMARY KEY AUTOINCREMENT, comment_id INTEGER, email TEXT, reply TEXT, date DATETIME, FOREIGN KEY (comment_id) REFERENCES comments(id))");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS favorites_videos (id INTEGER PRIMARY KEY AUTOINCREMENT, video_id INTEGER, email TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS videos (id INTEGER PRIMARY KEY AUTOINCREMENT, video TEXT, email TEXT, thumb TEXT, title TEXT, description TEXT, date DATETIME, view_count INT DEFAULT 0)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS comments_minutes (id INTEGER PRIMARY KEY AUTOINCREMENT, minute_id TEXT, email TEXT, comment TEXT, created_at TEXT)");
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
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <link rel="stylesheet" href="../style.css">
    <?php include('../bootstrapcss.php'); ?>
    <script>
      // Inline manifest
      const manifest = {
        "name": "ArtCODE",
        "short_name": "ArtCODE",
        "start_url": ".",
        "display": "standalone",
        "background_color": "#ffffff",
        "theme_color": "#000000",
        "icons": [
          {
            "src": "/icon/favicon.png",
            "sizes": "192x192",
            "type": "image/png"
          }
        ]
      };

      const manifestBlob = new Blob([JSON.stringify(manifest)], { type: 'application/json' });
      const manifestURL = URL.createObjectURL(manifestBlob);
      const link = document.createElement('link');
      link.rel = 'manifest';
      link.href = manifestURL;
      document.head.appendChild(link);

      // Inline service worker registration
      if ('serviceWorker' in navigator) {
        const swBlob = new Blob([`
          self.addEventListener('install', function(event) {
            event.waitUntil(
              caches.open('ArtCODE-v0.1.13').then(function(cache) {
                return cache.addAll([
                  '/',
                  '/home/index.php',
                  '/icon/favicon.png'
                ]);
              })
            );
          });

          self.addEventListener('fetch', function(event) {
            event.respondWith(
              caches.match(event.request).then(function(response) {
                return response || fetch(event.request);
              })
            );
          });
        `], { type: 'application/javascript' });

        const swURL = URL.createObjectURL(swBlob);
        navigator.serviceWorker.register(swURL).then(function(registration) {
          console.log('ServiceWorker registration successful with scope: ', registration.scope);
        }).catch(function(error) {
          console.log('ServiceWorker registration failed: ', error);
        });
      }
    </script>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <?php 
    $defaultOption = 'simple'; // Define a default option

    // Get the option from the URL or from localStorage (via JavaScript)
    $option = isset($_GET['option']) ? $_GET['option'] : "<script>document.write(localStorage.getItem('homepage_option') || '$defaultOption');</script>";

    switch ($option) {
      case 'full':
        include "home_full.php";
        break;
      case 'simple':
      default:
        include "home_simple.php";
        break;
    }
    ?>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>