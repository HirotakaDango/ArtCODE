<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Get the artist name from the database
$email = $_SESSION['email'];
$stmt = $db->prepare("SELECT id, artist, pic, `desc`, bgpic, twitter, pixiv, other, region FROM users WHERE email = :email");
$stmt->bindValue(':email', $email);
$result = $stmt->execute();
$row = $result->fetchArray();
$user_id = $row['id'];
$artist = $row['artist'];
$pic = $row['pic'];
$desc = $row['desc'];
$bgpic = $row['bgpic'];
$twitter = $row['twitter'];
$pixiv = $row['pixiv'];
$other = $row['other'];
$region = $row['region'];

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

// Count the number of followers
$stmt = $db->prepare("SELECT COUNT(*) AS num_followers FROM following WHERE following_email = :email");
$stmt->bindValue(':email', $email);
$result = $stmt->execute();
$row = $result->fetchArray();
$num_followers = $row['num_followers'];

// Count the number of following
$stmt = $db->prepare("SELECT COUNT(*) AS num_following FROM following WHERE follower_email = :email");
$stmt->bindValue(':email', $email);
$result = $stmt->execute();
$row = $result->fetchArray();
$num_following = $row['num_following'];

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $existing_fav = $db->query("SELECT COUNT(*) FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = $image_id")->fetchArray()[0];

  if ($existing_fav == 0) {
    $db->exec("INSERT INTO favorites (email, image_id) VALUES ('{$_SESSION['email']}', $image_id)");
  }

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: profile.php");
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $db->exec("DELETE FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = $image_id");

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: profile.php");
  exit();
} 

// Get all of the images uploaded by the current user
$stmt = $db->prepare("SELECT * FROM images WHERE email = :email ORDER BY id DESC");
$stmt->bindValue(':email', $email);
$result = $stmt->execute();

// Count the number of images uploaded by the current user
$count = 0;
while ($image = $result->fetchArray()) {
  $count++;
}
  
$fav_result = $db->query("SELECT COUNT(*) FROM favorites WHERE email = '{$_SESSION['email']}'");
$fav_count = $fav_result->fetchArray()[0];
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $artist; ?></title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="roow mb-2">
      <div class="cool-6 text-center">
        <div class="caard art">
          <div class="col-md-5 order-md-1 mt-3 b-radius" style="background-image: url('<?php echo !empty($bgpic) ? $bgpic : "default_bg_thumbnail.jpg"; ?>'); background-size: cover; height: 200px; width: 100%;">
            <img class="img-thumbnail rounded-circle text-secondary" src="<?php echo !empty($pic) ? $pic : "icon/profile.svg"; ?>" alt="Profile Picture" style="object-fit: cover; width: 110px; height: 110px; border-radius: 4px; margin-left: -167px; margin-top: 45px;">
            <a class="btn-sm btn btn-secondary fw-bold float-start mt-2 ms-2 rounded-pill opacity-50" type="button" href="setting.php">change background <i class="bi bi-camera-fill"></i></a>
          </div>
          <div class="btn-group d-none-sm-b b-section" role="group" aria-label="Basic example">
            <a class="btn btn-sm btn-secondary disabled rounded opacity-50 fw-bold"><i class="bi bi-images"></i> <?php echo $count; ?> <small>images</small></a>
            <a class="btn btn-sm btn-secondary ms-1 rounded fw-bold opacity-50" href="list_favorite.php?id=<?php echo $user_id; ?>"><i class="bi bi-heart"></i> <?php echo $fav_count;?> <small>favorites</small></a> 
            <button class="btn btn-sm btn-secondary ms-1 rounded fw-bold opacity-50" onclick="shareArtist(<?php echo $user_id; ?>)"><i class="bi bi-share-fill"></i> <small>share</small></button>
          </div>
        </div>
      </div>
      <div class="cool-6">
        <div class="caard art text-center">
          <h3 class="text-secondary mt-2 fw-bold"><?php echo $artist; ?></h3>
          <p class="text-center text-muted fw-semibold"><small><i class="bi bi-globe-asia-australia"></i> <?php echo $region; ?></small></p>
          <div class="btn-group mt-2 mb-3" role="group" aria-label="Basic example">
            <a class="btn btn-sm btn-secondary rounded fw-bold opacity-50" href="follower.php?id=<?php echo $user_id; ?>"><?php echo $num_followers ?> <small>followers</small></a>
            <a class="btn btn-sm btn-secondary ms-1 rounded fw-bold opacity-50" href="following.php?id=<?php echo $user_id; ?>"><?php echo $num_following ?> <small>following</small></a>
            <a class="btn btn-sm btn-secondary ms-1 rounded fw-bold opacity-50" href="album.php"><i class="bi bi-images"></i> <small>my albums</small></a>
          </div>
          <p class="text-center text-secondary fw-bold container-fluid" style="word-break: break-word; width: 97%;">
            <?php
              $messageText = $desc;
              $messageTextWithoutTags = is_null($messageText) ? '' : strip_tags($messageText);
              $pattern = '/\bhttps?:\/\/\S+/i';

              $formattedText = preg_replace_callback($pattern, function ($matches) {
                $url = htmlspecialchars($matches[0]);
                return '<a href="' . $url . '">' . $url . '</a>';
              }, $messageTextWithoutTags);

              $formattedTextWithLineBreaks = nl2br($formattedText);
              echo $formattedTextWithLineBreaks;
            ?>
          </p>
          <ul class="nav justify-content-center pb-3 mb-3">
            <li class="nav-item fw-bold"><a href="<?php echo $twitter; ?>" class="nav-link px-2 text-secondary"><img class="img-sns" width="16" height="16" src="icon/twitter.svg"> Twitter</a></li>
            <li class="nav-item fw-bold"><a href="<?php echo $pixiv; ?>" class="nav-link px-2 text-secondary"><img class="img-sns" width="16" height="16" src="icon/pixiv.svg"> Pixiv</a></li>
            <li class="nav-item fw-bold"><a href="<?php echo $other; ?>" class="nav-link px-2 text-secondary"><img class="img-sns" width="16" height="16" src="icon/globe-asia-australia.svg"> Other</a></li>
          </ul>
          <div class="btn-group d-md-none d-lg-none" role="group" aria-label="Basic example">
            <a class="btn btn-sm btn-secondary disabled rounded opacity-50 fw-bold"><i class="bi bi-images"></i> <?php echo $count; ?> <small>images</small></a>
            <a class="btn btn-sm btn-secondary ms-1 rounded fw-bold opacity-50" href="list_favorite.php?id=<?php echo $user_id; ?>"><i class="bi bi-heart"></i> <?php echo $fav_count;?> <small>favorites</small></a> 
            <button class="btn btn-sm btn-secondary ms-1 rounded fw-bold opacity-50" onclick="shareArtist(<?php echo $user_id; ?>)"><i class="bi bi-share-fill"></i> <small>share</small></button>
          </div>
        </div>
      </div>
    </div>
    <h5 class="container-fluid fw-bold text-secondary"><i class="bi bi-images"></i> All <?php echo $artist; ?>'s Images</h5>
    <div class="images">
      <?php while ($image = $result->fetchArray()): ?>
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
                  <li><button class="dropdown-item fw-bold" onclick="location.href='edit_image.php?id=<?php echo $image['id']; ?>'" ><i class="bi bi-pencil-fill"></i> edit image</button></li>
                  <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#deleteImage_<?php echo $image['id']; ?>"><i class="bi bi-trash-fill"></i> delete</button></li>
                  <?php
                    $is_favorited = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = {$image['id']}");
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
                </ul>
              </div>
            </div>
          </div>
          <div>
            <form action="delete.php" method="post">
              <!-- Modal -->
              <div class="modal fade" id="deleteImage_<?php echo $image['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                  <div class="modal-content rounded-3 shadow">
                    <div class="modal-header border-0">
                      <h5 class="modal-title fw-bold">Delete Image ID: "<?php echo $image['id']?>"</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                      <h5 class="mb-0">Are you sure want to delete the selected image?</h5>
                      <p class="fw-semibold">"<?php echo $image['title']?>" will be deleted permanently!</p>
                      <img class="rounded object-fit-cover mb-3 shadow lazy-load" data-src="thumbnails/<?php echo $image['filename']; ?>" style="width: 100%; height: 100%;">
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
                      <p class="mb-3 mt-2 fw-semibold">This action can't be undone! Make sure you download the image before you delete it.</p>
                      <a class="btn btn-primary fw-bold rounded-4" href="images/<?php echo $image['filename']; ?>" download><i class="bi bi-download"></i> download image</a>
                    </div>
                    <div class="modal-footer flex-nowrap p-0">
                      <input type="hidden" name="id" value="<?php echo $image['id']; ?>">
                      <button class="btn btn-lg text-danger btn-link fs-6 text-decoration-none col-6 py-3 m-0 rounded-0 border-end" type="submit" value="Delete"><strong>Yes, delete the image!</strong></button>
                      <button type="button" class="btn btn-lg btn-link fs-6 text-decoration-none col-6 py-3 m-0 rounded-0" data-bs-dismiss="modal">Cancel, keep it!</button>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
    <div class="mb-4"></div>
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
      function shareArtist(userId) {
        // Compose the share URL
        var shareUrl = 'artist.php?id=' + userId;

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
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
