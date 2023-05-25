<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}

$email = $_SESSION['email'];

// Connect to SQLite database
$db = new PDO('sqlite:database.sqlite');

// Get the ID of the selected user from the URL
$id = $_GET['id'];
$query = $db->prepare('SELECT artist, `desc`, `bgpic`, pic, twitter, pixiv, other, email FROM users WHERE id = :id');
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
$query = $db->prepare('SELECT images.id, images.filename, images.title, images.imgdesc FROM images JOIN users ON images.email = users.email WHERE users.id = :id ORDER BY images.id DESC');
$query->bindParam(':id', $id);
$query->execute();
$images = $query->fetchAll(PDO::FETCH_ASSOC);

// Check if the logged-in user is already following the selected user
$query = $db->prepare('SELECT COUNT(*) FROM following WHERE follower_email = :follower_email AND following_email = :following_email');
$query->bindParam(':follower_email', $email);
$query->bindParam(':following_email', $user['email']);
$query->execute();
$is_following = $query->fetchColumn();

// Function to format numbers
function formatNumber($num) {
  if ($num >= 1000000) {
    return round($num / 1000000, 1) . 'm';
  } elseif ($num >= 100000) {
    return round($num / 1000) . 'k';
  } elseif ($num >= 10000) {
    return round($num / 1000, 1) . 'k';
  } elseif ($num >= 1000) {
    return round($num / 1000) . 'k';
  } else {
    return $num;
  }
}

// Get the number of followers for the selected user
$query = $db->prepare('SELECT COUNT(*) FROM following WHERE following_email = :following_email');
$query->bindParam(':following_email', $user['email']);
$query->execute();
$num_followers = $query->fetchColumn();

// Get the number of people the selected user is following
$query = $db->prepare('SELECT COUNT(*) FROM following WHERE follower_email = :follower_email');
$query->bindParam(':follower_email', $user['email']);
$query->execute();
$num_following = $query->fetchColumn(); 

// Handle following/unfollowing actions
if (isset($_POST['follow'])) {
  // Add a following relationship between the logged-in user and the selected user
  $query = $db->prepare('INSERT INTO following (follower_email, following_email) VALUES (:follower_email, :following_email)');
  $query->bindParam(':follower_email', $email);
  $query->bindParam(':following_email', $user['email']);
  $query->execute();
  $is_following = true;
  header("Location: artist.php?id={$id}");
  exit;
} elseif (isset($_POST['unfollow'])) {
  // Remove the following relationship between the logged-in user and the selected user
  $query = $db->prepare('DELETE FROM following WHERE follower_email = :follower_email AND following_email = :following_email');
  $query->bindParam(':follower_email', $email);
  $query->bindParam(':following_email', $user['email']);
  $query->execute();
  $is_following = false;
  header("Location: artist.php?id={$id}");
  exit;
} 

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $existing_fav = $db->query("SELECT COUNT(*) FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = $image_id")->fetchColumn();

  if ($existing_fav == 0) {
    $db->exec("INSERT INTO favorites (email, image_id) VALUES ('{$_SESSION['email']}', $image_id)");
  }

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: artist.php?id={$id}");
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $db->exec("DELETE FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = $image_id");

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: artist.php?id={$id}");
  exit();
}

$fav_count = $db->query("SELECT COUNT(*) FROM favorites WHERE email = '{$_SESSION['email']}'")->fetchColumn();
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $artist; ?></title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?> 
    <div class="roow mb-2">
      <div class="cool-6 text-center">
        <div class="caard art">
          <div class="col-md-5 order-md-1 mt-3 b-radius" style="background-image: url('<?php echo $bgpic; ?>'); background-size: cover; height: 200px; width: 100%;">
            <img class="img-thumbnail rounded-circle" src="<?php echo !empty($pic) ? $pic : "icon/profile.svg"; ?>" alt="Profile Picture" style="width: 110px; height: 110px; object-fit: cover; border-radius: 4px; margin-top: 45px;">
          </div> 
          <div class="btn-group d-none-sm-b b-section" role="group" aria-label="Basic example">
            <a class="btn btn-sm btn-secondary disabled rounded opacity-50 fw-bold"><i class="bi bi-images"></i> <?php echo count($images); ?> <small>images</small></a>
            <a class="btn btn-sm btn-secondary ms-1 rounded fw-bold opacity-50" href="list_favorite.php?id=<?php echo $id; ?>"><i class="bi bi-heart"></i> <?php echo $fav_count;?> <small>favorites</small></a> 
            <button class="btn btn-sm btn-secondary ms-1 rounded fw-bold opacity-50" onclick="sharePage()"><i class="bi bi-share-fill"></i> <small>share</small></button>
          </div>
        </div>
      </div>
      <div class="cool-6">
        <div class="caard art text-center">
          <h3 class="text-secondary mt-2 fw-bold"><?php echo $artist; ?></h3>
          <p class="text-center text-muted fw-semibold"><small>user id: <?php echo $id; ?></small></p>
          <div class="btn-group mt-2 mb-3" role="group" aria-label="Basic example">
            <form method="post">
              <?php if ($is_following): ?>
                <button class="btn btn-sm btn-secondary rounded fw-bold opacity-50" type="submit" name="unfollow"><i class="bi bi-person-dash-fill"></i> <small>unfollow</small></button>
              <?php else: ?>
                <button class="btn btn-sm btn-secondary rounded fw-bold opacity-50" type="submit" name="follow"><i class="bi bi-person-fill-add"></i> <small>follow</small></button>
              <?php endif; ?>
            </form>
            <a class="btn btn-sm btn-secondary ms-1 rounded fw-bold opacity-50" href="follower.php?id=<?php echo $id; ?>"><?php echo $num_followers ?> <small>followers</small></a>
            <a class="btn btn-sm btn-secondary ms-1 rounded fw-bold opacity-50" href="following.php?id=<?php echo $id; ?>"><?php echo $num_following ?> <small>following</small></a>
          </div>
          <p class="text-center text-secondary fw-bold container-fluid" style="word-break: break-word; width: 97%;">
          <?php
            if (!empty($desc)) {
              $replacedDesc = preg_replace('/\b(https?:\/\/\S+)/i', '<a class="text-decoration-none" target="_blank" href="$1">$1</a>', $desc);
              echo $replacedDesc;
            }
          ?>
          </p>
          <ul class="nav justify-content-center pb-3 mb-3">
            <li class="nav-item fw-bold"><a href="<?php echo $twitter; ?>" class="nav-link px-2 text-secondary"><img class="img-sns" width="16" height="16" src="icon/twitter.svg"> Twitter</a></li>
            <li class="nav-item fw-bold"><a href="<?php echo $pixiv; ?>" class="nav-link px-2 text-secondary"><img class="img-sns" width="16" height="16" src="icon/pixiv.svg"> Pixiv</a></li>
            <li class="nav-item fw-bold"><a href="<?php echo $other; ?>" class="nav-link px-2 text-secondary"><img class="img-sns" width="16" height="16" src="icon/globe-asia-australia.svg"> Other</a></li>
          </ul>
          <div class="btn-group d-md-none d-lg-none" role="group" aria-label="Basic example">
            <a class="btn btn-sm btn-secondary disabled rounded opacity-50 fw-bold"><i class="bi bi-images"></i> <?php echo count($images); ?> <small>images</small></a>
            <a class="btn btn-sm btn-secondary ms-1 rounded fw-bold opacity-50" href="list_favorite.php?id=<?php echo $id; ?>"><i class="bi bi-heart"></i> <?php echo $fav_count;?> <small>favorites</small></a> 
            <button class="btn btn-sm btn-secondary ms-1 rounded fw-bold opacity-50" onclick="sharePage()"><i class="bi bi-share-fill"></i> <small>share</small></button>
          </div>
        </div>
      </div>
    </div>
    <h5 class="container-fluid fw-bold text-secondary"><i class="bi bi-images"></i> All <?php echo $artist; ?>'s Images</h5>
    <div class="images">
      <?php foreach ($images as $image): ?>
        <div class="image-container">
          <div class="position-relative">
            <a class="shadow" href="image.php?artworkid=<?php echo $image['id']; ?>">
              <img class="lazy-load" data-src="thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>">
            </a> 
            <div class="position-absolute top-0 start-0">
              <div class="dropdown">
                <button class="btn btn-sm btn-secondary ms-1 mt-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <?php
                    $is_favorited = $db->query("SELECT COUNT(*) FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = {$image['id']}")->fetchColumn();
                    if ($is_favorited) {
                  ?>
                    <form method="POST">
                      <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                      <li><button type="submit" class="dropdown-item fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i> <small>unfavorite</small></button></li>
                    </form>
                  <?php } else { ?>
                    <form method="POST">
                      <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                      <li><button type="submit" class="dropdown-item fw-bold" name="favorite"><i class="bi bi-heart"></i> <small>favorite</small></button></li>
                    </form>
                  <?php } ?>
                  <li><button class="dropdown-item fw-bold" onclick="shareImage(<?php echo $image['id']; ?>)"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
                  <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $image['id']; ?>"><i class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
                </ul>
                <!-- Modal -->
                <div class="modal fade" id="infoImage_<?php echo $image['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content rounded-3 shadow">
                      <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold"><?php echo $image['title']?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body p-4 text-center">
                        <img class="rounded object-fit-cover mb-3 shadow" src="thumbnails/<?php echo $image['filename']; ?>" style="width: 100%; height: 300px;">
                        <p class="text-start fw-semibold"><?php echo $image['imgdesc']?></p>
                        <div class="card container">
                          <p class="text-center fw-semibold mt-2">Image Information</p>
                          <p class="text-start fw-semibold">Image ID: "<?php echo $image['id']?>"</p>
                          <?php
                            // Get image size in megabytes
                            $image_size = round(filesize('images/' . $image['filename']) / (1024 * 1024), 2);

                            // Get image dimensions
                            list($width, $height) = getimagesize('images/' . $image['filename']);

                            // Display image information
                            echo "<p class='text-start fw-semibold'>Image data size: " . $image_size . " MB</p>";
                            echo "<p class='text-start fw-semibold'>Image dimensions: " . $width . "x" . $height . "</p>";
                            echo "<p class='text-start fw-semibold'><a class='text-decoration-none' href='images/" . $image['filename'] . "'>View original image</a></p>";
                          ?>
                        </div>
                        <div class="btn-group w-100 mt-2">
                          <a class="btn btn-primary fw-bold rounded-start-5" href="image.php?artworkid=<?php echo $image['id']; ?>"><i class="bi bi-eye-fill"></i> view</a>
                          <a class="btn btn-primary fw-bold" href="images/<?php echo $image['filename']; ?>" download><i class="bi bi-download"></i> download</a>
                          <button class="btn btn-primary fw-bold rounded-end-5" onclick="shareImage(<?php echo $image['id']; ?>)"><i class="bi bi-share-fill"></i> share</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
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
        .b-section {
          margin-top: 50px;
        }
      }
      
      @media (max-width: 767px) {
        .d-none-sm-b {
          display: none;
        }
      }
      
      .images {
        display: grid;
        grid-template-columns: repeat(2, 1fr); /* Two columns in mobile view */
        grid-gap: 3px;
        justify-content: center;
        margin-right: 3px;
        margin-left: 3px;
      }

      @media (min-width: 768px) {
        /* For desktop view, change the grid layout */
        .images {
          grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
      }

      .images a {
        display: block;
        border-radius: 4px;
        overflow: hidden;
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
      function shareImage(userId) {
        // Compose the share URL
        var shareUrl = 'image.php?artworkid=' + userId;

        // Check if the Share API is supported by the browser
        if (navigator.share) {
          navigator.share({
          url: shareUrl
        })
          .then(() => console.log('Shared successfully.'))
          .catch((error) => console.error('Error sharing:', error));
        } else {
          console.log('Share API is not supported in this browser.');
          // Provide an alternative action for browsers that do not support the Share API
          // For example, you can open a new window with the share URL
          window.open(shareUrl, '_blank');
        }
      }
    </script>
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
    <script>
      function sharePage() {
        if (navigator.share) {
          navigator.share({
            title: document.title,
            url: window.location.href
          }).then(() => {
            console.log('Page shared successfully.');
          }).catch((error) => {
            console.error('Error sharing page:', error);
          });
        } else {
          console.log('Web Share API not supported.');
        }
      }
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
