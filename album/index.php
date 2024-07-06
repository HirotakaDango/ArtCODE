<?php
require_once('../auth.php');

// Connect to the SQLite database
$db = new SQLite3('../database.sqlite');

$album_id = $_GET['id'];
$email = $_SESSION['email'];

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $existing_fav = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = $image_id");

  if ($existing_fav == 0) {
    $db->exec("INSERT INTO favorites (email, image_id) VALUES ('$email', $image_id)");
  }
  
  // Redirect to the same page with the appropriate sorting parameter
  header("Location: {$_SERVER['REQUEST_URI']}");
  exit(); 
  
} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $db->exec("DELETE FROM favorites WHERE email = '$email' AND image_id = $image_id");

  // Redirect to the same page with the appropriate sorting parameter
  header("Location: {$_SERVER['REQUEST_URI']}");
  exit();
}

// Handle deleting an image from the album if the delete button was clicked
if (isset($_POST['delete_image'])) {
  $image_album_id = $_POST['image_album_id'];
  $stmt = $db->prepare('DELETE FROM image_album WHERE id = :id AND email = :email');
  $stmt->bindValue(':id', $image_album_id, SQLITE3_INTEGER);
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->execute();

  // Redirect back to the same page to update the displayed images
  header("Location: {$_SERVER['REQUEST_URI']}");
  exit();
}

// Check if an album has been specified
if (!isset($_GET['id'])) {
  // If no album is specified, redirect to albums.php
  header('Location: ../album.php');
  exit();
}

// Check if the specified album exists by verifying the album_id and email
$stmt = $db->prepare('
    SELECT COUNT(*) AS total_images
    FROM image_album
    WHERE album_id = :album_id AND email = :email
');
$stmt->bindValue(':album_id', $album_id, SQLITE3_INTEGER);
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

// Check if the album exists
if ($result['total_images'] == 0) {
  // If the album does not exist, redirect to albums.php
  header('Location: ../album.php');
  exit();
}

// Get the total number of images in the specified album and album name
$stmt = $db->prepare('
    SELECT COUNT(image_album.id) AS total_images, album.album_name
    FROM image_album
    INNER JOIN album ON image_album.album_id = album.id
    WHERE image_album.album_id = :album_id AND image_album.email = :email
');
$stmt->bindValue(':album_id', $album_id, SQLITE3_INTEGER);
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

$total = $result['total_images'];
$album_name = $result['album_name'];
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <title>
      <?php if ($total == 0): ?>
        This album is empty
      <?php else: ?>
        My Album: <?php echo $album_name; ?>
      <?php endif; ?>
    </title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="../style.css">
  </head>
  <body>
    <?php include('../header.php'); ?>
    <?php if ($total == 0): ?>
      <h5 class="fw-bold ms-2">This album is empty</h5>
    <?php else: ?>
      <h5 class="fw-bold ms-2"><i class="bi bi-journal-album"></i> <?php echo $album_name; ?></h5>
    <?php endif; ?>
    <div class="dropdown">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=newest&id=<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?by=oldest&id=<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?by=popular&id=<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">popular</a></li>
        <li><a href="?by=view&id=<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'view') echo 'active'; ?>">most viewed</a></li>
        <li><a href="?by=least&id=<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'least') echo 'active'; ?>">least viewed</a></li>
        <li><a href="?by=liked&id=<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'liked') echo 'active'; ?>">liked</a></li>
        <li><a href="?by=order_asc&id=<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'order_asc') echo 'active'; ?>">from A to Z</a></li>
        <li><a href="?by=order_desc&id=<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'order_desc') echo 'active'; ?>">from Z to A</a></li>
      </ul> 
    </div> 
        <?php 
        if(isset($_GET['by'])){
          $sort = $_GET['by'];
 
          switch ($sort) {
            case 'newest':
            include "index_desc.php";
            break;
            case 'oldest':
            include "index_asc.php";
            break;
            case 'popular':
            include "index_pop.php";
            break;
            case 'view':
            include "index_view.php";
            break;
            case 'least':
            include "index_least.php";
            break;
            case 'liked':
            include "index_like.php";
            break;
            case 'order_asc':
            include "index_order_asc.php";
            break;
            case 'order_desc':
            include "index_order_desc.php";
            break;
          }
        }
        else {
          include "index_desc.php";
        }
        
        ?>
    <script>
      function shareImage(userId) {
        // Compose the share URL
        var shareUrl = '/image.php?artworkid=' + userId;

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
      const defaultPlaceholder = "icon/bg.png";

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
          image.addEventListener("load", function() {
            image.style.filter = "none"; // Remove blur after image loads
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
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html> 