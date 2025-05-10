<?php
require_once('auth.php');

$db = new PDO('sqlite:database.sqlite');

$artworkId = $_GET['artworkid'];

$stmt = $db->prepare("SELECT * FROM images WHERE id = :artworkid ");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$image = $stmt->fetch();

if (!$image) {
  header("Location: error.php");
  exit;
}

$current_image_id = $image['id']; 
$image_owner_email_for_navigation = $image['email'];

$stmt = $db->prepare("SELECT * FROM images WHERE id < :id AND email = :email ORDER BY id DESC LIMIT 1");
$stmt->bindParam(':id', $current_image_id);
$stmt->bindParam(':email', $image_owner_email_for_navigation);
$stmt->execute();
$prev_image = $stmt->fetch();

$stmt = $db->prepare("SELECT * FROM images WHERE id > :id AND email = :email ORDER BY id ASC LIMIT 1");
$stmt->bindParam(':id', $current_image_id);
$stmt->bindParam(':email', $image_owner_email_for_navigation);
$stmt->execute();
$next_image = $stmt->fetch();

$loggedInUserEmail = '';
if (isset($_SESSION['email'])) {
  $loggedInUserEmail = $_SESSION['email'];
}

$imageOwnerEmail = $image['email'];

$query = $db->prepare('SELECT * FROM users WHERE email = :email');
$query->bindParam(':email', $imageOwnerEmail);
$query->execute();
$user = $query->fetch();

$query = $db->prepare('SELECT COUNT(*) FROM following WHERE follower_email = :follower_email AND following_email = :following_email');
$query->bindParam(':follower_email', $loggedInUserEmail);
$query->bindParam(':following_email', $imageOwnerEmail);
$query->execute();
$is_following = $query->fetchColumn();

if (isset($_POST['follow'])) {
  $query = $db->prepare('INSERT INTO following (follower_email, following_email) VALUES (:follower_email, :following_email)');
  $query->bindParam(':follower_email', $loggedInUserEmail);
  $query->bindParam(':following_email', $imageOwnerEmail);
  $query->execute();
  $is_following = true;
  header("Location: ?artworkid=" . urlencode($image['id']));
  exit;
} elseif (isset($_POST['unfollow'])) {
  $query = $db->prepare('DELETE FROM following WHERE follower_email = :follower_email AND following_email = :following_email');
  $query->bindParam(':follower_email', $loggedInUserEmail);
  $query->bindParam(':following_email', $imageOwnerEmail);
  $query->execute();
  $is_following = false;
  header("Location: ?artworkid=" . urlencode($image['id']));
  exit;
}

if (isset($_POST['favorite'])) {
  $image_id_fav = $_POST['image_id'];

  $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE email = :email AND image_id = :image_id");
  $stmt->bindParam(':email', $loggedInUserEmail);
  $stmt->bindParam(':image_id', $image_id_fav);
  $stmt->execute();
  $existing_fav = $stmt->fetchColumn();

  if ($existing_fav == 0) {
    $stmt = $db->prepare("INSERT INTO favorites (email, image_id) VALUES (:email, :image_id)");
    $stmt->bindParam(':email', $loggedInUserEmail);
    $stmt->bindParam(':image_id', $image_id_fav);
    $stmt->execute();
  }
  header("Location: ?artworkid=" . urlencode($image['id']));
  exit();
} elseif (isset($_POST['unfavorite'])) {
  $image_id_fav = $_POST['image_id'];
  $stmt = $db->prepare("DELETE FROM favorites WHERE email = :email AND image_id = :image_id");
  $stmt->bindParam(':email', $loggedInUserEmail);
  $stmt->bindParam(':image_id', $image_id_fav);
  $stmt->execute();
  header("Location: ?artworkid=" . urlencode($image['id']));
  exit();
}

$stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :image_id");
$stmt->bindParam(':image_id', $current_image_id);
$stmt->execute();
$child_images = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT COUNT(*) as total_images FROM images WHERE id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$total_images_main = $stmt->fetch()['total_images'];

$stmt = $db->prepare("SELECT COUNT(*) as total_child_images FROM image_child WHERE image_id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId); 
$stmt->execute();
$total_child_images = $stmt->fetch()['total_child_images'];

$total_all_images = $total_images_main + $total_child_images;

$original_image_path = 'images/' . $image['filename'];
$thumbnail_image_path = 'thumbnails/' . $image['filename'];
$original_image_size = file_exists($original_image_path) ? round(filesize($original_image_path) / (1024 * 1024), 2) : 0;
$thumbnail_image_size = file_exists($thumbnail_image_path) ? round(filesize($thumbnail_image_path) / (1024 * 1024), 2) : 0;

$reduction_percentage = 0;
if ($original_image_size > 0) {
  $reduction_percentage = ((($original_image_size - $thumbnail_image_size) / $original_image_size) * 100);
}

list($width, $height) = file_exists($original_image_path) ? getimagesize($original_image_path) : [0,0];
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

      <div class="position-absolute top-0 end-0 me-2 mt-2">
        <div class="btn-group">
          <?php if ($imageOwnerEmail === $loggedInUserEmail): ?>
            <a class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold opacity-75 rounded-3 rounded-end-0" onclick="event.preventDefault(); window.top.location.href=this.href;" href="edit_image.php?id=<?php echo urlencode($image['id']); ?>">
              <i class="bi bi-pencil-fill"></i> Edit Image
            </a>
          <?php endif; ?>
          <div class="dropdown">
            <button class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold opacity-75 <?php echo ($imageOwnerEmail === $loggedInUserEmail) ? 'rounded-start-0 rounded-3' : 'rounded-3'; ?>" type="button" data-bs-toggle="dropdown" aria-expanded="false">
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
              <li><small><a class="dropdown-item fw-bold" href="#">Total Size: <?php echo $total_album_size; ?> MB</a></small></li>
              <li><small><a class="dropdown-item fw-bold" href="#"><?php echo $viewCount; ?> views</a></small></li>
            </ul>
          </div>
        </div>
      </div>
      <?php include('view_option.php'); ?>
      <div class="position-absolute bottom-0 end-0 me-2 mb-2">
        <div class="btn-group">
          <div class="dropdown">
            <button class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> opacity-75 rounded-3 rounded-end-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
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
            $fav_image_id = $image['id'];
            $stmt_fav_count = $db->prepare("SELECT COUNT(*) FROM favorites WHERE image_id = :image_id");
            $stmt_fav_count->bindParam(':image_id', $fav_image_id);
            $stmt_fav_count->execute();
            $fav_count = $stmt_fav_count->fetchColumn();

            if ($fav_count >= 1000000000) {
              $fav_count_display = round($fav_count / 1000000000, 1) . 'b';
            } elseif ($fav_count >= 1000000) {
              $fav_count_display = round($fav_count / 1000000, 1) . 'm';
            } elseif ($fav_count >= 1000) {
              $fav_count_display = round($fav_count / 1000, 1) . 'k';
            } else {
              $fav_count_display = $fav_count;
            }
            
            $stmt_is_fav = $db->prepare("SELECT COUNT(*) FROM favorites WHERE email = :email AND image_id = :image_id");
            $stmt_is_fav->bindParam(':email', $loggedInUserEmail);
            $stmt_is_fav->bindParam(':image_id', $fav_image_id);
            $stmt_is_fav->execute();
            $is_favorited_by_user = $stmt_is_fav->fetchColumn();
            
            if ($is_favorited_by_user) {
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