<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: session.php");
  exit;
}

$username = $_SESSION['username'];
  
// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Retrieve the tag from the URL parameter
$tag = htmlspecialchars($_GET['tag']);

// Retrieve all images with the specified tag
$stmt = $db->prepare("SELECT * FROM images WHERE tags LIKE :tag ORDER BY id DESC");
$stmt->bindValue(':tag', "%{$tag}%");
$result = $stmt->execute();

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $existing_fav = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE username = '$username' AND image_id = $image_id");

  if ($existing_fav == 0) {
    $db->exec("INSERT INTO favorites (username, image_id) VALUES ('$username', $image_id)");
  }

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: tagged_images.php?tag={$tag}");
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $db->exec("DELETE FROM favorites WHERE username = '$username' AND image_id = $image_id");

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: tagged_images.php?tag={$tag}");
  exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8"> 
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
</head>
<body>  
  <center style="margin-bottom: 60px; font-weight: 800; color: gray;">
    <nav class="navbar fixed-top bg-light shadow" style="padding-bottom: 7px; padding-top: 7px;">
      <div class="container">
        <a class="nav-link px-2 text-secondary" href="forum-chat/index.php"><i class="bi bi-chat-dots-fill"></i></a>
        <a class="nav-link px-2 text-secondary" href="imgupload.php"><i class="bi bi-cloud-arrow-up-fill"></i></a>
        <h1 style="color: gray; margin-top: 7px;" class="nav-link px-2 text-secondary"><a class="nav-link border-bottom" href="index.php">ArtCODE</a></h1>
        <a class="nav-link text-secondary" href="global.php"><i class="bi bi-images"></i></a>
        <div class="dropdown">
          <a class="nav-link px-2 text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle" style="font-size: 15.5px;"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-start">
            <li><a class="dropdown-item text-secondary fw-bold mt-1" href="popular.php"><i class="bi bi-graph-up"></i> Popular</a></li>
            <li><a class="dropdown-item text-secondary fw-bold" href="tags.php"><i class="bi bi-tags-fill"></i> Tags</a></li>
            <li><a class="dropdown-item text-secondary fw-bold border-bottom" href="users.php"><i class="bi bi-person-fill"></i> Users</a></li>
            <li><a class="dropdown-item text-secondary fw-bold" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a></li>
            <li><a class="dropdown-item text-secondary fw-bold" href="favorite.php"><i class="bi bi-heart-fill"></i> Favorites</a></li>
            <li><a class="dropdown-item text-secondary fw-bold" href="logout.php"><i class="bi bi-door-open-fill"></i> Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>
  </center>
  <h5 class="text-secondary ms-2 fw-bold"><i class="bi bi-tags-fill"></i> <?php echo $tag; ?></h5>
  <!-- Display the images -->
  <div class="images">
    <?php while ($image = $result->fetchArray()): ?>
      <div class="image-container">
        <a href="image.php?filename=<?php echo $image['filename']; ?>">
          <img class="lazy-load" data-src="thumbnails/<?php echo $image['filename']; ?>">
        </a>
        <div class="favorite-btn">
          <?php
            $is_favorited = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE username = '$username' AND image_id = {$image['id']}");
            if ($is_favorited) {
          ?>
            <form action="favindex.php" method="POST">
              <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
              <button style="margin-top: -74px; margin-left: 8px; font-size: 10px;" type="submit" class="btn btn-danger rounded-5 fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i></button>
            </form>
          <?php } else { ?>
            <form action="favindex.php" method="POST">
              <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
              <button style="margin-top: -74px; margin-left: 8px; font-size: 10px;" type="submit" class="btn btn-danger rounded-5 fw-bold" name="favorite"><i class="bi bi-heart"></i></button>
            </form>
          <?php } ?>
        </div>
      </div>
    <?php endwhile; ?>
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