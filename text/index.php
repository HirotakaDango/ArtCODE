<?php
require_once('../auth.php');

// Connect to the SQLite database
$db = new SQLite3('../database.sqlite');

$email = $_SESSION['email'];

// Create tables if not exist
$db->exec('CREATE TABLE IF NOT EXISTS texts (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, title TEXT NOT NULL, content TEXT NOT NULL, tags TEXT, date DATETIME, view_count INTEGER DEFAULT 0)');
$db->exec('CREATE TABLE IF NOT EXISTS text_favorites (id INTEGER PRIMARY KEY AUTOINCREMENT, text_id INTEGER NOT NULL, email TEXT NOT NULL, FOREIGN KEY (text_id) REFERENCES texts(id))');

// Retrieve user ID from the `users` table based on email
$stmt2 = $db->prepare("SELECT id FROM users WHERE email = :email");
$stmt2->bindValue(':email', $email, SQLITE3_TEXT);  // Corrected variable binding
$result2 = $stmt2->execute();
$row2 = $result2->fetchArray(SQLITE3_ASSOC);
$user_id2 = $row2['id'] ?? null; // Handle case where user ID might not be found

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Sanitize and validate input
  $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $tags = filter_input(INPUT_POST, 'tags', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

  // Convert newlines to <br> tags
  $content = nl2br($content);

  // Get current datetime
  $datetime = date('Y/m/d');

  // Insert data into the `texts` table
  $stmt = $db->prepare('INSERT INTO texts (email, title, content, tags, date) VALUES (:email, :title, :content, :tags, :date)');
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':title', $title, SQLITE3_TEXT);
  $stmt->bindValue(':content', $content, SQLITE3_TEXT);
  $stmt->bindValue(':tags', $tags, SQLITE3_TEXT);
  $stmt->bindValue(':date', $datetime, SQLITE3_TEXT);
  $stmt->execute();

  // Redirect to avoid form resubmission
  header("Location: /text/?uid=" . $user_id2);
  exit;
}

// Get current search and tag parameters
$searchQuery = isset($_GET['q']) ? filter_input(INPUT_GET, 'q', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW) : '';
$tagFilter = isset($_GET['tag']) ? filter_input(INPUT_GET, 'tag', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW) : '';
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
      <?php
      if (isset($_GET['q'])) {
        echo 'Search: "' . $_GET['q'] . '"';
      } elseif (isset($_GET['tag'])) {
        echo 'Tag: "' . $_GET['tag'] . '"';
      } else {
        echo 'Text';
      }
      ?>
    </title>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <div class="container-fluid mt-2">
      <form method="get" action="/text/" class="mb-2 pb-1">
        <div class="input-group">
          <input type="text" class="form-control rounded-start-pill border-0 bg-body-tertiary" name="q" value="<?php echo $searchQuery; ?>" placeholder="Search by title, content, or tags">
          <button class="btn bg-body-tertiary border-0 rounded-end-pill" type="submit"><i class="bi bi-search"></i></button>
        </div>
      </form>
    </div>
    <div class="dropdown">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        sort by
      </button>
      <ul class="dropdown-menu">
        <?php
        // Get current query parameters, excluding 'by' and 'page'
        $queryParams = array_diff_key($_GET, array('by' => '', 'page' => ''));
        
        // Define sorting options and labels
        $sortOptions = [
          'newest' => 'newest',
          'oldest' => 'oldest',
          'popular' => 'popular',
          'view' => 'most viewed',
          'least' => 'least viewed',
          'liked' => 'liked',
          'order_asc' => 'from A to Z',
          'order_desc' => 'from Z to A'
        ];
    
        // Loop through each sort option
        foreach ($sortOptions as $key => $label) {
          // Determine if the current option is active
          $activeClass = (!isset($_GET['by']) && $key === 'newest') || (isset($_GET['by']) && $_GET['by'] === $key) ? 'active' : '';
          
          // Generate the dropdown item with the appropriate active class
          echo '<li><a href="?' . http_build_query(array_merge($queryParams, ['by' => $key, 'page' => isset($_GET['page']) ? $_GET['page'] : '1'])) . '" class="dropdown-item fw-bold ' . $activeClass . '">' . $label . '</a></li>';
        }
        ?>
      </ul>
    </div>
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
    <button type="button" class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-pill fw-bold position-fixed start-0 bottom-0 m-3" data-bs-toggle="modal" data-bs-target="#uploadText">
      upload
    </button>
    <div class="modal fade" id="uploadText" tabindex="-1" aria-labelledby="uploadTextLabel" aria-hidden="true">
      <div class="modal-dialog modal-fullscreen">
        <div class="modal-content border-0">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="uploadTextLabel">Post your text</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="index.php" method="post">
              <div class="form-floating mb-2">
                <input class="form-control rounded-3 fw-medium border-0 shadow bg-body-tertiary" type="text" name="title" id="title" placeholder="Enter title for your image" maxlength="500" required>  
                <label for="title" class="fw-medium">Enter title for your image</label>
              </div>
              <div class="form-floating mb-2">
                <input class="form-control rounded-3 fw-medium border-0 shadow bg-body-tertiary" type="text" name="tags" id="tags" placeholder="Enter tags for your image" maxlength="500" required>  
                <label for="tags" class="fw-medium">Enter tags for your image</label>
              </div>
              <div class="form-floating mb-2">
                <textarea class="form-control rounded-3 fw-medium border-0 shadow bg-body-tertiary vh-100" type="text" name="content" id="content" placeholder="Enter description for your image" required></textarea>
                <label for="content" class="fw-medium">Enter description</label>
              </div>
              <button type="submit" class="btn btn-primary w-100 fw-medium">UPLOAD</button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $currentUrl . '?' . http_build_query(array_merge($queryParams, ['page' => 1])); ?>">
          <i class="bi text-stroke bi-chevron-double-left"></i>
        </a>
      <?php endif; ?>

      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $currentUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $prevPage])); ?>">
          <i class="bi text-stroke bi-chevron-left"></i>
        </a>
      <?php endif; ?>

      <?php
        $startPage = max($page - 2, 1);
        $endPage = min($page + 2, $totalPages);
        for ($i = $startPage; $i <= $endPage; $i++) {
          if ($i === $page) {
            echo '<span class="btn btn-sm btn-primary active fw-bold">' . $i . '</span>';
          } else {
            echo '<a class="btn btn-sm btn-primary fw-bold" href="' . $currentUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $i])) . '">' . $i . '</a>';
          }
        }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $currentUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $nextPage])); ?>">
          <i class="bi text-stroke bi-chevron-right"></i>
        </a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $currentUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $totalPages])); ?>">
          <i class="bi text-stroke bi-chevron-double-right"></i>
        </a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>