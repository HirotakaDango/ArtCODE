<?php
require_once('../auth.php');

// Connect to the database using PDO
$db = new PDO('sqlite:../database.sqlite');

// Get the filename from the query string
$artworkId = $_GET['artworkid'] ?? null;

if (!$artworkId) {
  header("Location: /my-private/~");
  exit;
}

// Check if the user is logged in and get their email
if (!isset($_SESSION['email'])) {
  header("Location: /my-private/~");
  exit;
}
$current_email = $_SESSION['email'];

// Get the image info
$stmt = $db->prepare("SELECT * FROM private_images WHERE id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$image = $stmt->fetch();

// Check if the image exists and belongs to the current user
if (!$image || $image['email'] !== $current_email) {
  header("Location: /my-private/~");
  exit;
}

$image_id = $image['id'];

// Previous and next images (only for current user)
$stmt = $db->prepare("SELECT * FROM private_images WHERE id < :id AND email = :email ORDER BY id DESC LIMIT 1");
$stmt->bindParam(':id', $image_id);
$stmt->bindParam(':email', $current_email);
$stmt->execute();
$prev_image = $stmt->fetch();

$stmt = $db->prepare("SELECT * FROM private_images WHERE id > :id AND email = :email ORDER BY id ASC LIMIT 1");
$stmt->bindParam(':id', $image_id);
$stmt->bindParam(':email', $current_email);
$stmt->execute();
$next_image = $stmt->fetch();

// Get the owner info
$query = $db->prepare('SELECT * FROM users WHERE email = :email');
$query->bindParam(':email', $current_email);
$query->execute();
$user = $query->fetch();

// Handle following/unfollowing actions (can only follow self in private mode, so skip this logic)
// Handle favorite/unfavorite actions
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];
  $stmt = $db->prepare("SELECT COUNT(*) FROM private_favorites WHERE email = :email AND image_id = :image_id");
  $stmt->bindParam(':email', $current_email);
  $stmt->bindParam(':image_id', $image_id);
  $stmt->execute();
  $existing_fav = $stmt->fetchColumn();

  if ($existing_fav == 0) {
    $stmt = $db->prepare("INSERT INTO private_favorites (email, image_id) VALUES (:email, :image_id)");
    $stmt->bindParam(':email', $current_email);
    $stmt->bindParam(':image_id', $image_id);
    $stmt->execute();
  }
  header("Location: ?artworkid={$image['id']}");
  exit;
} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $stmt = $db->prepare("DELETE FROM private_favorites WHERE email = :email AND image_id = :image_id");
  $stmt->bindParam(':email', $current_email);
  $stmt->bindParam(':image_id', $image_id);
  $stmt->execute();
  header("Location: ?artworkid={$image['id']}");
  exit;
}

// Increment the view count for the image
$stmt = $db->prepare("UPDATE private_images SET view_count = view_count + 1 WHERE id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();

$stmt = $db->prepare("SELECT * FROM private_images WHERE id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$image = $stmt->fetch();
$viewCount = $image['view_count'];

// History tracking
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS history (id INTEGER PRIMARY KEY AUTOINCREMENT, history TEXT, email TEXT, image_artworkid TEXT, date_history DATETIME)");
$stmt->execute();

$uri = $_SERVER['REQUEST_URI'];
$currentDate = date('Y-m-d');

$stmt = $db->prepare("SELECT * FROM history WHERE history = :history AND image_artworkid = :artworkId AND email = :email AND date_history = :date_history");
$stmt->bindParam(':history', $uri);
$stmt->bindParam(':artworkId', $artworkId);
$stmt->bindParam(':email', $current_email);
$stmt->bindParam(':date_history', $currentDate);
$stmt->execute();
$existing_entry = $stmt->fetch();

if (!$existing_entry) {
  $stmt = $db->prepare("INSERT INTO history (history, email, image_artworkid, date_history) VALUES (:history, :email, :artworkId, :date_history)");
  $stmt->bindParam(':history', $uri);
  $stmt->bindParam(':email', $current_email);
  $stmt->bindParam(':artworkId', $artworkId);
  $stmt->bindParam(':date_history', $currentDate);
  $stmt->execute();
}

// Get all child images
$stmt = $db->prepare("SELECT * FROM private_image_child WHERE image_id = :image_id");
$stmt->bindParam(':image_id', $image_id);
$stmt->execute();
$child_images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count images (for this artworkid, only for current user)
$stmt = $db->prepare("SELECT COUNT(*) as total_images FROM private_images WHERE id = :artworkid AND email = :email");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->bindParam(':email', $current_email);
$stmt->execute();
$total_images = $stmt->fetch()['total_images'];

$stmt = $db->prepare("SELECT COUNT(*) as total_child_images FROM private_image_child WHERE image_id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$total_child_images = $stmt->fetch()['total_child_images'];

$total_all_images = $total_images + $total_child_images;

// Get image size
$original_image_size = round(filesize('../private_images/' . $image['filename']) / (1024 * 1024), 2);
$thumbnail_image_size = round(filesize('../private_thumbnails/' . $image['filename']) / (1024 * 1024), 2);
$reduction_percentage = $original_image_size > 0 ?
  ((($original_image_size - $thumbnail_image_size) / $original_image_size) * 100) : 0;

// Get image dimensions
list($width, $height) = getimagesize('../private_images/' . $image['filename']);

// Daily view tracking
$currentDate = date('Y-m-d');
$stmt = $db->prepare("SELECT * FROM private_daily WHERE image_id = :image_id AND date = :date");
$stmt->bindParam(':image_id', $image['id']);
$stmt->bindParam(':date', $currentDate);
$stmt->execute();
$daily_view = $stmt->fetch();

if ($daily_view) {
  $stmt = $db->prepare("UPDATE private_daily SET views = views + 1 WHERE id = :id");
  $stmt->bindParam(':id', $daily_view['id']);
  $stmt->execute();
} else {
  $stmt = $db->prepare("INSERT INTO private_daily (image_id, views, date) VALUES (:image_id, 1, :date)");
  $stmt->bindParam(':image_id', $image['id']);
  $stmt->bindParam(':date', $currentDate);
  $stmt->execute();
}

// New Section: Extract the filename only (e.g., d7361061dd44_i0.png)
$filenameWithoutExtension = preg_replace('/.*\/([^\/]+)$/i', '$1', $image['filename']);

// New Section: Extract the base path up to `imageassets_<unique_id>` (e.g., uid_1/data/imageid-24/imageassets_d7361061dd44)
$baseFilename = preg_replace('/(.*imageassets_[^\/]+)\/[^\/]+$/i', '$1', $image['filename']);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $image['title']; ?></title>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
    <style>
      body, html {
        padding: 0;
        margin: 0;
        width: 100%;
        height: 100%;
        overflow: hidden; /* Remove scrollbars */
      }
      iframe {
        border: none; /* Remove default border */
        width: 100%;
        height: 100%;
        display: block; /* Ensure iframe takes up the full container */
      }
      .spinner-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
        width: 100%;
        background-color: #fff; /* Optional: background color for better visibility */
        position: absolute;
      }
    </style>
  </head>
  <body>
    <div class="spinner-container" id="spinner">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
    <iframe src="/private_images/viewer.php?<?php echo $baseFilename; ?>#pid=<?php echo $filenameWithoutExtension; ?>" sandbox="allow-scripts allow-same-origin" onload="hideSpinner()"></iframe>
    <button class="position-absolute end-0 bottom-0 m-3 btn btn-primary rounded-pill fw-bold btn-sm" onclick="window.location.reload();"><i class="bi bi-arrow-clockwise"></i></button>
  
    <script>
      function hideSpinner() {
        document.getElementById('spinner').style.display = 'none';
      }
    </script>
  </body>
</html>