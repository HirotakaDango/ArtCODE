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
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container mt-2 d-flex justify-content-center">
      <div class="position-relative" style="height: calc(100vh - 75px);">
        <a href="#" id="originalImageLink" data-bs-toggle="modal" data-bs-target="#originalImageModal" data-original-src="images/<?php echo $image['filename']; ?>">
          <img class="img-pointer shadow-lg rounded-4" style="height: calc(100vh - 75px);" src="thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>">
        </a>
        <?php
          // Function to calculate the size of an image in MB
          function getImageSizeInMB($filename) {
            return round(filesize('images/' . $filename) / (1024 * 1024), 2);
          }

          // Get the total size of images from 'images' table
          $stmt = $db->prepare("SELECT * FROM images WHERE id = :artworkid");
          $stmt->bindParam(':artworkid', $artworkId);
          $stmt->execute();
          $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

          // Get the total size of images from 'image_child' table
          $stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :artworkid");
          $stmt->bindParam(':artworkid', $artworkId);
          $stmt->execute();
          $image_childs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                              
          // Function to format the date
          function formatDate($date) {
            return date('Y/F/l jS') ;
          }

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
    
        <?php include('view_option.php'); ?>
    
        <!-- Navigation Buttons -->
        <div class="position-absolute top-0 end-0 me-2 mt-2">
          <div class="btn-group">
            <div class="dropdown">
              <button class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> opacity-75 rounded-3 rounded-end-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-eye-fill"></i>
              </button>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item fw-bold" href="/view/gallery/?artworkid=<?php echo $image['id']; ?>">
                    <i class="bi bi-distribute-vertical"></i> full gallery view
                  </a>
                </li>
                <li>
                  <a class="dropdown-item fw-bold" href="/view/carousel/?artworkid=<?php echo $image['id']; ?>">
                    <i class="bi bi-distribute-horizontal"></i> full carousel view
                  </a>
                </li>
              </ul>
            </div>
            <?php if ($user_email === $email): ?>
              <a class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> opacity-75 rounded-0" href="edit_image.php?id=<?php echo $image['id']; ?>">
                <i class="bi bi-pencil-fill"></i>
              </a>
            <?php endif; ?>
            <button class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold opacity-75 rounded-0" id="loadOriginalBtn">Load Original Image</button>
            <a class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold opacity-75 rounded-3 rounded-start-0" data-bs-toggle="modal" data-bs-target="#downloadOption">
              <i class="bi bi-cloud-arrow-down-fill"></i>
            </a>
          </div>
        </div>
        <button id="showProgressBtn" class="fw-bold btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> position-absolute top-50 start-50 translate-middle text-nowrap rounded-pill opacity-75" style="display: none;">
          progress
        </button>
        <?php if ($next_image): ?>
          <div class="">
            <button class="btn btn-sm opacity-75 rounded fw-bold position-absolute start-0 top-50 translate-middle-y rounded-start-0"  onclick="location.href='?artworkid=<?= $next_image['id'] ?>'">
              <i class="bi bi-chevron-left fs-5" style="-webkit-text-stroke: 4px;"></i>
            </button>
          </div>
        <?php endif; ?> 
        <?php if ($prev_image): ?>
          <div class="">
            <button class="btn btn-sm opacity-75 rounded fw-bold position-absolute end-0 top-50 translate-middle-y rounded-end-0"  onclick="location.href='?artworkid=<?= $prev_image['id'] ?>'">
              <i class="bi bi-chevron-right fs-5" style="-webkit-text-stroke: 4px;"></i>
            </button>
          </div>
        <?php endif; ?> 
      </div>
    </div>
    <script>
      function copyToClipboard() {
        var urlInput = document.getElementById('urlInput');
        urlInput.select();
        urlInput.setSelectionRange(0, 99999); // For mobile devices

        document.execCommand('copy');
      }

      function copyToClipboard1() {
        var urlInput1 = document.getElementById('urlInput1');
        urlInput1.select();
        urlInput1.setSelectionRange(0, 99999); // For mobile devices

        document.execCommand('copy');
      }

      document.addEventListener("DOMContentLoaded", function() {
        const toggleButtonExpand = document.getElementById("toggleButtonExpand");
        const caretIconExpand = toggleButtonExpand.querySelector("i");
        const toggleTextExpand = document.getElementById("toggleTextExpand");
        const collapseDataImageExpand = document.getElementById("collapseMoreExpand");

        toggleButtonExpand.addEventListener("click", function() {
          if (caretIconExpand.classList.contains("bi-caret-down-fill")) {
            caretIconExpand.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
            toggleTextExpand.innerText = "show less images";
          } else {
            caretIconExpand.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
            toggleTextExpand.innerText = "show more images";
          }
        });

        collapseDataImage.addEventListener("hidden.bs.collapse", function () {
          caretIconExpand.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
          toggleTextExpand.innerText = "show more images";
        });

        collapseDataImageExpand.addEventListener("shown.bs.collapse", function () {
          caretIconExpand.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
          toggleTextExpand.innerText = "show less images";
        });
      });
      
      document.addEventListener("DOMContentLoaded", function() {
        const toggleButton = document.getElementById("toggleButton");
        const caretIcon = toggleButton.querySelector("i");
        const toggleText = document.getElementById("toggleText");
        const collapseDataImage = document.getElementById("collapseDataImage");

        toggleButton.addEventListener("click", function() {
          if (caretIcon.classList.contains("bi-caret-down-fill")) {
            caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
            toggleText.innerText = "show less";
          } else {
            caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
            toggleText.innerText = "show more";
          }
        });

        collapseDataImage.addEventListener("hidden.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
          toggleText.innerText = "show more";
        });

        collapseDataImage.addEventListener("shown.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
          toggleText.innerText = "show less";
        });
      });

      document.addEventListener("DOMContentLoaded", function() {
        const toggleButton1 = document.getElementById("toggleButton1");
        const caretIcon = toggleButton1.querySelector("i");
        const collapseExample = document.getElementById("collapseExample1");

        toggleButton1.addEventListener("click", function() {
          if (caretIcon.classList.contains("bi-caret-down-fill")) {
            caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
          } else {
            caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
          }
        });

        collapseExample.addEventListener("hidden.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
        });

        collapseExample.addEventListener("shown.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
        });
      });

      document.addEventListener("DOMContentLoaded", function() {
        const toggleButton2 = document.getElementById("toggleButton2");
        const caretIcon = toggleButton2.querySelector("i");
        const collapseExample = document.getElementById("collapseDataImage");

        toggleButton2.addEventListener("click", function() {
          if (caretIcon.classList.contains("bi-caret-down-fill")) {
            caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
          } else {
            caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
          }
        });

        collapseExample.addEventListener("hidden.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
        });

        collapseExample.addEventListener("shown.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
        });
      });

      document.addEventListener("DOMContentLoaded", function() {
        const toggleButton3 = document.getElementById("toggleButton3");
        const caretIcon = toggleButton3.querySelector("i");
        const collapseExample = document.getElementById("collapseDataImage1");

        toggleButton3.addEventListener("click", function() {
          if (caretIcon.classList.contains("bi-caret-down-fill")) {
            caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
            toggleButton3.innerHTML = "<i class='bi bi-caret-down-fill'></i> <small>show more</small>";
          } else {
            caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
            toggleButton3.innerHTML = "<i class='bi bi-caret-up-fill'></i> <small>show less</small>";
          }
        });

        collapseExample.addEventListener("hidden.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
          toggleButton3.innerHTML = "<i class='bi bi-caret-down-fill'></i> <small>show more</small>";
        });

        collapseExample.addEventListener("shown.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
          toggleButton3.innerHTML = "<i class='bi bi-caret-up-fill'></i> <small>show less</small>";
        });
      });
    </script>
    <script>
      var originalImageLink = document.getElementById("originalImageLink");
      var originalImage = document.getElementById("originalImage");
      var originalImageSrc = originalImageLink.getAttribute("data-original-src");

      originalImageLink.addEventListener("click", function(event) {
        event.preventDefault();
        originalImage.setAttribute("src", originalImageSrc);
      });

      // Update the Load Original button functionality
      var loadOriginalBtn = document.getElementById("loadOriginalBtn");
      var showProgressBtn = document.getElementById("showProgressBtn");
      var thumbnailImage = document.querySelector("#originalImageLink img");

      if (loadOriginalBtn) {
        loadOriginalBtn.addEventListener("click", function(event) {
          event.preventDefault();

          var originalSrc = originalImageLink.getAttribute("data-original-src");
          thumbnailImage.setAttribute("src", originalSrc);

          // Hide the "loadOriginalBtn" after it's clicked
          loadOriginalBtn.style.display = "none";

          // Show the "showProgressBtn" to indicate progress
          showProgressBtn.style.display = "block";

          var xhr = new XMLHttpRequest();
          xhr.open("GET", originalSrc, true);
          xhr.responseType = "blob";

          xhr.onprogress = function(event) {
            if (event.lengthComputable) {
              var percentLoaded = (event.loaded / event.total) * 100;
              showProgressBtn.textContent = "Loading Image: " + percentLoaded.toFixed(2) + "% (<?php echo $images_total_size; ?> MB)";
            }
          };

          xhr.onload = function() {
            var blob = xhr.response;
            var objectURL = URL.createObjectURL(blob);
            thumbnailImage.setAttribute("src", objectURL);
            // Hide the progress button when loading is complete
            showProgressBtn.style.display = "none";
          };

          xhr.send();
        });
      }

      if (window.innerWidth <= 767) {
        window.location.href = 'image.php?artworkid=<?php echo $image['id']; ?>';
      }
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>