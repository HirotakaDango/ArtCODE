<?php
// Start a session to check if the user is logged in
session_start();

// Get the filename from the query string
$filename = $_GET['filename'];

// Connect to the database using PDO
$db = new PDO('sqlite:database.sqlite');

// Get the image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE filename = :filename");
$stmt->bindParam(':filename', $filename);
$stmt->execute();
$image = $stmt->fetch();

// Check if the user is logged in and get their username
$username = '';
if (isset($_SESSION['username'])) {
  $username = $_SESSION['username'];
}

// Get the username of the selected user
$user_username = $image['username'];

// Get the selected user's information from the database
$query = $db->prepare('SELECT * FROM users WHERE username = :username');
$query->bindParam(':username', $user_username);
$query->execute();
$user = $query->fetch();

// Check if the logged-in user is already following the selected user
$query = $db->prepare('SELECT COUNT(*) FROM following WHERE follower_username = :follower_username AND following_username = :following_username');
$query->bindParam(':follower_username', $username);
$query->bindParam(':following_username', $user_username);
$query->execute();
$is_following = $query->fetchColumn();

// Handle following/unfollowing actions
if (isset($_POST['follow'])) {
  // Add a following relationship between the logged-in user and the selected user
  $query = $db->prepare('INSERT INTO following (follower_username, following_username) VALUES (:follower_username, :following_username)');
  $query->bindParam(':follower_username', $username);
  $query->bindParam(':following_username', $user_username);
  $query->execute();
  $is_following = true;
  header("Location: image.php?filename={$image['filename']}");
  exit;
} elseif (isset($_POST['unfollow'])) {
  // Remove the following relationship between the logged-in user and the selected user
  $query = $db->prepare('DELETE FROM following WHERE follower_username = :follower_username AND following_username = :following_username');
  $query->bindParam(':follower_username', $username);
  $query->bindParam(':following_username', $user_username);
  $query->execute();
  $is_following = false;
  header("Location: image.php?filename={$image['filename']}");
  exit;
} 
// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE username = :username AND image_id = :image_id");
  $stmt->bindParam(':username', $_SESSION['username']);
  $stmt->bindParam(':image_id', $image_id);
  $stmt->execute();
  $existing_fav = $stmt->fetchColumn();

  if ($existing_fav == 0) {
    $stmt = $db->prepare("INSERT INTO favorites (username, image_id) VALUES (:username, :image_id)");
    $stmt->bindParam(':username', $_SESSION['username']);
    $stmt->bindParam(':image_id', $image_id);
    $stmt->execute();
  }

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: image.php?filename={$image['filename']}");
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $stmt = $db->prepare("DELETE FROM favorites WHERE username = :username AND image_id = :image_id");
  $stmt->bindParam(':username', $_SESSION['username']);
  $stmt->bindParam(':image_id', $image_id);
  $stmt->execute();

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: image.php?filename={$image['filename']}");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"> 
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Image</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  <style>
    .tag-buttons {
      display: flex;
      flex-wrap: wrap;
    }

    .tag-button {
      display: inline-block;
      padding: 6px 12px;
      margin: 6px;
      background-color: #eee;
      color: #333;
      border-radius: 3px;
      text-decoration: none;
      font-size: 14px;
      line-height: 1;
      font-weight: 800;
    }

    .tag-button:hover {
      background-color: #ccc;
    }

    .tag-button:active {
      background-color: #aaa;
    }
  </style>
</head>
<body>
  <center style="margin-bottom: 60px; font-weight: 800; color: gray;">
    <nav class="navbar fixed-top bg-light shadow" style="padding-bottom: 7px; padding-top: 7px;">
      <div class="bb1 container">
        <a class="nav-link" href="forum-chat/index.php"><i class="bi bi-chat-dots-fill"></i></a>
        <a class="nav-link px-2 text-secondary" href="imgupload.php"><i class="bi bi-cloud-arrow-up-fill"></i></a>
        <h1 style="color: gray; margin-top: 7px;" class="nav-link px-2 text-secondary"><a class="nav-link border-bottom" href="index.php">ArtCODE LITE</a></h1>
        <a class="nav-link px-2 text-secondary" href="users.php"><i class="bi bi-person-fill"></i></a>
        <div class="dropdown">
          <a class="nav-link px-2 text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle" style="font-size: 15.5px;"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-start">
            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a></li>
            <li><a class="dropdown-item" href="favorite.php"><i class="bi bi-heart-fill"></i> Favorites</a></li>
            <li><a class="dropdown-item" href="tags.php"><i class="bi bi-tags-fill"></i> Tags</a></li>
            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-door-open-fill"></i> Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>
  </center>  
  <div>
    <div class="container mb-2" style="display: flex; align-items: center;">
      <?php
        $stmt = $db->prepare("SELECT id, artist, pic FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
      ?>
      <div style="display: flex; align-items: center;">
        <i class="bi bi-person-circle me-2"></i>
        <a class="text-secondary fw-bold" href="artist.php?id=<?= $user['id'] ?>"><?php echo $user['artist']; ?></a>
      </div>
      <div style="margin-left: auto;">
        <form method="post">
          <?php if ($is_following): ?>
            <button class="btn btn-sm btn-danger rounded-pill fw-bold" type="submit" name="unfollow"><i class="bi bi-person-dash-fill"></i> unfollow</button>
          <?php else: ?>
            <button class="btn btn-sm btn-primary rounded-pill fw-bold" type="submit" name="follow"><i class="bi bi-person-fill-add"></i> follow</button>
          <?php endif; ?>
        </form>
      </div>
    </div>
    <a href="images/<?php echo $filename; ?>">
      <img class="img-fluid" src="thumbnails/<?php echo $filename; ?>" width="100%" height="auto">
    </a>
    <div class="favorite-btn">
    <?php
      $stmt = $db->query("SELECT COUNT(*) FROM favorites WHERE username = '$username' AND image_id = {$image['id']}");
      $is_favorited = $stmt->fetchColumn();
      if ($is_favorited) {
    ?>
      <form action="image.php?filename=<?php echo $image['filename']; ?>" method="POST">
        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
        <button style="margin-top: -74px; margin-left: 8px; font-size: 10px;" type="submit" class="btn btn-danger rounded-5 fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i></button>
      </form>
    <?php } else { ?>
      <form action="image.php?filename=<?php echo $image['filename']; ?>" method="POST">
        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
        <button style="margin-top: -74px; margin-left: 8px; font-size: 10px;" type="submit" class="btn btn-danger rounded-5 fw-bold" name="favorite"><i class="bi bi-heart"></i></button>
      </form>
    <?php } ?>
    </div> 
    <div class="tag-buttons container">
    <?php
      $tags = explode(',', $image['tags']);
      foreach ($tags as $tag) {
        $tag = trim($tag);
        if (!empty($tag)) {
    ?>
        <a href="tagged_images.php?tag=<?php echo urlencode($tag); ?>"
           class="tag-button">
          <?php echo $tag; ?>
        </a>
    <?php
        }
      }
    ?>
    </div>
  </div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>

