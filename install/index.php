<?php
// Define the path to the SQLite database
$dbPath = '../database.sqlite';

// Initialize status variables
$databaseCreated = false;
$adminCreated = false;

// Start session for user authentication
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
  header('Location: /index.php');
  exit();
}

try {
  $db = new PDO('sqlite:' . $dbPath);

  // Check if the database file exists
  if (file_exists($dbPath)) {
    $databaseCreated = true;

    // Check if the admin table exists and if the admin user is present
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='admin'");
    if ($stmt->fetch()) {
      // Check if there's at least one admin user
      $stmt = $db->query("SELECT COUNT(*) FROM admin");
      $adminCount = $stmt->fetchColumn();
      if ($adminCount > 0) {
        $adminCreated = true;
      }
    }
  }

  // Handle form submission
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $email = $_POST['email'];
    $password = $_POST['password'];
    $artist = $_POST['artist'];

    // Validate input
    if (empty($email) || empty($password) || empty($artist)) {
      $error_msg = 'All fields are required.';
    } else {
      try {
        // Create the tables
        $queries = [
          "CREATE TABLE IF NOT EXISTS daily (id INTEGER PRIMARY KEY AUTOINCREMENT, image_id TEXT NOT NULL, views TEXT, date DATETIME)",
          "CREATE TABLE IF NOT EXISTS images (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, email TEXT, tags TEXT, title TEXT, imgdesc TEXT, link TEXT, date DATETIME, view_count INT DEFAULT 0, type TEXT, episode_name TEXT, artwork_type TEXT, `group` TEXT, categories TEXT, language TEXT, parodies TEXT, characters TEXT)",
          "CREATE TABLE IF NOT EXISTS messages (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, message TEXT, date DATETIME, to_user_email TEXT)",
          "CREATE TABLE IF NOT EXISTS chat_group (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, group_message TEXT, date DATETIME)",
          "CREATE TABLE IF NOT EXISTS image_child (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT NOT NULL, image_id INTEGER NOT NULL, email TEXT NOT NULL, FOREIGN KEY (image_id) REFERENCES images (id))",
          "CREATE TABLE IF NOT EXISTS comments (id INTEGER PRIMARY KEY, imageid TEXT, email TEXT, comment TEXT, created_at DATETIME)",
          "CREATE TABLE IF NOT EXISTS reply_comments (id INTEGER PRIMARY KEY AUTOINCREMENT, comment_id INTEGER, email TEXT, reply TEXT, date DATETIME, FOREIGN KEY (comment_id) REFERENCES comments(id))",
          "CREATE TABLE IF NOT EXISTS favorites (id INTEGER PRIMARY KEY AUTOINCREMENT, image_id INTEGER, email TEXT)",
          "CREATE TABLE IF NOT EXISTS following (id INTEGER PRIMARY KEY AUTOINCREMENT, follower_email TEXT NOT NULL, following_email TEXT NOT NULL)",
          "CREATE TABLE IF NOT EXISTS news (id INTEGER PRIMARY KEY, title TEXT, description TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, ver TEXT, verlink TEXT, preview TEXT)",
          "CREATE TABLE IF NOT EXISTS status (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT, message TEXT, date DATETIME)",
          "CREATE TABLE IF NOT EXISTS image_album (id INTEGER PRIMARY KEY AUTOINCREMENT, image_id INTEGER NOT NULL, email TEXT NOT NULL, album_id INTEGER NOT NULL, FOREIGN KEY (image_id) REFERENCES images(id), FOREIGN KEY (album_id) REFERENCES album(id))",
          "CREATE TABLE IF NOT EXISTS album (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, album_name TEXT NOT NULL)",
          "CREATE TABLE IF NOT EXISTS episode (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, episode_name TEXT)",
          "CREATE TABLE IF NOT EXISTS posts (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, content TEXT NOT NULL, email INTEGER NOT NULL, tags TEXT NOT NULL, date DATETIME, category TEXT, FOREIGN KEY (email) REFERENCES users(id))",
          "CREATE TABLE IF NOT EXISTS category (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT, category_name TEXT)",
          "CREATE TABLE IF NOT EXISTS starred (id INTEGER PRIMARY KEY AUTOINCREMENT, note_id INTEGER, email TEXT)",
          "CREATE TABLE IF NOT EXISTS novel (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, email TEXT, title TEXT, description TEXT, content TEXT, tags TEXT, date DATETIME, view_count INT DEFAULT 0)",
          "CREATE TABLE IF NOT EXISTS comments_novel (id INTEGER PRIMARY KEY, filename TEXT, email TEXT, comment TEXT, created_at TEXT)",
          "CREATE TABLE IF NOT EXISTS favorites_novel (id INTEGER PRIMARY KEY AUTOINCREMENT, novel_id INTEGER, email TEXT)",
          "CREATE TABLE IF NOT EXISTS chapter (id INTEGER PRIMARY KEY AUTOINCREMENT, novel_id TEXT, email TEXT, title TEXT, content TEXT, FOREIGN KEY (novel_id) REFERENCES novel(id), FOREIGN KEY (email) REFERENCES users(email))",
          "CREATE TABLE IF NOT EXISTS reply_comments_novel (id INTEGER PRIMARY KEY AUTOINCREMENT, comment_id INTEGER, email TEXT, reply TEXT, date DATETIME, FOREIGN KEY (comment_id) REFERENCES comments(id))",
          "CREATE TABLE IF NOT EXISTS favorites_videos (id INTEGER PRIMARY KEY AUTOINCREMENT, video_id INTEGER, email TEXT)",
          "CREATE TABLE IF NOT EXISTS videos (id INTEGER PRIMARY KEY AUTOINCREMENT, video TEXT, email TEXT, thumb TEXT, title TEXT, description TEXT, date DATETIME, view_count INT DEFAULT 0)",
          "CREATE TABLE IF NOT EXISTS comments_minutes (id INTEGER PRIMARY KEY, minute_id TEXT, email TEXT, comment TEXT, created_at TEXT)",
          "CREATE TABLE IF NOT EXISTS reply_comments_minutes (id INTEGER PRIMARY KEY AUTOINCREMENT, comment_id INTEGER, email TEXT, reply TEXT, date DATETIME, FOREIGN KEY (comment_id) REFERENCES comments(id))"
        ];

        foreach ($queries as $query) {
          $stmt = $db->prepare($query);
          $stmt->execute();
        }

        // Create the admin table if it does not exist
        $stmt = $db->prepare("CREATE TABLE IF NOT EXISTS admin (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, status TEXT DEFAULT 'superadmin')");
        $stmt->execute();

        // Insert the user as an admin
        $stmt = $db->prepare("INSERT INTO admin (email, status) VALUES (:email, 'superadmin')");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // Create a user table if it does not exist
        $stmt = $db->prepare("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT, password TEXT, artist TEXT, pic TEXT, desc TEXT, bgpic TEXT, token TEXT, twitter TEXT, pixiv TEXT, other TEXT, region TEXT, joined DATETIME, born DATETIME, numpage TEXT, display TEXT, message_1 TEXT, message_2 TEXT, message_3 TEXT, message_4 TEXT, mode TEXT)");
        $stmt->execute();
        
        $stmt = $db->prepare("INSERT INTO users (email, password, artist) VALUES (:email, :password, :artist)");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR); // Store plain password
        $stmt->bindParam(':artist', $artist, PDO::PARAM_STR);
        $stmt->execute();

        // Get user ID
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['id'] == 1) {
          // Directly log in the user
          $_SESSION['email'] = $email;
          // Redirect to the current page (installation page)
          header('Location: /install/');
          exit();
        } else {
          // Redirect to index page for other users
          header('Location: /index.php');
          exit();
        }

      } catch (PDOException $e) {
        $error_msg = 'Database error: ' . $e->getMessage();
      }
    }
  }
} catch (PDOException $e) {
  $error_msg = 'Database connection error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install</title>
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
      <div class="card p-4 rounded-4 border-0 bg-body-tertiary w-100" style="max-width: 500px;">
        <?php if (!$databaseCreated || !$adminCreated): ?>
          <h1 class="text-center fw-bold">Install</h1>
          <form method="post">
            <div class="w-100">
              <div class="row mt-3 align-items-center">
                <div class="col-md-3">
                  <span class="fw-medium" for="artist">Artist</span>
                </div>
                <div class="col-md-9">
                  <div class="form-group">
                    <input type="text" name="artist" id="artist" class="form-control" required>
                  </div>
                </div>
              </div>
  
              <div class="row mt-3 align-items-center">
                <div class="col-md-3">
                  <span class="fw-medium" for="email">Email</span>
                </div>
                <div class="col-md-9">
                  <div class="form-group">
                    <input type="email" name="email" id="email" class="form-control" required>
                  </div>
                </div>
              </div>
  
              <div class="row mt-3 align-items-center">
                <div class="col-md-3">
                  <span class="fw-medium" for="password">Password</span>
                </div>
                <div class="col-md-9">
                  <div class="form-group">
                    <input type="password" name="password" id="password" class="form-control" required>
                  </div>
                </div>
              </div>
  
              <button type="submit" class="btn btn-primary w-100 mt-4">Set your current configuration</button>
            </div>
          </form>
          <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger mt-3"><?php echo htmlspecialchars($error_msg); ?></div>
          <?php endif; ?>
        <?php else: ?>
          <h5 class="mt-4 fw-bold text-center">Database and admin user are already set up.</h5>
          <a href="/install/next.php" class="btn btn-success mt-4">Next</a>
        <?php endif; ?>
      </div>
    </div>
  </body>
</html>