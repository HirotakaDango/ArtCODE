<?php
require_once('auth.php');

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Check if an album has been specified
if (isset($_GET['album'])) {
  $album_id = $_GET['album'];
  $email = $_SESSION['email'];
  
  // Get the total number of images in the specified album
  $stmt = $db->prepare('SELECT COUNT(*) AS total_images FROM image_album WHERE album_id = :album_id AND email = :email');
  $stmt->bindValue(':album_id', $album_id, SQLITE3_INTEGER);
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
  $total = $result['total_images'];
  
  // Set the limit of images per page
  $limit = 100;
  
  // Get the current page number, default to 1
  $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
  
  // Calculate the offset based on the current page number and limit
  $offset = ($page - 1) * $limit;
  
  // Get the images for the specified album with pagination
  $stmt = $db->prepare('SELECT images.id, images.filename, images.title, images.imgdesc, images.link, album.album_name, image_album.id AS image_album_id FROM image_album INNER JOIN images ON image_album.image_id = images.id INNER JOIN album ON image_album.album_id = album.id WHERE image_album.album_id = :album_id AND image_album.email = :email ORDER BY image_album.id DESC LIMIT :limit OFFSET :offset');
  $stmt->bindValue(':album_id', $album_id, SQLITE3_INTEGER);
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
  $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
  $results = $stmt->execute();
  
  // Store images in an array for later use
  $images = [];
  while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $images[] = $row;
    
    $album_name = $row['album_name'];
  }
  
} else {
  // If no album is specified, redirect to album.php
  header('Location: album.php');
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
  header("Location: album_images.php?album=$album_id&page=$page");
  exit();
}

// Close the database connection 
$db->close();
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Album Images</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <?php include 'header.php'; ?>
    <?php if (empty($images)): ?>
      <h5 class="text-secondary fw-bold text-center mt-2">This album is empty</h5>
    <?php else: ?>
      <h5 class="text-secondary fw-bold text-center mt-2">My Album <?php echo htmlspecialchars($album_name); ?></h5>
    <?php endif; ?>
    <div class="images">
      <?php foreach ($images as $image): ?>
        <div class="image-container">
          <div class="position-relative">
            <a class="shadow rounded imagesA" href="image.php?artworkid=<?php echo htmlspecialchars($image['id']); ?>">
              <img class="lazy-load imagesImg" data-src="thumbnails/<?php echo htmlspecialchars($image['filename']); ?>" alt="<?php echo $image['title']; ?>">
            </a>
            <div class="position-absolute top-0 start-0">
              <div class="dropdown">
                <button class="btn btn-sm btn-dark ms-1 mt-1 rounded-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <form method="POST" onsubmit="return confirm('Are you sure you want to delete this image from the album?');">
                    <input type="hidden" name="image_album_id" value="<?php echo htmlspecialchars($image['image_album_id']); ?>">
                    <li><button type="submit" name="delete_image" class="dropdown-item fw-bold"><i class="bi bi-trash-fill"></i> delete</button></li>
                  </form> 
                  <li><button class="dropdown-item fw-bold" onclick="shareImage(<?php echo $image['id']; ?>)"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php
      $totalPages = ceil($total / $limit);
      $prevPage = $page - 1;
      $nextPage = $page + 1;
    ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?album=<?php echo $album_id; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
      <?php endif; ?>

      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?album=<?php echo $album_id; ?>&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
      <?php endif; ?>

      <?php
        // Calculate the range of page numbers to display
        $startPage = max($page - 2, 1);
        $endPage = min($page + 2, $totalPages);

        // Display page numbers within the range
        for ($i = $startPage; $i <= $endPage; $i++) {
          if ($i === $page) {
            echo '<span class="btn btn-sm btn-primary active fw-bold">' . $i . '</span>';
          } else {
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?album=' . $album_id . '&page=' . $i . '">' . $i . '</a>';
          }
        }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?album=<?php echo $album_id; ?>&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?album=<?php echo $album_id; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>
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
    <?php include('bootstrapjs.php'); ?>
  </body>
</html> 