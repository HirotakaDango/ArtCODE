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
$query = $db->prepare('SELECT artist, `desc`, `bgpic`, pic, twitter, pixiv, other, username FROM users WHERE id = :id');
$query->bindParam(':id', $id);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);
$artist = $user['artist'];
$desc = $user['desc'];
$pic = $user['pic'];
$bgpic = $user['bgpic'];
$twitter = $user['twitter'];
$pixiv = $user['pixiv'];
$other = $user['other'];

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
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  </head>
  <body>
    <?php include('header.php'); ?> 
    <div class="roow mb-2">
      <div class="cool-6 text-center">
        <div class="caard art">
          <div class="col-md-5 order-md-1 mt-3 b-radius" style="background-image: url('<?php echo $bgpic; ?>'); background-size: cover; height: 200px; width: 100%;">
            <img class="img-thumbnail rounded-circle" src="<?php echo !empty($pic) ? $pic : "icon/profile.svg"; ?>" alt="Profile Picture" style="width: 110px; height: 110px; object-fit: cover; border-radius: 4px; margin-top: 45px;">
          </div> 
        </div>
      </div>
      <div class="cool-6 border-down">
        <div class="caard art text-center">
          <h3 class="text-secondary mt-2 fw-bold"><?php echo $artist; ?></h3>
          <form method="post">
            <?php if ($is_following): ?>
              <button class="btn btn-sm btn-danger rounded-pill fw-bold" type="submit" name="unfollow"><i class="bi bi-person-dash-fill"></i> unfollow</button>
            <?php else: ?>
              <button class="btn btn-sm btn-primary rounded-pill fw-bold" type="submit" name="follow"><i class="bi bi-person-fill-add"></i> follow</button>
            <?php endif; ?>
          </form>
          <p class="text-secondary ms-1 mt-2 fw-bold">
            <a class="text-secondary" href="follower.php?id=<?php echo $id; ?>"><?php echo $num_followers ?> followers</a><span style="padding-right: 10px;"></span>
            <a class="text-secondary" href="following.php?id=<?php echo $id; ?>"><?php echo $num_following ?> following</a><span style="padding-right: 10px;"></span> 
            <?php echo count($images); ?> posts
          </p>
          <center><p class="text-secondary fw-bold" style="word-break: break-all; width: 97%;"><?php echo $desc; ?></p><center>
          <ul class="nav justify-content-center pb-3 mb-3">
            <li class="nav-item fw-bold"><a href="<?php echo $twitter; ?>" class="nav-link px-2 text-secondary"><img class="img-sns" width="16" height="16" src="icon/twitter.svg"> Twitter</a></li>
            <li class="nav-item fw-bold"><a href="<?php echo $pixiv; ?>" class="nav-link px-2 text-secondary"><img class="img-sns" width="16" height="16" src="icon/pixiv.svg"> Pixiv</a></li>
            <li class="nav-item fw-bold"><a href="<?php echo $other; ?>" class="nav-link px-2 text-secondary"><img class="img-sns" width="16" height="16" src="icon/globe-asia-australia.svg"> Other</a></li>
          </ul>
        </div>
      </div>
    </div>
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
              <form method="POST">
                <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                <button type="submit" class="p-b3 btn btn-sm rounded btn-dark opacity-50 fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i></button>
              </form>
            <?php } else { ?>
              <form method="POST">
                <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                <button type="submit" class="p-b3 btn btn-sm rounded btn-dark opacity-50 fw-bold" name="favorite"><i class="bi bi-heart"></i></button>
              </form>
            <?php } ?> 
          </div> 
        </div>
      <?php endforeach; ?> 
    </div>
    <div class="mt-5"></div>
    <style>
      .img-sns {
        margin-top: -4px;
      }
    
      @media (min-width: 768px) {
        .p-b3 {
          margin-left: 6px;
          border-radius: 4px;
          margin-top: -71px;
        } 
      }
      
      @media (max-width: 767px) {
        .p-b3 {
          margin-left: 5px;
          border-radius: 4px;
          margin-top: -71px;
        }
      } 

      @media (max-width: 450px) {
        .p-b3 {
          margin-left: 6px;
          border-radius: 4px;
          margin-top: -70px;
        } 
      }

      @media (max-width: 415px) {
        .p-b3 {
          margin-left: 6px;
          border-radius: 4px;
          margin-top: -70px;
        } 
      }

      @media (max-width: 380px) {
        .p-b3 {
          margin-left: 6px;
          border-radius: 4px;
          margin-top: -70px;
        } 
      }
      
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
    
      .roow {
        display: flex;
        flex-wrap: wrap;
        border-radius: 5px;
        border: 2px solid lightgray;
        margin-right: 10px;
        margin-left: 10px;
        margin-top: 10px;
      }

      .cool-6 {
        width: 50%;
        padding: 0 15px;
      }

      .caard {
        background-color: #fff;
        margin-bottom: 15px;
      }
      
      .b-radius {
        border-radius: 10px;
      }

      .art {
        border-radius: 10px;
      }

      @media (max-width: 768px) {
        .roow {
          border: none;
          margin-right: 0;
          margin-left: 0;
          margin-top: -15px;
        }
        
        .cool-6 {
          width: 100%;
          padding: 0;
        }
        
        .b-radius {
          border-right: none;
          border-left: none;
          border-top: 1px solid lightgray;
          border-bottom: 1px solid lightgray; 
          border-radius: 0;
        }
        
        .border-down {
          border-bottom: 2px solid lightgray;
        }
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
