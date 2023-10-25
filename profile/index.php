<?php
require_once('../auth.php');

// Connect to SQLite database
$db = new PDO('sqlite:../database.sqlite');

// Get the artist information from the database
$email = $_SESSION['email'];
try {
  $stmt = $db->prepare("SELECT id, artist, pic, `desc`, bgpic, twitter, pixiv, other, region, joined, born FROM users WHERE email = :email");
  $stmt->bindValue(':email', $email);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  // Check if the user exists
  if (!$row) {
    die("User not found");
  }

  $user_id = $row['id'];
  $artist = $row['artist'];
  $pic = $row['pic'];
  $desc = $row['desc'];
  $bgpic = $row['bgpic'];
  $twitter = $row['twitter'];
  $pixiv = $row['pixiv'];
  $other = $row['other'];
  $region = $row['region'];
  $joined = $row['joined'];
  $born = $row['born'];
} catch (PDOException $e) {
  die("Error: " . $e->getMessage());
}

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
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$num_followers = $row['num_followers'];

// Count the number of following
$stmt = $db->prepare("SELECT COUNT(*) AS num_following FROM following WHERE follower_email = :email");
$stmt->bindValue(':email', $email);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$num_following = $row['num_following'];

// Format the numbers
$formatted_followers = formatNumber($num_followers);
$formatted_following = formatNumber($num_following);

// Process any favorite/unfavorite requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['favorite']) && isset($_POST['image_id'])) {
    $image_id = $_POST['image_id'];

    // Check if the image has already been favorited by the current user
    $stmt = $db->prepare("SELECT COUNT(*) AS num_favorites FROM favorites WHERE email = :email AND image_id = :image_id");
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':image_id', $image_id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $num_favorites = $row['num_favorites'];

    if ($num_favorites == 0) {
      $stmt = $db->prepare("INSERT INTO favorites (email, image_id) VALUES (:email, :image_id)");
      $stmt->bindValue(':email', $email);
      $stmt->bindValue(':image_id', $image_id);
      $stmt->execute();
    }

    // Redirect to the current page to prevent duplicate form submissions
    header("Location: {$_SERVER['REQUEST_URI']}");
    exit();
  } elseif (isset($_POST['unfavorite']) && isset($_POST['image_id'])) {
    $image_id = $_POST['image_id'];
    $stmt = $db->prepare("DELETE FROM favorites WHERE email = :email AND image_id = :image_id");
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':image_id', $image_id);
    $stmt->execute();

    // Redirect to the current page to prevent duplicate form submissions
    header("Location: {$_SERVER['REQUEST_URI']}");
    exit();
  }
}

// Get all of the images uploaded by the current user
$stmtI = $db->prepare("SELECT * FROM images WHERE email = :email ORDER BY id DESC");
$stmtI->bindValue(':email', $email);
$stmtI->execute();
$images = $stmtI->fetchAll(PDO::FETCH_ASSOC);

// Count the number of images uploaded by the current user
$count = count($images);

// Count the number of favorites
$stmtFav = $db->prepare("SELECT COUNT(*) AS num_favorites FROM favorites WHERE email = :email");
$stmtFav->bindValue(':email', $email);
$stmtFav->execute();
$fav_row = $stmtFav->fetch(PDO::FETCH_ASSOC);
$fav_count = $fav_row['num_favorites'];
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $artist; ?></title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="../image/png" href="../icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="../style.css">
  </head>
  <body>
    <?php include('../contents/header.php'); ?>
    
    <!-- Porfile Header -->
    <div class="mt-2 vh-100 d-none d-md-block d-lg-block position-relative" style="background-image: url('<?php echo !empty($bgpic) ? $bgpic : "../icon/bg.png"; ?>'); background-size: cover; height: 100%; width: 100%;">
      <a class="btn-sm btn btn-dark fw-bold rounded-pill opacity-75 position-absolute bottom-0 start-0 m-2" type="button" href="setting.php">change background <i class="bi bi-camera-fill"></i></a>
      <button class="btn btn-sm btn-dark opacity-75 rounded-3 position-absolute bottom-0 end-0 m-1" data-bs-toggle="modal" data-bs-target="#modalUserInfo"><i class="bi bi-info-circle-fill"></i></button>
    </div>
    <div class="container-fluid d-none d-md-block d-lg-block">
      <div class="row">
        <div class="col-md-2 d-flex align-item-center">
          <img class="img-thumbnail border-0 shadow text-center rounded-circle m-3" src="<?php echo !empty($pic) ? $pic : "../icon/profile.svg"; ?>" alt="Profile Picture" style="width: 150px; height: 150px;">
        </div>
        <div class="col-md-10 d-flex align-items-center">
          <div>
            <h1 class="fw-bold d-none d-md-block d-lg-block"><?php echo $artist; ?></h1>
            <div>
              <span class="me-4"><a class="btn border-0 fw-medium" href="<?php echo $_SERVER['REQUEST_URI']; ?>"> <?php echo $count; ?> <small> Images</small></a></span>
              <span class="me-4"><a class="btn border-0 fw-medium" href="../list_favorite.php?id=<?php echo $user_id; ?>"> <?php echo $fav_count;?> <small> Favorites</small></a></span>
            </div>
            <div>
              <span class="me-4"><a class="btn border-0 fw-medium" href="../follower.php?id=<?php echo $user_id; ?>"> <?php echo $num_followers ?> <small>Followers</small></a></span>
              <span class="me-4"><a class="btn border-0 fw-medium" href="../following.php?id=<?php echo $user_id; ?>"> <?php echo $num_following ?> <small>Following</small></a></span>
              <span class="me-4"><a class="btn border-0 fw-medium" href="../album.php"><small>My Album</small></a></span>
              <span class="me-4"><button class="btn border-0 fw-medium" onclick="shareArtist(<?php echo $user_id; ?>)"><small>Shares</small></button></span>
            </div>
            <p class="mt-4 ms-3 fw-medium">
              <small>
                <?php
                  if (!empty($desc)) {
                    $messageText = $desc;
                    $messageTextWithoutTags = strip_tags($messageText);
                    $pattern = '/\bhttps?:\/\/\S+/i';

                    $formattedText = preg_replace_callback($pattern, function ($matches) {
                      $url = htmlspecialchars($matches[0]);
                      return '<a href="' . $url . '">' . $url . '</a>';
                    }, $messageTextWithoutTags);

                    $formattedTextWithLineBreaks = nl2br($formattedText);
                    echo $formattedTextWithLineBreaks;
                  } else {
                    echo "User description is empty.";
                  }
                ?>
              </small>
            </p>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid d-md-none d-lg-none">
      <div class="row">
        <div class="col-md-5 order-md-1 mt-2 b-radius position-relative" style="background-image: url('<?php echo !empty($bgpic) ? $bgpic : "../icon/bg.png"; ?>'); background-size: cover; height: 250px; width: 100%;">
          <img class="img-thumbnail border-0 shadow position-absolute top-50 start-50 translate-middle rounded-circle" src="<?php echo !empty($pic) ? $pic : "../icon/profile.svg"; ?>" alt="Profile Picture" style="width: 150px; height: 150px;">
          <a class="btn-sm btn btn-dark fw-bold rounded-pill opacity-75 position-absolute top-0 start-0 m-2" type="button" href="setting.php">change background <i class="bi bi-camera-fill"></i></a>
          <button class="btn btn-sm btn-dark opacity-75 rounded-3 position-absolute top-0 end-0 m-2" data-bs-toggle="modal" data-bs-target="#modalUserInfo"><i class="bi bi-info-circle-fill"></i></button>
        </div>
        <div class="d-flex align-items-center">
          <div>
            <h1 class="fw-bold text-center d-md-none d-lg-none"><?php echo $artist; ?></h1>
            <div class="text-center">
              <span class=""><a class="btn border-0 fw-medium" href="<?php echo $_SERVER['REQUEST_URI']; ?>"> <?php echo $count; ?> <small> Images</small></a></span>
              <span class=""><a class="btn border-0 fw-medium" href="../list_favorite.php?id=<?php echo $user_id; ?>"> <?php echo $fav_count;?> <small> Favorites</small></a></span>
              <span class=""><a class="btn border-0 fw-medium" href="../album.php"><small>My Album</small></a></span>
              <span class=""><a class="btn border-0 fw-medium" href="../follower.php?id=<?php echo $user_id; ?>"> <?php echo $num_followers ?> <small>Followers</small></a></span>
              <span class=""><a class="btn border-0 fw-medium" href="../following.php?id=<?php echo $user_id; ?>"> <?php echo $num_following ?> <small>Following</small></a></span>
              <span class=""><button class="btn border-0 fw-medium" onclick="shareArtist(<?php echo $user_id; ?>)"><small>Shares</small></button></span>
            </div>
            <p class="mt-4 fw-medium">
              <small>
                <?php
                  if (!empty($desc)) {
                    $messageText = $desc;
                    $messageTextWithoutTags = strip_tags($messageText);
                    $pattern = '/\bhttps?:\/\/\S+/i';

                    $formattedText = preg_replace_callback($pattern, function ($matches) {
                      $url = htmlspecialchars($matches[0]);
                      return '<a href="' . $url . '">' . $url . '</a>';
                    }, $messageTextWithoutTags);

                    $formattedTextWithLineBreaks = nl2br($formattedText);
                    echo $formattedTextWithLineBreaks;
                  } else {
                    echo "User description is empty.";
                  }
                ?>
              </small>
            </p>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="modalUserInfo" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h1 class="modal-title fw-bold fs-5" id="exampleModalLabel">Information</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="input-group mb-2">
              <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
              <input type="text" class="form-control fw-bold text-end" value="<?php echo !empty($user_id) ? $user_id : ''; ?>" readonly>
            </div>
            <div class="input-group mb-2">
              <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
              <input type="text" class="form-control fw-bold text-end" value="<?php echo !empty($artist) ? $artist : ''; ?>" readonly>
            </div>
            <div class="input-group mb-2">
              <span class="input-group-text"><i class="bi bi-globe-asia-australia"></i></span>
              <input type="text" class="form-control fw-bold text-end" value="<?php echo !empty($region) ? $region : ''; ?>" readonly>
            </div>
            <div class="input-group mb-2">
              <span class="input-group-text"><i class="bi bi-person-fill-check"></i></span>
              <input type="text" class="form-control fw-bold text-end" value="<?php echo !empty($joined) ? date('Y/m/d', strtotime($joined)) : ''; ?>" readonly>
            </div>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-calendar-fill"></i></span>
              <input type="text" class="form-control fw-bold text-end" value="<?php echo !empty($born) ? date('Y/m/d', strtotime($born)) : ''; ?>" readonly>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- End of Porfile Header -->

    <h5 class="container-fluid fw-bold text-secondary"><i class="bi bi-images"></i> All <?php echo $artist; ?>'s Images</h5>
    <div class="dropdown">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-3 btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=newest" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?by=oldest" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?by=popular" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">popular</a></li>
      </ul> 
    </div> 
        <?php 
        if(isset($_GET['by'])){
          $sort = $_GET['by'];
 
          switch ($sort) {
            case 'newest':
            include "profile_desc.php";
            break;
            case 'oldest':
            include "profile_asc.php";
            break;
            case 'popular':
            include "profile_pop.php";
            break;
          }
        }
        else {
          include "profile_desc.php";
        }
        
        ?>
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
      
      .roow {
        display: flex;
        flex-wrap: wrap;
        border-radius: 5px;
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
        var shareUrl = '../image.php?artworkid=' + userId;

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
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      let imageContainer = document.getElementById("image-container");

      // Set the default placeholder image
      const defaultPlaceholder = "../icon/bg.png";

      if ("IntersectionObserver" in window) {
        let imageObserver = new IntersectionObserver(function(entries, observer) {
          entries.forEach(function(entry) {
            if (entry.isIntersecting) {
              let image = entry.target;
              image.src = image.dataset.src;
              imageObserver.unobserve(image);
            }
          });
        });

        lazyloadImages.forEach(function(image) {
          image.src = defaultPlaceholder; // Apply default placeholder
          imageObserver.observe(image);
          image.style.filter = "blur(5px)"; // Apply initial blur to all images

          // Remove blur and apply custom blur to NSFW images after they load
          image.addEventListener("load", function() {
            image.style.filter = ""; // Remove initial blur
            if (image.classList.contains("nsfw")) {
              image.style.filter = "blur(4px)"; // Apply blur to NSFW images
          
              // Add overlay with icon and text
              let overlay = document.createElement("div");
              overlay.classList.add("overlay", "rounded");
              let icon = document.createElement("i");
              icon.classList.add("bi", "bi-eye-slash-fill", "text-white");
              overlay.appendChild(icon);
              let text = document.createElement("span");
              text.textContent = "R-18";
              text.classList.add("shadowed-text", "fw-bold", "text-white");
              overlay.appendChild(text);
              image.parentNode.appendChild(overlay);
            }
          });
        });
      } else {
        let lazyloadThrottleTimeout;

        function lazyload() {
          if (lazyloadThrottleTimeout) {
            clearTimeout(lazyloadThrottleTimeout);
          }
          lazyloadThrottleTimeout = setTimeout(function() {
            let scrollTop = window.pageYOffset;
            lazyloadImages.forEach(function(img) {
              if (img.offsetTop < window.innerHeight + scrollTop) {
                img.src = img.dataset.src;
                img.classList.remove("lazy-load");
              }
            });
            lazyloadImages = Array.from(lazyloadImages).filter(function(image) {
              return image.classList.contains("lazy-load");
            });
            if (lazyloadImages.length === 0) {
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

      // Infinite scrolling
      let loading = false;

      function loadMoreImages() {
        if (loading) return;
        loading = true;

        // Simulate loading delay for demo purposes
        setTimeout(function() {
          for (let i = 0; i < 10; i++) {
            if (lazyloadImages.length === 0) {
              break;
            }
            let image = lazyloadImages[0];
            imageContainer.appendChild(image);
            lazyloadImages = Array.from(lazyloadImages).slice(1);
          }
          loading = false;
        }, 1000);
      }

      window.addEventListener("scroll", function() {
        if (window.innerHeight + window.scrollY >= imageContainer.clientHeight) {
          loadMoreImages();
        }
      });

      // Initial loading
      loadMoreImages();
    </script>
    <script>
      function shareArtist(userId) {
        // Compose the share URL
        var shareUrl = '../artist.php?id=' + userId;

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
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>