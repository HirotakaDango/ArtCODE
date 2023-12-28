<?php
require_once('../auth.php');

$email = $_SESSION['email'];

// Connect to SQLite database
$db = new PDO('sqlite:../database.sqlite');

// Get the ID of the selected user from the URL
$id = $_GET['id'];
$query = $db->prepare('SELECT artist, `desc`, `bgpic`, pic, twitter, pixiv, other, region, joined, born, email, message_1, message_2, message_3, message_4 FROM users WHERE id = :id');
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
$region = $user['region'];
$joined = $user['joined'];
$born = $user['born'];
$message_1 = $user['message_1'];
$message_2 = $user['message_2'];
$message_3 = $user['message_3'];
$message_4 = $user['message_4'];

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

  // Get the current page URL
  $currentUrl = $_SERVER['REQUEST_URI'];

  // Redirect to the current page to prevent duplicate form submissions
  header("Location: $currentUrl");
  exit();
} elseif (isset($_POST['unfollow'])) {
  // Remove the following relationship between the logged-in user and the selected user
  $query = $db->prepare('DELETE FROM following WHERE follower_email = :follower_email AND following_email = :following_email');
  $query->bindParam(':follower_email', $email);
  $query->bindParam(':following_email', $user['email']);
  $query->execute();
  $is_following = false;

  // Get the current page URL
  $currentUrl = $_SERVER['REQUEST_URI'];

  // Redirect to the current page to prevent duplicate form submissions
  header("Location: $currentUrl");
  exit();
} 

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $existing_fav = $db->query("SELECT COUNT(*) FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = $image_id")->fetchColumn();

  if ($existing_fav == 0) {
    $db->exec("INSERT INTO favorites (email, image_id) VALUES ('{$_SESSION['email']}', $image_id)");
  }

  // Get the current page URL
  $currentUrl = $_SERVER['REQUEST_URI'];

  // Redirect to the current page to prevent duplicate form submissions
  header("Location: $currentUrl");
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $db->exec("DELETE FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = $image_id");

  // Get the current page URL
  $currentUrl = $_SERVER['REQUEST_URI'];

  // Redirect to the current page to prevent duplicate form submissions
  header("Location: $currentUrl");
  exit();
}

$fav_count = $db->prepare("SELECT COUNT(*) FROM favorites WHERE email = :email");
$fav_count->bindParam(':email', $user['email']);
$fav_count->execute();
$fav_count = $fav_count->fetchColumn();
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $artist; ?></title>
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="../style.css">
  </head>
  <body>
    <?php include('../contents/header.php'); ?> 
    
    <!-- Porfile Header -->
    <div class="mt-2 vh-100 d-none d-md-block d-lg-block position-relative" style="background-image: url('<?php echo !empty($bgpic) ? $bgpic : "../icon/bg.png"; ?>'); background-size: cover; height: 100%; width: 100%;">
      <button class="btn btn-sm btn-dark opacity-75 rounded-3 position-absolute bottom-0 end-0 m-1" data-bs-toggle="modal" data-bs-target="#modalUserInfo"><i class="bi bi-info-circle-fill"></i></button>
    </div>
    <div class="container-fluid d-none d-md-block d-lg-block mt-3">
      <div class="row">
        <div class="col-md-2 d-flex align-item-center">
          <div class="card border-0">
            <img class="img-thumbnail border-0 shadow text-center rounded-circle mt-3 mx-3" src="<?php echo !empty($pic) ? $pic : "../icon/profile.svg"; ?>" alt="Profile Picture" style="width: 150px; height: 150px;">
            <div class="card-body">
              <h6 class="text-center"><span class="badge bg-secondary fw-medium rounded-pill"><?php echo $region; ?></span></h6>
            </div>
          </div>
        </div>
        <div class="col-md-7 d-flex align-items-center">
          <div>
            <h1 class="fw-bold d-none d-md-block d-lg-block mt-2"><?php echo $artist; ?></h1>
            <div>
              <span class="me-4"><a class="btn border-0 fw-medium" href="<?php echo $_SERVER['REQUEST_URI']; ?>"> <?php echo count($images); ?> <small> Images</small></a></span>
              <span class="me-4"><a class="btn border-0 fw-medium" href="../list_favorite.php?id=<?php echo $id; ?>"> <?php echo $fav_count; ?> <small> Favorites</small></a></span>
            </div>
            <div class="button-group">
              <form method="post">
                <?php if ($is_following): ?>
                  <span class="me-3"><button class="btn btn-outline-dark rounded-pill fw-medium" type="submit" name="unfollow"><i class="bi bi-person-dash-fill"></i> <small>unfollow</small></button></span>
                <?php else: ?>
                  <span class="me-3"><button class="btn btn-outline-dark rounded-pill fw-medium" type="submit" name="follow"><i class="bi bi-person-fill-add"></i> <small>follow</small></button></span>
                <?php endif; ?>
              </form>
              <span class="me-4"><a class="btn border-0 fw-medium" href="../follower.php?id=<?php echo $id; ?>"> <?php echo $num_followers ?> <small>Followers</small></a></span>
              <span class="me-4"><a class="btn border-0 fw-medium" href="../following.php?id=<?php echo $id; ?>"> <?php echo $num_following ?> <small>Following</small></a></span>
              <span class="me-4"><button class="btn border-0 fw-medium" onclick="sharePage()"><small>Shares</small></button></span>
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

                    $charLimit = 100; // Set your character limit

                    if (strlen($formattedText) > $charLimit) {
                      $limitedText = substr($formattedText, 0, $charLimit);
                      echo '<span id="limitedText">' . nl2br($limitedText) . '...</span>'; // Display the capped text with line breaks and "..."
                      echo '<span id="more" style="display: none;">' . nl2br($formattedText) . '</span>'; // Display the full text initially hidden with line breaks
                      echo '</br><button class="btn btn-sm mt-2 fw-medium p-0 border-0" onclick="myFunction()" id="myBtn">read more</button>';
                    } else {
                      // If the text is within the character limit, just display it with line breaks.
                      echo nl2br($formattedText);
                    }
                  } else {
                    echo "User description is empty.";
                  }
                ?>
              </small>
            </p>

            <script>
              function myFunction() {
                var dots = document.getElementById("limitedText");
                var moreText = document.getElementById("more");
                var btnText = document.getElementById("myBtn");

                if (moreText.style.display === "none") {
                  dots.style.display = "none";
                  moreText.style.display = "inline";
                  btnText.innerHTML = "read less";
                } else {
                  dots.style.display = "inline";
                  moreText.style.display = "none";
                  btnText.innerHTML = "read more";
                }
              }
            </script>

          </div>
        </div>
        <div class="col-md-2 d-flex align-item-center">
          <div class="btn-group gap-2 mt-2" role="group" aria-label="Social Media Links">
            <span>
              <?php if (!empty($twitter)): ?>
                <?php $twitterUrl = (strpos($twitter, 'https') !== false) ? $twitter : 'https://' . $twitter; ?>
                <a href="<?php echo $twitterUrl; ?>" class="btn fw-medium" role="button">
                  <img class="" width="16" height="16" src="../icon/twitter.svg"> <small>Twitter</small>
                </a>
              <?php endif; ?>
            </span>
            <span>
              <?php if (!empty($pixiv)): ?>
                <?php $pixivUrl = (strpos($pixiv, 'https') !== false) ? $pixiv : 'https://' . $pixiv; ?>
                <a href="<?php echo $pixivUrl; ?>" class="btn fw-medium" role="button">
                  <img class="" width="16" height="16" src="../icon/pixiv.svg"> <small>Pixiv</small>
                </a>
              <?php endif; ?>
            </span>
            <span>
              <?php if (!empty($other)): ?>
                <?php $otherUrl = (strpos($other, 'https') !== false) ? $other : 'https://' . $other; ?>
                <a href="<?php echo $otherUrl; ?>" class="btn fw-medium" role="button">
                  <img class="" width="16" height="16" src="../icon/globe-asia-australia.svg"> <small>Other</small>
                </a>
              <?php endif; ?>
            </span>
            <span>
              <button class="btn fw-medium" data-bs-toggle="modal" data-bs-target="#contactModal"><i class="bi bi-chat-fill"></i> <small>Message</small></button>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid d-md-none d-lg-none">
      <div class="row">
        <div class="b-radius position-relative" style="background-image: url('<?php echo !empty($bgpic) ? $bgpic : "../icon/bg.png"; ?>'); background-size: cover; height: 250px; width: 100%;">
          <img class="img-thumbnail border-0 shadow position-absolute top-50 start-50 translate-middle rounded-circle" src="<?php echo !empty($pic) ? $pic : "../icon/profile.svg"; ?>" alt="Profile Picture" style="width: 150px; height: 150px;">
          <button class="btn btn-sm btn-dark opacity-75 rounded-3 position-absolute top-0 end-0 m-2" data-bs-toggle="modal" data-bs-target="#modalUserInfo"><i class="bi bi-info-circle-fill"></i></button>
        </div>
        <div class="d-flex align-items-center justify-content-center">
          <div>
            <h1 class="fw-bold text-center d-md-none d-lg-none mt-2"><?php echo $artist; ?></h1>
            <h6 class="text-center"><span class="badge bg-secondary fw-medium rounded-pill"><?php echo $region; ?></span></h6>
            <div class="d-flex my-2 justify-content-center align-item-center">
              <div class="btn-group gap-2 my-2" role="group" aria-label="Social Media Links">
                <span>
                  <?php if (!empty($twitter)): ?>
                    <?php $twitterUrl = (strpos($twitter, 'https') !== false) ? $twitter : 'https://' . $twitter; ?>
                    <a href="<?php echo $twitterUrl; ?>" class="btn fw-medium" role="button">
                      <img class="" width="16" height="16" src="../icon/twitter.svg"> <small>Twitter</small>
                    </a>
                  <?php endif; ?>
                </span>
                <span>
                  <?php if (!empty($pixiv)): ?>
                    <?php $pixivUrl = (strpos($pixiv, 'https') !== false) ? $pixiv : 'https://' . $pixiv; ?>
                    <a href="<?php echo $pixivUrl; ?>" class="btn fw-medium" role="button">
                      <img class="" width="16" height="16" src="../icon/pixiv.svg"> <small>Pixiv</small>
                    </a>
                  <?php endif; ?>
                </span>
                <span>
                  <?php if (!empty($other)): ?>
                    <?php $otherUrl = (strpos($other, 'https') !== false) ? $other : 'https://' . $other; ?>
                    <a href="<?php echo $otherUrl; ?>" class="btn fw-medium" role="button">
                      <img class="" width="16" height="16" src="../icon/globe-asia-australia.svg"> <small>Other</small>
                    </a>
                  <?php endif; ?>
                </span>
                <span>
                  <button class="btn fw-medium" data-bs-toggle="modal" data-bs-target="#contactModal"><i class="bi bi-chat-fill"></i> <small>Message</small></button>
                </span>
              </div>
            </div>
            <div class="d-flex align-items-center justify-content-center mb-2">
              <form class="w-100" method="post">
                <?php if ($is_following): ?>
                  <button class="btn btn-outline-dark rounded-pill w-100 fw-medium" type="submit" name="unfollow"><i class="bi bi-person-dash-fill"></i> <small>unfollow</small></button>
                <?php else: ?>
                  <button class="btn btn-outline-dark rounded-pill w-100 fw-medium" type="submit" name="follow"><i class="bi bi-person-fill-add"></i> <small>follow</small></button>
                <?php endif; ?>
              </form>
            </div>
            <div class="btn-group w-100 mt-2">
              <a class="btn border-0 fw-medium text-center w-50" href="../../follower.php?id=<?php echo $id; ?>"> <?php echo $num_followers ?> <small>Followers</small></a>
              <a class="btn border-0 fw-medium text-center w-50" href="../../following.php?id=<?php echo $id; ?>"> <?php echo $num_following ?> <small>Following</small></a>
              <a class="btn border-0 fw-medium text-center w-50" href="<?php echo $_SERVER['REQUEST_URI']; ?>"> <?php echo count($images); ?> <small> Images</small></a>
            </div>
            <div class="btn-group w-100 mt-2">
              <a class="btn border-0 fw-medium text-center w-50" href="../list_favorite.php?id=<?php echo $id; ?>"> <?php echo $fav_count;?> <small> Favorites</small></a>
              <button class="btn border-0 fw-medium text-center w-50" onclick="sharePage()"><small>Shares</small></button>
            </div>
            <p class="mt-4 fw-medium text-break">
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

                    $charLimit = 100; // Set your character limit

                    if (strlen($formattedText) > $charLimit) {
                      $limitedText = substr($formattedText, 0, $charLimit);
                      echo '<span id="limitedText1">' . nl2br($limitedText) . '...</span>'; // Display the capped text with line breaks and "..."
                      echo '<span id="more1" style="display: none;">' . nl2br($formattedText) . '</span>'; // Display the full text initially hidden with line breaks
                      echo '</br><button class="btn btn-sm mt-2 fw-medium p-0 border-0" onclick="myFunction1()" id="myBtn1"><small>read more</small></button>';
                    } else {
                      // If the text is within the character limit, just display it with line breaks.
                      echo nl2br($formattedText);
                    }
                  } else {
                    echo "User description is empty.";
                  }
                ?>
              </small>
            </p>
            
            <script>
              function myFunction1() {
                var dots1 = document.getElementById("limitedText1");
                var moreText1 = document.getElementById("more1");
                var btnText1 = document.getElementById("myBtn1");

                if (moreText1.style.display === "none") {
                  dots1.style.display = "none";
                  moreText1.style.display = "inline";
                  btnText1.innerHTML = "read less";
                } else {
                  dots1.style.display = "inline";
                  moreText1.style.display = "none";
                  btnText1.innerHTML = "read more";
                }
              }
            </script>
            
          </div>
        </div>
      </div>
    </div>
    <!-- End of Profile Header -->

    <?php include('profile_header.php'); ?>
    <?php include('contact_header.php'); ?>
    <?php include('most_popular_artist.php'); ?>
    
    <h6 class="container-fluid fw-bold"><i class="bi bi-images"></i> All <?php echo $artist; ?>'s Images</h6>
    <div class="dropdown <?php echo ((isset($_GET['by']) && ($_GET['by'] === 'tagged_oldest' || $_GET['by'] === 'tagged_newest' || $_GET['by'] === 'tagged_popular')) || (strpos($_SERVER['REQUEST_URI'], 'header_artist_asc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'header_artist_desc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'header_artist_pop.php') !== false)) ? 'd-none' : ''; ?>">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-3 btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?id=<?php echo $id; ?>&by=newest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?id=<?php echo $id; ?>&by=oldest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?id=<?php echo $id; ?>&by=popular&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">popular</a></li>
      </ul>
    </div> 
    <div class="dropdown <?php echo ((isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest' || $_GET['by'] === 'popular')) || (strpos($_SERVER['REQUEST_URI'], 'artist_asc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'artist_desc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'artist_pop.php') !== false)) ? 'd-none' : ''; ?>">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-3 btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?id=<?php echo $id; ?>&by=tagged_newest&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'tagged_newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?id=<?php echo $id; ?>&by=tagged_oldest&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'tagged_oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?id=<?php echo $id; ?>&by=tagged_popular&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'tagged_popular') echo 'active'; ?>">popular</a></li>
      </ul>
    </div> 
        <?php 
        if(isset($_GET['by'])){
          $sort = $_GET['by'];
 
          switch ($sort) {
            case 'newest':
            include "artist_desc.php";
            break;
            case 'oldest':
            include "artist_asc.php";
            break;
            case 'popular':
            include "artist_pop.php";
            break;
            case 'tagged_newest':
            include "artist_tagged_desc.php";
            break;
            case 'tagged_oldest':
            include "artist_tagged_asc.php";
            break;
            case 'tagged_popular':
            include "artist_tagged_pop.php";
            break;
          }
        }
        else {
          include "artist_desc.php";
        }
        
        ?>
    <div class="mt-5"></div>
    <style>
      .button-group {
        display: flex;
        flex-wrap: wrap;
      }

      @media only screen and (min-width: 767px) {
        .rounded-min-5 {
          border-radius: 1.6rem;
        }
      }
        
      .button-group button {
        white-space: nowrap; /* Prevent wrapping of button text */
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
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>
