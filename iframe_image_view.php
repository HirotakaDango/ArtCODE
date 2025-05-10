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
    <title><?php echo htmlspecialchars($image['title']); ?></title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
    <style>
      body {
        margin: 0;
        padding: 0;
        overflow: hidden;
      }

      #showProgressBtn {
        display: none;
      }
    </style>
  </head>
  <body class="vh-100 d-flex justify-content-center align-items-center bg-dark">
    <div class="position-relative w-100 vh-100 d-flex justify-content-center align-items-center">
      <a href="#" id="originalImageLink" data-bs-toggle="modal" data-bs-target="#originalImageModal" data-original-src="/images/<?php echo $image['filename']; ?>" class="d-block w-100 h-100">
        <img class="img-pointer shadow-lg w-100 h-100 object-fit-cover" src="/thumbnails/<?php echo htmlspecialchars($image['filename']); ?>" alt="<?php echo htmlspecialchars($image['title']); ?>">
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
      <div class="position-absolute top-0 end-0 me-2 mt-2">
        <div class="btn-group">
          <?php if ($user_email === $email): ?>
            <!-- Display the edit button only if the current user is the owner of the image -->
            <a class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold opacity-75 rounded-3 rounded-end-0" href="edit_image.php?id=<?php echo $image['id']; ?>">
              <i class="bi bi-pencil-fill"></i> Edit Image
            </a>
          <?php endif; ?>
          <div class="dropdown">
            <button class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold opacity-75 <?php echo ($user_email === $email) ? 'rounded-start-0 rounded-3' : 'rounded-3'; ?>" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-images"></i> <?php echo $total_all_images; ?>
            </button>
            <ul class="dropdown-menu">
              <li><small><a class="dropdown-item fw-bold" href="#">
                <?php 
                  if ($total_all_images == 1) {
                    echo "Total Image: 1 image";
                  } else {
                    echo "Total Images: " . $total_all_images . " images";
                  }
                ?>
              </a></small></li>
              <li><small><a class="dropdown-item fw-bold" href="#">Total Size: <?php echo $total_size; ?> MB</a></small></li>
              <li><small><a class="dropdown-item fw-bold" href="#"><?php echo $viewCount; ?> views</a></small></li>
            </ul>
          </div>
        </div>
      </div>
      <?php include('view_option.php'); ?>
      <div class="position-absolute bottom-0 end-0 me-2 mb-2">
        <div class="btn-group">
          <div class="dropdown">
            <button class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold opacity-75 rounded-3 rounded-end-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-eye-fill"></i>
            </button>
            <ul class="dropdown-menu">
              <li>
                <a class="dropdown-item fw-bold" onclick="event.preventDefault(); window.top.location.href=this.href;" href="/view/gallery/?artworkid=<?php echo urlencode($image['id']); ?>">
                  <i class="bi bi-distribute-vertical"></i> full gallery view
                </a>
              </li>
              <li>
                <a class="dropdown-item fw-bold" onclick="event.preventDefault(); window.top.location.href=this.href;" href="/view/manga/?artworkid=<?php echo urlencode($image['id']); ?>&page=1">
                  <i class="bi bi-journals"></i> full manga view
                </a>
              </li>
              <li>
                <a class="dropdown-item fw-bold" onclick="event.preventDefault(); window.top.location.href=this.href;" href="/view/carousel/?artworkid=<?php echo urlencode($image['id']); ?>">
                  <i class="bi bi-distribute-horizontal"></i> full carousel view
                </a>
              </li>
            </ul>
          </div>
          <button class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold opacity-75 rounded-0" id="loadOriginalBtn">Load Original Image</button>
          <?php
            $image_id = $image['id'];
            $stmt = $db->query("SELECT COUNT(*) FROM favorites WHERE image_id = $image_id");
            $fav_count = $stmt->fetchColumn();
            if ($fav_count >= 1000000000) {
              $fav_count = round($fav_count / 1000000000, 1) . 'b';
            } elseif ($fav_count >= 1000000) {
              $fav_count = round($fav_count / 1000000, 1) . 'm';
            } elseif ($fav_count >= 1000) {
              $fav_count = round($fav_count / 1000, 1) . 'k';
            }
            $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE email = :email AND image_id = :image_id");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':image_id', $image_id);
            $stmt->execute();
            $is_favorited = $stmt->fetchColumn();
            if ($is_favorited) {
          ?>
            <form action="?artworkid=<?php echo urlencode($image['id']); ?>" method="POST" class="d-inline">
              <input type="hidden" name="image_id" value="<?php echo htmlspecialchars($image['id']); ?>">
              <button type="submit" class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold opacity-75 rounded-3 rounded-start-0" name="unfavorite"><i class="bi bi-heart-fill"></i></button>
            </form>
          <?php } else { ?>
            <form action="?artworkid=<?php echo urlencode($image['id']); ?>" method="POST" class="d-inline">
              <input type="hidden" name="image_id" value="<?php echo htmlspecialchars($image['id']); ?>">
              <button type="submit" class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold opacity-75 rounded-3 rounded-start-0" name="favorite"><i class="bi bi-heart"></i></button>
            </form>
          <?php } ?>
        </div>
      </div>
      <button id="showProgressBtn" class="fw-bold btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> position-absolute top-50 start-50 translate-middle text-nowrap rounded-pill opacity-75">
        progress
      </button>
    </div>

    <script>
      var originalImageLink = document.getElementById("originalImageLink");
      var originalImageDisplayInModal = document.getElementById("originalImage"); // Image tag in the modal
      
      if (originalImageLink && originalImageDisplayInModal) {
        originalImageLink.addEventListener("click", function(event) {
          event.preventDefault(); // Prevent default link behavior if it's just for modal
          var originalImageSrc = originalImageLink.getAttribute("data-original-src");
          originalImageDisplayInModal.setAttribute("src", originalImageSrc);
          // The data-bs-toggle and data-bs-target attributes on the link will handle modal display
        });
      }

      var loadOriginalBtn = document.getElementById("loadOriginalBtn");
      var showProgressBtn = document.getElementById("showProgressBtn");
      var thumbnailImage = document.querySelector("#originalImageLink img"); // The main displayed image

      if (loadOriginalBtn && thumbnailImage && showProgressBtn) {
        loadOriginalBtn.addEventListener("click", function(event) {
          event.preventDefault();

          var originalSrc = originalImageLink.getAttribute("data-original-src");
          
          loadOriginalBtn.style.display = "none";
          showProgressBtn.style.display = "block";
          showProgressBtn.textContent = "Loading Image: 0.00% (<?php echo $original_image_size; ?> MB)";


          var xhr = new XMLHttpRequest();
          xhr.open("GET", originalSrc, true);
          xhr.responseType = "blob";

          xhr.onprogress = function(event) {
            if (event.lengthComputable) {
              var percentLoaded = (event.loaded / event.total) * 100;
              showProgressBtn.textContent = "Loading Image: " + percentLoaded.toFixed(2) + "% (<?php echo $original_image_size; ?> MB)";
            }
          };

          xhr.onload = function() {
            if (xhr.status === 200) {
              var blob = xhr.response;
              var objectURL = URL.createObjectURL(blob);
              thumbnailImage.setAttribute("src", objectURL);
            } else {
              thumbnailImage.setAttribute("src", originalSrc); // Fallback to direct link if XHR fails
              console.error("Failed to load image via XHR, falling back to direct src.");
            }
            showProgressBtn.style.display = "none";
          };

          xhr.onerror = function() {
            showProgressBtn.textContent = "Error loading image.";
            // Optionally, re-show the load button or try direct load
            // loadOriginalBtn.style.display = "block"; // Or some other error handling
            thumbnailImage.setAttribute("src", originalSrc); // Fallback
             setTimeout(() => {  showProgressBtn.style.display = "none"; }, 2000);
          };

          xhr.send();
        });
      }
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>