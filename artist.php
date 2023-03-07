<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: session.php");
  exit;
}

$username = $_SESSION['username'];

// Connect to SQLite database
$db = new PDO('sqlite:database.sqlite');

// Get the ID of the selected user from the URL
$id = $_GET['id'];
$query = $db->prepare('SELECT artist, `desc`, `bgpic`, pic, username FROM users WHERE id = :id');
$query->bindParam(':id', $id);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);
$artist = $user['artist'];
$desc = $user['desc'];
$pic = $user['pic'];
$bgpic = $user['bgpic'];

// Get all images for the selected user from the images table
$query = $db->prepare('SELECT images.id, images.filename FROM images JOIN users ON images.username = users.username WHERE users.id = :id ORDER BY images.id DESC');
$query->bindParam(':id', $id);
$query->execute();
$images = $query->fetchAll(PDO::FETCH_ASSOC);

// Check if the logged-in user is already following the selected user
$query = $db->prepare('SELECT COUNT(*) FROM following WHERE follower_username = :follower_username AND following_username = :following_username');
$query->bindParam(':follower_username', $username);
$query->bindParam(':following_username', $user['username']);
$query->execute();
$is_following = $query->fetchColumn();

// Get the number of followers for the selected user
$query = $db->prepare('SELECT COUNT(*) FROM following WHERE following_username = :following_username');
$query->bindParam(':following_username', $user['username']);
$query->execute();
$num_followers = $query->fetchColumn();

// Get the number of people the selected user is following
$query = $db->prepare('SELECT COUNT(*) FROM following WHERE follower_username = :follower_username');
$query->bindParam(':follower_username', $user['username']);
$query->execute();
$num_following = $query->fetchColumn();

// Handle following/unfollowing actions
if (isset($_POST['follow'])) {
  // Add a following relationship between the logged-in user and the selected user
  $query = $db->prepare('INSERT INTO following (follower_username, following_username) VALUES (:follower_username, :following_username)');
  $query->bindParam(':follower_username', $username);
  $query->bindParam(':following_username', $user['username']);
  $query->execute();
  $is_following = true;
  header("Location: artist.php?id={$id}");
  exit;
} elseif (isset($_POST['unfollow'])) {
  // Remove the following relationship between the logged-in user and the selected user
  $query = $db->prepare('DELETE FROM following WHERE follower_username = :follower_username AND following_username = :following_username');
  $query->bindParam(':follower_username', $username);
  $query->bindParam(':following_username', $user['username']);
  $query->execute();
  $is_following = false;
  header("Location: artist.php?id={$id}");
  exit;
} 

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $existing_fav = $db->query("SELECT COUNT(*) FROM favorites WHERE username = '{$_SESSION['username']}' AND image_id = $image_id")->fetchColumn();

  if ($existing_fav == 0) {
    $db->exec("INSERT INTO favorites (username, image_id) VALUES ('{$_SESSION['username']}', $image_id)");
  }

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: artist.php?id={$id}");
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $db->exec("DELETE FROM favorites WHERE username = '{$_SESSION['username']}' AND image_id = $image_id");

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: artist.php?id={$id}");
  exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $artist; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
</head>
<body>
    <center style="margin-bottom: 60px; font-weight: 800; color: gray;">
      <nav class="navbar fixed-top bg-light shadow" style="padding-bottom: 7px; padding-top: 7px;">
        <div class="bb1 container">
          <a class="nav-link" href="forum-chat/index.php"><i class="bi bi-chat-dots-fill"></i></a>
          <a class="nav-link px-2 text-secondary" href="imgupload.php"><i class="bi bi-cloud-arrow-up-fill"></i></a>
          <h1 style="color: gray; margin-top: 7px;" class="nav-link px-2 text-secondary"><a class="nav-link border-bottom" href="index.php">ArtCODE LITE</a></h1>
          <a class="nav-link px-2 text-secondary" href="global.php"><i class="bi bi-images"></i></a>
          <div class="dropdown">
            <a class="nav-link px-2 text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle" style="font-size: 15.5px;"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-start">
              <li><a class="dropdown-item text-secondary fw-bold" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a></li>
              <li><a class="dropdown-item text-secondary fw-bold" href="favorite.php"><i class="bi bi-heart-fill"></i> Favorites</a></li>
              <li><a class="dropdown-item text-secondary fw-bold" href="tags.php"><i class="bi bi-tags-fill"></i> Tags</a></li>
              <li><a class="dropdown-item text-secondary fw-bold" href="users.php"><i class="bi bi-person-fill"></i> Users</a></li>
              <li><a class="dropdown-item text-secondary fw-bold" href="logout.php"><i class="bi bi-door-open-fill"></i> Logout</a></li>
            </ul>
          </div>
        </div>
      </nav>
    </center>  
    <center>
      <div class="container-fluid w-auto mb-2">
        <div class="row justify-content-center">
          <div>
            <div class="card">
              <div class="card-body">
                <div class="row featurette container">
                  <div class="col-md-5 order-md-1 img-thumbnail" style="background-image: url('<?php echo $bgpic; ?>'); background-size: cover; height: 180px;">
                    <img class="img-thumbnail mt-4 rounded-circle" src="<?php echo $pic; ?>" alt="Profile Picture" style="width: 120px; height: 120px; border-radius: 4px;">
                  </div>
                  <div class="col-md-7 order-md-2">
                    <h3 class="text-secondary mt-2 fw-bold"><?php echo $artist; ?></h3> 
                    <form method="post">
                      <?php if ($is_following): ?>
                        <button class="btn btn-danger rounded-pill fw-bold" type="submit" name="unfollow"><i class="bi bi-person-dash-fill"></i> unfollow</button>
                      <?php else: ?>
                        <button class="btn btn-primary rounded-pill fw-bold" type="submit" name="follow"><i class="bi bi-person-fill-add"></i> follow</button>
                      <?php endif; ?>
                    </form>    
                    <h5 class="text-secondary ms-1 mt-2 fw-bold"><?php echo $num_followers ?> <i class="bi bi-people-fill me-5"></i> <?php echo $num_following ?> <i class="bi bi-person-fill me-5"></i> <?php echo count($images); ?> <i class="bi bi-images"></i></h5>
                    <p class="text-secondary text-center fw-bold"><?php echo $desc; ?></p>
                  </div>
                </div> 
              </div>
            </div>
          </div>
        </div>
      </div>
    </center>
    <div class="images">
      <?php foreach ($images as $image): ?> 
        <div class="image-container">
          <a href="image.php?filename=<?php echo $image['filename']; ?>">
            <img class="lazy-load" data-src="thumbnails/<?php echo $image['filename']; ?>">
          </a>
          <div class="favorite-btn">
            <?php
              $is_favorited = $db->query("SELECT COUNT(*) FROM favorites WHERE username = '{$_SESSION['username']}' AND image_id = {$image['id']}")->fetchColumn();
              if ($is_favorited) {
            ?>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . urlencode($id); ?>" method="POST">
              <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
              <button style="margin-top: -74px; margin-left: 8px; font-size: 10px;" type="submit" class="btn btn-danger rounded-5 fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i></button>
            </form>
            <?php } else { ?>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . urlencode($id); ?>" method="POST">
              <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
              <button style="margin-top: -74px; margin-left: 8px; font-size: 10px;" type="submit" class="btn btn-danger rounded-5 fw-bold" name="favorite"><i class="bi bi-heart"></i></button>
            </form>
            <?php } ?>
          </div> 
        </div>
      <?php endforeach; ?> 
    </div>
    
    <style>
    .image-container {
      margin-bottom: -24px;  
    }
      
    .images {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
      grid-gap: 2px;
      justify-content: center;
      margin-right: 3px;
      margin-left: 3px;
    }

    .images a {
      display: block;
      border-radius: 4px;
      overflow: hidden;
      border: 2px solid #ccc;
    }

    .images img {
      width: 100%;
      height: auto;
      object-fit: cover;
      height: 200px;
      transition: transform 0.5s ease-in-out;
    }
    </style>
    <script>
      document.addEventListener("DOMContentLoaded", function() {
        let lazyloadImages;
        if("IntersectionObserver" in window) {
          lazyloadImages = document.querySelectorAll(".lazy-load");
          let imageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
              if(entry.isIntersecting) {
                let image = entry.target;
                image.src = image.dataset.src;
                image.classList.remove("lazy-load");
                imageObserver.unobserve(image);
              }
            });
          });
          lazyloadImages.forEach(function(image) {
            imageObserver.observe(image);
          });
        } else {
          let lazyloadThrottleTimeout;
          lazyloadImages = document.querySelectorAll(".lazy-load");

          function lazyload() {
            if(lazyloadThrottleTimeout) {
              clearTimeout(lazyloadThrottleTimeout);
            }
            lazyloadThrottleTimeout = setTimeout(function() {
              let scrollTop = window.pageYOffset;
              lazyloadImages.forEach(function(img) {
                if(img.offsetTop < (window.innerHeight + scrollTop)) {
                  img.src = img.dataset.src;
                  img.classList.remove('lazy-load');
                }
              });
              if(lazyloadImages.length == 0) {
                document.removeEventListener("scroll", lazyload);
                window.removeEventListener("resize", lazyload);
                window.removeEventListener("orientationChange", lazyload);
              }
            }, 20);
          }
          document.addEventListener("scroll", lazyload);
          window.addEventListener("resize", lazyload);
          window.addEventListener("orientationChange", lazyload);
        }
      })
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
</body>
</html>
