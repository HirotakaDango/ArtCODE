<?php
require_once('../../auth.php');

// Connect to the database using PDO
$db = new PDO('sqlite:../../database.sqlite');

// Get the filename from the query string
$filename = $_GET['artworkid'];

// Get the current image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id = :filename ");
$stmt->bindParam(':filename', $filename);
$stmt->execute();
$image = $stmt->fetch();

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

// Increment the view count for the image
$stmt = $db->prepare("UPDATE images SET view_count = view_count + 1 WHERE id = :filename");
$stmt->bindParam(':filename', $filename);
$stmt->execute();

// Get the updated image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id = :filename");
$stmt->bindParam(':filename', $filename);
$stmt->execute();
$image = $stmt->fetch();

// Retrieve the updated view count from the image information
$viewCount = $image['view_count'];

// Get all child images associated with the current image from the "image_child" table
$stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :image_id");
$stmt->bindParam(':image_id', $image_id);
$stmt->execute();
$child_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?> 

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $image['title']; ?></title>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <div id="content">
      <?php if (empty($image['filename'])) : ?>
        <div class="position-absolute top-50 start-50 translate-middle text-nowrap">
          <h1 class="fw-bold">Image not found</h1>
          <div class="d-flex justify-content-center">
            <a class="btn btn-primary fw-bold" href="/">back to home</a>
          </div>
        </div>
      <?php else : ?>
        <div class="position-relative d-flex align-items-center justify-content-center vh-100">
          <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-container">
              <div class="carousel-inner">
                <div class="carousel-item active">
                  <?php
                    $imagePath = "../../images/" . $image['filename'];
                    list($width, $height) = getimagesize($imagePath);
                    $fileSize = filesize($imagePath);
                    $fileDate = date("Y-m-d", filemtime($imagePath));
                  ?>
                  <img src="<?php echo $imagePath; ?>" class="d-block w-100" alt="<?php echo $image['title']; ?>">
                  <div class="image-info">
                    <div class="navbar fixed-bottom w-100" style="background: linear-gradient(to top, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0));">
                      <div class="z-3">
                        <div class="m-2 fw-bold">
                          <p class="text-shadow">Filename: <?php echo $image['filename']; ?></p>
                          <p class="text-shadow">Size: <?php echo round($fileSize / 1024, 2); ?> KB</p>
                          <p class="text-shadow">Date: <?php echo $fileDate; ?></p>
                          <p class="text-shadow">Resolution: <?php echo $width; ?>x<?php echo $height; ?></p>
                          <a class="text-decoration-none" href="../../images/<?php echo $image['filename']; ?>" download>
                            Download
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <?php foreach ($child_images as $child_image) : ?>
                  <?php if (!empty($child_image['filename'])) : ?>
                    <div class="carousel-item">
                      <?php
                        $childImagePath = "../../images/" . $child_image['filename'];
                        list($childWidth, $childHeight) = getimagesize($childImagePath);
                        $childFileSize = filesize($childImagePath);
                        $childFileDate = date("Y-m-d", filemtime($childImagePath));
                      ?>
                      <img src="<?php echo $childImagePath; ?>" class="d-block w-100" alt="<?php echo $child_image['title']; ?>">
                      <div class="image-info">
                        <div class="navbar fixed-bottom w-100" style="background-color: rgba(0, 0, 0, 0.5);">
                          <div class="z-3">
                            <div class="m-2 fw-bold">
                              <p class="text-shadow">Filename: <?php echo $child_image['filename']; ?></p>
                              <p class="text-shadow">Size: <?php echo round($childFileSize / 1024, 2); ?> KB</p>
                              <p class="text-shadow">Date: <?php echo $childFileDate; ?></p>
                              <p class="text-shadow">Resolution: <?php echo $childWidth; ?>x<?php echo $childHeight; ?></p>
                              <a class="text-decoration-none" href="../../images/<?php echo $child_image['filename']; ?>" download>
                                Download
                              </a>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <a class="carousel-control-prev position-fixed" href="#carouselExample" role="button" data-bs-slide="prev">
            <i class="bi bi-chevron-left display-5 text-white" style="-webkit-text-stroke: 4px;"></i>
          </a>
          <a class="carousel-control-next position-fixed" href="#carouselExample" role="button" data-bs-slide="next">
            <i class="bi bi-chevron-right display-5 text-white" style="-webkit-text-stroke: 4px;"></i>
          </a>
        </div>
        <?php
          // Function to calculate the size of an image in MB
          function getImageSizeInMB($filename) {
            return round(filesize('../../images/' . $filename) / (1024 * 1024), 2);
          }

          // Get the total size of images from 'images' table
          $stmt = $db->prepare("SELECT * FROM images WHERE id = :filename");
          $stmt->bindParam(':filename', $filename);
          $stmt->execute();
          $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

          // Get the total size of images from 'image_child' table
          $stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :filename");
          $stmt->bindParam(':filename', $filename);
          $stmt->execute();
          $image_childs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
          $images_total_size = 0;
          foreach ($images as $image) {
            $images_total_size += getImageSizeInMB($image['filename']);
          }

          $image_child_total_size = 0;
          foreach ($image_childs as $image_child) {
            $image_child_total_size += getImageSizeInMB($image_child['filename']);
          }
      
          $total_size = $images_total_size + $image_child_total_size;
        ?>
        <div id="scrollButton">
          <div class="fixed-top" >
            <div class="p-2 container-fluid mb-2 d-flex" style="background: linear-gradient(to bottom, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0));">
              <?php
                $stmt = $db->prepare("SELECT u.id, u.email, u.password, u.artist, u.pic, u.desc, u.bgpic, i.id AS image_id, i.filename, i.tags FROM users u INNER JOIN images i ON u.id = i.id WHERE u.id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
              ?>
              <div class="d-flex d-md-none d-lg-none">
                <a class="text-decoration-none text-light fw-bold rounded-pill" href="../../artist.php?id=<?php echo $user['id']; ?>" data-bs-toggle="modal" data-bs-target="#userModal">
                  <?php if (!empty($user['pic'])): ?>
                    <img class="object-fit-cover border border-1 rounded-circle" src="../../<?php echo $user['pic']; ?>" style="width: 32px; height: 32px;">
                  <?php else: ?>
                    <img class="object-fit-cover border border-1 rounded-circle" src="../../icon/profile.svg" style="width: 32px; height: 32px;">
                  <?php endif; ?>
                  <?php echo (mb_strlen($user['artist']) > 10) ? mb_substr($user['artist'], 0, 10) . '...' : $user['artist']; ?> <small class="badge rounded-pill bg-primary"><i class="bi bi-globe-asia-australia"></i> <?php echo $user['region']; ?></small>
                </a>
              </div>
              <div class="d-flex d-none d-md-block d-lg-block">
                <a class="text-decoration-none text-light fw-bold rounded-pill" href="../../artist.php?id=<?php echo $user['id']; ?>" data-bs-toggle="modal" data-bs-target="#userModal">
                  <?php if (!empty($user['pic'])): ?>
                    <img class="object-fit-cover border border-1 rounded-circle" src="../../<?php echo $user['pic']; ?>" style="width: 32px; height: 32px;">
                  <?php else: ?>
                    <img class="object-fit-cover border border-1 rounded-circle" src="../../icon/profile.svg" style="width: 32px; height: 32px;">
                  <?php endif; ?>
                  <?php echo $user['artist']; ?> <small class="badge rounded-pill bg-primary"><i class="bi bi-globe-asia-australia"></i> <?php echo $user['region']; ?></small>
                </a>
              </div>
              <div class="ms-auto">
                <form method="post">
                  <?php if ($is_following): ?>
                    <button class="btn btn-sm btn-outline-light rounded-pill fw-bold" type="submit" name="unfollow"><i class="bi bi-person-dash-fill"></i> unfollow</button>
                  <?php else: ?>
                    <button class="btn btn-sm btn-outline-light rounded-pill fw-bold" type="submit" name="follow"><i class="bi bi-person-fill-add"></i> follow</button>
                  <?php endif; ?>
                </form>
              </div>
            </div>
            <div class="container d-flex justify-content-center">
              <div class="btn-group">
                <a class="btn rounded" href="../gallery/?artworkid=<?php echo $image['id']; ?>"><i class="fs-4 bi bi-distribute-vertical"></i></a>
                <?php
                  $image_id = $image['id'];
                  $stmt = $db->query("SELECT COUNT(*) FROM favorites WHERE image_id = $image_id");
                  $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE email = :email AND image_id = :image_id");
                  $stmt->bindParam(':email', $email);
                  $stmt->bindParam(':image_id', $image_id);
                  $stmt->execute();
                  $is_favorited = $stmt->fetchColumn();
                  if ($is_favorited) : ?>
                  <form class="w-100" method="POST">
                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                    <button type="submit" class="text-start btn" name="unfavorite" id="unfavoriteButton">
                      <i class="fs-4 bi bi-heart-fill"></i>
                    </button>
                  </form>
                <?php else : ?>
                  <form class="w-100" method="POST">
                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                    <button type="submit" class="text-start btn" name="favorite" id="favoriteButton">
                      <i class="fs-4 bi bi-heart text-stroke"></i>
                    </button>
                  </form>
                <?php endif; ?>
                <button class="text-start fw-bold btn rounded" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="fs-4 bi bi-info-circle-fill"></i></button>
                <?php if ($next_image): ?>
                  <button class="text-start fw-bold btn rounded" id="option5" onclick="location.href='?artworkid=<?= $next_image['id'] ?>'">
                    <i class="fs-4 bi bi-chevron-left text-stroke-3"></i>
                  </button>
                <?php endif; ?> 
                <?php if ($prev_image): ?>
                  <button class="text-start fw-bold btn rounded" id="option6" onclick="location.href='?artworkid=<?= $prev_image['id'] ?>'">
                    <i class="fs-4 bi bi-chevron-right text-stroke-3"></i>
                  </button>
                <?php endif; ?>
                <a class="text-start fw-bold btn rounded" id="option3" href="../../image.php?artworkid=<?php echo $image['id']; ?>"><i class="fs-4 bi bi-arrow-left-circle-fill"></i></a>
              </div>
            </div>
          </div>
          <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-body">
                  <button class="fw-bold btn btn-outline-light w-100" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample"><i class="bi bi-caret-down-fill"></i> <small>more</small></button>
                  <div class="collapse mt-4" id="collapseExample">
                    <h6 class="fw-bold text-center"><?php echo $image['title']; ?></h6>
                    <p class="text-start fw-bold mt-4" style="word-wrap: break-word;">
                      <?php
                        if (!empty($image['imgdesc'])) {
                          $messageText = $image['imgdesc'];
                          $messageTextWithoutTags = strip_tags($messageText);
                          $pattern = '/\bhttps?:\/\/\S+/i';

                          $formattedText = preg_replace_callback($pattern, function ($matches) {
                            $url = htmlspecialchars($matches[0]);
                            return '<a href="' . $url . '">' . $url . '</a>';
                          }, $messageTextWithoutTags);

                          $formattedTextWithLineBreaks = nl2br($formattedText);
                            echo $formattedTextWithLineBreaks;
                          } else {
                            echo "Image description is empty.";
                        }
                      ?>
                    </p>
                    <h6 class="text-start fw-bold"><?php echo $total_size; ?> MB</h6>
                    <h5 class="fw-bold text-center my-3">Information: </h5>
                    <p class="fw-bold text-start">1. Swipe to left or right to navigate.</p>
                    <p class="fw-bold text-start">2. Double tap to show or hide the navbar.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
    <div class="modal fade" id="swipeModal" tabindex="-1" aria-labelledby="swipeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-body">
            <h5 class="fw-bold text-center mb-3">Information: </h5>
            <p class="fw-bold text-start">1. Swipe to left or right to navigate.</p>
            <p class="fw-bold text-start">2. Double tap to show or hide the navbar.</p>
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="dontRemindCheckbox">
              <label class="form-check-label fw-bold" for="dontRemindCheckbox">Don't remind me again!</label>
            </div>
            <button type="button" class="mt-3 btn btn-outline-light fw-bold w-100" data-bs-dismiss="modal">Okay, I understand!</button>
          </div>
        </div>
      </div>
    </div>
    <style>
      #scrollButton {
        transition: opacity 0.5s ease-in-out; /* Add smooth opacity transition */
        opacity: 0; /* Initially visible */
        display: block;
      }
      
      .image-info {
        transition: opacity 0.5s ease-in-out;
        opacity: 1;
        display: block;
      }

      .text-stroke {
        -webkit-text-stroke: 1px;
      }

      .text-stroke-2 {
        -webkit-text-stroke: 2px;
      }
      
      .text-stroke-3 {
        -webkit-text-stroke: 3px;
      }

      .carousel-container {
        overflow: hidden;
        width: 100%;
        height: 100vh; /* 100% of the viewport height */
      }

      #myCarousel {
        width: 100%;
        height: 100%;
      }

      .carousel-inner {
        width: 100%;
        height: 100%;
      }

      .carousel-item {
        width: 100%;
        height: 100%;
      }

      .carousel-item img {
        object-fit: contain; /* This ensures the image covers the entire container */
        width: 100%;
        height: 100%;
      }
    </style>
    <script>
      var isButtonVisible = false; // Set the initial state to invisible
      var lastTapTime = 0;

      function toggleButtonVisibility(event) {
        var currentTime = new Date().getTime();
        var tapTimeDiff = currentTime - lastTapTime;

        if (tapTimeDiff < 300) {
          var scrollButton = document.getElementById("scrollButton");
          if (isButtonVisible) {
            scrollButton.style.opacity = "0";
          } else {
            scrollButton.style.opacity = "1";
          }
          isButtonVisible = !isButtonVisible;
        }
        lastTapTime = currentTime;
      }

      // Add a touchstart event listener to the document to detect double tap
      document.addEventListener("touchstart", toggleButtonVisibility);

      let imageInfos = document.querySelectorAll(".image-info");

      imageInfos.forEach(info => {
        info.style.display = "none"; // Hide image info by default

        // Double-tap event listener
        let tapCount = 0;
        info.previousElementSibling.addEventListener("click", () => {
          tapCount++;
          setTimeout(() => {
            if (tapCount === 2) {
              // Double-tap detected, toggle image info
              info.style.display = info.style.display === "none" ? "block" : "none";
            }
            tapCount = 0;
          }, 300); // Adjust this timing as needed
        });
      });
    
      // Wait for the document to be fully loaded
      document.addEventListener("DOMContentLoaded", function () {
        // Check if the modal has been shown before
        var hasModalBeenShown = localStorage.getItem("hasModalBeenShown");

        if (!hasModalBeenShown || localStorage.getItem("dontRemindAgain") !== "true") {
          // Select the modal element by its ID
          var modal = document.getElementById("swipeModal");

          // Show the modal
          var modalInstance = new bootstrap.Modal(modal);
          modalInstance.show();

          // Set a flag in localStorage to indicate that the modal has been shown
          localStorage.setItem("hasModalBeenShown", "true");

          // Listen for changes to the "Don't remind me again!" checkbox
          var dontRemindCheckbox = document.getElementById("dontRemindCheckbox");
          dontRemindCheckbox.addEventListener("change", function () {
            if (dontRemindCheckbox.checked) {
              // If checked, set a flag in localStorage to not show the modal again
              localStorage.setItem("dontRemindAgain", "true");
            } else {
              // If unchecked, remove the flag
              localStorage.removeItem("dontRemindAgain");
            }
          });
        }
      });
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
