<?php
session_start();

$db = new PDO('sqlite:forum/database.db');
$db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT NOT NULL, password TEXT NOT NULL)");
$db->exec("CREATE TABLE IF NOT EXISTS posts (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, content TEXT NOT NULL, user_id INTEGER NOT NULL, date DATETIME, category TEXT, FOREIGN KEY (user_id) REFERENCES users(id))");
$db->exec("CREATE TABLE IF NOT EXISTS comments (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT, comment TEXT, date DATETIME, post_id TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS category (id INTEGER PRIMARY KEY AUTOINCREMENT, category_name TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS favorites (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, link TEXT, image_cover TEXT, episode_name TEXT)");
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Characters</title>
    <?php include('bootstrap.php'); ?>
    <?php include('connection.php'); ?>
    <link rel="icon" type="image/png" href="<?php echo $web; ?>/icon/favicon.png">
    <meta property="og:url" content="<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Manga-API">
    <meta property="og:image" content="<?php echo $web; ?>/icon/favicon.png">
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container my-3">
      <h1 class="mb-4 fw-bold">Characters</h1>
      <?php
        // Fetch JSON data from api_manga_characters.php
        $json = file_get_contents($web . '/api_manga_characters.php');
        $data = json_decode($json, true);
    
        // Check if the data is an array and not empty
        if (is_array($data) && !empty($data)) {
          $characters = $data['characters'];
    
          // Group characters by their starting character or letter
          $groupedCharacters = [];
          foreach ($characters as $character => $count) {
            // Get the first character (or substring) as the key for grouping
            $firstChar = mb_substr($character, 0, 1, 'UTF-8');
    
            // Ensure the starting character is uppercase
            $firstCharUpper = mb_strtoupper($firstChar, 'UTF-8');
    
            if (!isset($groupedCharacters[$firstCharUpper])) {
              $groupedCharacters[$firstCharUpper] = [];
            }
            $groupedCharacters[$firstCharUpper][$character] = $count;
          }
    
          // Sort groups alphabetically by their keys (characters or letters)
          ksort($groupedCharacters, SORT_STRING);
        ?>
          <div class="row justify-content-center">
            <?php foreach ($groupedCharacters as $group => $characters): ?>
              <div class="col-4 col-md-2 col-sm-5 px-0">
                <a class="btn btn-outline-light border-0 fw-medium d-flex flex-column align-items-center" href="#category-<?php echo $group; ?>"><h6 class="fw-medium">Category</h6> <h6 class="fw-bold"><?php echo $group; ?></h6></a>
              </div>
            <?php endforeach; ?>
          </div>
          <?php foreach ($groupedCharacters as $group => $characters): ?>
            <div id="category-<?php echo $group; ?>" class="category-section pt-5">
              <h5 class="fw-bold text-start">Category <?php echo mb_strtoupper($group); ?></h5>
              <?php foreach ($characters as $character => $count): ?>
                <div class="btn-group my-1 w-100">
                  <a href="index.php?character=<?php echo urlencode($character); ?>" class="btn bg-secondary-subtle fw-bold text-start">
                    <?php echo htmlspecialchars($character, ENT_QUOTES, 'UTF-8'); ?>
                  </a>
                  <a href="#" class="btn bg-body-tertiary fw-bold text-wrap" style="width: 50px; max-width: 50px;">
                    <?php echo $count; ?>
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        <?php 
        } else { 
        ?>
          <p>No data found.</p>
        <?php 
        } 
      ?>
    </div>
  </body>
</html>