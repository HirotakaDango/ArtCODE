<?php
require_once('auth.php');

// Connect to the database using PDO
$db = new PDO('sqlite:database.sqlite');

// Get the filename from the query string
$artworkId = $_GET['artworkid'];

// Get the current image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id = :artworkid ");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$image = $stmt->fetch();

// Check if the image exists in the database
if (!$image) {
  header("Location: error.php");
  exit; // Stop further execution
}

// Get the ID of the current image and the email of the owner
$image_id = $image['id'];
$email = $image['email'];

// Get the previous image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id < :id AND email = :email ORDER BY id DESC LIMIT 1");
$stmt->bindParam(':id', $image_id);
$stmt->bindParam(':email', $email);
$stmt->execute();
$prev_image = $stmt->fetch();

// Get the next image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id > :id AND email = :email ORDER BY id ASC LIMIT 1");
$stmt->bindParam(':id', $image_id);
$stmt->bindParam(':email', $email);
$stmt->execute();
$next_image = $stmt->fetch();

// Get the image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$image = $stmt->fetch();
$image_id = $image['id'];

// Check if the user is logged in and get their email
$email = '';
if (isset($_SESSION['email'])) {
  $email = $_SESSION['email'];
}

// Get the email of the selected user
$user_email = $image['email'];

// Get the selected user's information from the database
$query = $db->prepare('SELECT * FROM users WHERE email = :email');
$query->bindParam(':email', $user_email);
$query->execute();
$user = $query->fetch();

// Check if the logged-in user is already following the selected user
$query = $db->prepare('SELECT COUNT(*) FROM following WHERE follower_email = :follower_email AND following_email = :following_email');
$query->bindParam(':follower_email', $email);
$query->bindParam(':following_email', $user_email);
$query->execute();
$is_following = $query->fetchColumn();

// Handle following/unfollowing actions
if (isset($_POST['follow'])) {
  // Add a following relationship between the logged-in user and the selected user
  $query = $db->prepare('INSERT INTO following (follower_email, following_email) VALUES (:follower_email, :following_email)');
  $query->bindParam(':follower_email', $email);
  $query->bindParam(':following_email', $user_email);
  $query->execute();
  $is_following = true;
  header("Location: ?artworkid={$image['id']}");
  exit;
} elseif (isset($_POST['unfollow'])) {
  // Remove the following relationship between the logged-in user and the selected user
  $query = $db->prepare('DELETE FROM following WHERE follower_email = :follower_email AND following_email = :following_email');
  $query->bindParam(':follower_email', $email);
  $query->bindParam(':following_email', $user_email);
  $query->execute();
  $is_following = false;
  header("Location: ?artworkid={$image['id']}");
  exit;
}

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE email = :email AND image_id = :image_id");
  $stmt->bindParam(':email', $_SESSION['email']);
  $stmt->bindParam(':image_id', $image_id);
  $stmt->execute();
  $existing_fav = $stmt->fetchColumn();

  if ($existing_fav == 0) {
    $stmt = $db->prepare("INSERT INTO favorites (email, image_id) VALUES (:email, :image_id)");
    $stmt->bindParam(':email', $_SESSION['email']);
    $stmt->bindParam(':image_id', $image_id);
    $stmt->execute();
  }

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: ?artworkid={$image['id']}");
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $stmt = $db->prepare("DELETE FROM favorites WHERE email = :email AND image_id = :image_id");
  $stmt->bindParam(':email', $_SESSION['email']);
  $stmt->bindParam(':image_id', $image_id);
  $stmt->execute();

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: ?artworkid={$image['id']}");
  exit();
}

$url_comment = "comments_preview.php?imageid=" . $image_id;

// Increment the view count for the image
$stmt = $db->prepare("UPDATE images SET view_count = view_count + 1 WHERE id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();

// Get the updated image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$image = $stmt->fetch();

// Retrieve the updated view count from the image information
$viewCount = $image['view_count'];

// Create the "history" table if it does not exist
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS history (id INTEGER PRIMARY KEY AUTOINCREMENT, history TEXT, email TEXT, image_artworkid TEXT, date_history DATETIME)");
$stmt->execute();

// Store the link URL and image ID into the "history" table
if (isset($_GET['artworkid'])) {
  $artworkId = $_GET['artworkid'];
  $uri = $_SERVER['REQUEST_URI'];
  $email = $_SESSION['email'];
  $currentDate = date('Y-m-d'); // Get the current date

  // Check if the same URL and image ID exist in the history for the current day
  $stmt = $db->prepare("SELECT * FROM history WHERE history = :history AND image_artworkid = :artworkId AND email = :email AND date_history = :date_history");
  $stmt->bindParam(':history', $uri);
  $stmt->bindParam(':artworkId', $artworkId);
  $stmt->bindParam(':email', $email);
  $stmt->bindParam(':date_history', $currentDate);
  $stmt->execute();
  $existing_entry = $stmt->fetch();

  if (!$existing_entry) {
    // Insert the URL and image ID into the history table
    $stmt = $db->prepare("INSERT INTO history (history, email, image_artworkid, date_history) VALUES (:history, :email, :artworkId, :date_history)");
    $stmt->bindParam(':history', $uri);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':artworkId', $artworkId);
    $stmt->bindParam(':date_history', $currentDate);
    $stmt->execute();
  }
}

// Get all child images associated with the current image from the "image_child" table
$stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :image_id");
$stmt->bindParam(':image_id', $image_id);
$stmt->execute();
$child_images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count the total number of images from "images" table for the specific artworkid
$stmt = $db->prepare("SELECT COUNT(*) as total_images FROM images WHERE id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$total_images = $stmt->fetch()['total_images'];

// Count the total number of images from "image_child" table for the specific artworkid
$stmt = $db->prepare("SELECT COUNT(*) as total_child_images FROM image_child WHERE image_id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$total_child_images = $stmt->fetch()['total_child_images'];

// Calculate the combined total
$total_all_images = $total_images + $total_child_images;

// Get image size of the original image in megabytes
$original_image_size = round(filesize('images/' . $image['filename']) / (1024 * 1024), 2);

// Get image size of the thumbnail in megabytes
$thumbnail_image_size = round(filesize('thumbnails/' . $image['filename']) / (1024 * 1024), 2);

// Calculate the percentage of reduction
$reduction_percentage = ((($original_image_size - $thumbnail_image_size) / $original_image_size) * 100);

// Get image dimensions
list($width, $height) = getimagesize('images/' . $image['filename']);

// Get the current date
$currentDate = date('Y-m-d');

// Check if there's already a record for today in the daily table
$stmt = $db->prepare("SELECT * FROM daily WHERE image_id = :image_id AND date = :date");
$stmt->bindParam(':image_id', $image['id']);
$stmt->bindParam(':date', $currentDate);
$stmt->execute();
$daily_view = $stmt->fetch();

if ($daily_view) {
  // If there's already a record for today, increment the view count
  $stmt = $db->prepare("UPDATE daily SET views = views + 1 WHERE id = :id");
  $stmt->bindParam(':id', $daily_view['id']);
  $stmt->execute();
} else {
  // If there's no record for today, insert a new record
  $stmt = $db->prepare("INSERT INTO daily (image_id, views, date) VALUES (:image_id, 1, :date)");
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
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
    <script type="module" src="/swup/swup.js"></script>
    <style>
      html.is-changing .transition-main {
        transition: opacity 2ms ease-in-out;
      }
    </style>
  </head>
  <body>
    <?php include('header.php'); ?>
    <main id="swup" class="transition-main">
    <div class="mt-2">
      <div class="container-fluid mb-2 d-flex d-md-none d-lg-none">
        <?php
          $stmt = $db->prepare("SELECT u.id, u.email, u.password, u.artist, u.pic, u.desc, u.bgpic, i.id AS image_id, i.filename, i.tags FROM users u INNER JOIN images i ON u.id = i.id WHERE u.id = :id");
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
        <div class="ms-auto">
          <form method="post">
            <?php if ($is_following): ?>
              <button class="btn btn-sm btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-pill fw-bold opacity-75" type="submit" name="unfollow"><i class="bi bi-person-dash-fill"></i> unfollow</button>
            <?php else: ?>
              <button class="btn btn-sm btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-pill fw-bold opacity-75" type="submit" name="follow"><i class="bi bi-person-fill-add"></i> follow</button>
            <?php endif; ?>
          </form>
        </div>
      </div>
      <?php include('image_bio.php'); ?>
      <div class="roow">
        <?php include('image_iframe.php'); ?>
        <?php include('demo_image_information.php'); ?>
      </div>
    </div>
    </main>
    <div class="mt-5"></div>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>