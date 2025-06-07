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
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $image['title']; ?></title>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
    <script type="module" src="/swup/swup.js"></script>
    <style>
      html.is-changing .transition-main {
        transition: opacity 2ms ease-in-out;
      }
    </style>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <main id="swup" class="transition-main">
    <div class="mt-2">
      <div class="container-fluid mb-2 d-flex d-md-none d-lg-none">
        <?php
          $stmt = $db->prepare("SELECT u.id, u.email, u.password, u.artist, u.pic, u.desc, u.bgpic, i.id AS image_id, i.filename, i.tags FROM users u INNER JOIN private_images i ON u.id = i.id WHERE u.id = :id");
          $stmt->bindParam(':id', $id);
          $stmt->execute();
          $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="d-flex">
          <a class="text-decoration-none text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold rounded-pill" href="#" data-bs-toggle="modal" data-bs-target="#userModal">
            <?php if (!empty($user['pic'])): ?>
              <img class="object-fit-cover border border-1 rounded-circle" src="<?php echo $user['pic']; ?>" style="width: 32px; height: 32px;">
            <?php else: ?>
              <img class="object-fit-cover border border-1 rounded-circle" src="icon/profile.svg" style="width: 32px; height: 32px;">
            <?php endif; ?>
            <?php echo (mb_strlen($user['artist']) > 10) ? mb_substr($user['artist'], 0, 10) . '...' : $user['artist']; ?> <small class="badge rounded-pill text-bg-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>"><i class="bi bi-globe-asia-australia"></i> <?php echo $user['region']; ?></small>
          </a>
        </div>
      </div>
      <?php include('image_bio.php'); ?>
      <div class="roow">
        <?php include('image_iframe.php'); ?>
        <?php include('image_information.php'); ?>
      </div>
    </div>
    </main>
    <div class="mt-5"></div>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>