<?php
require_once('auth.php');

$dbP = new SQLite3('database.sqlite'); 

$email = $_SESSION['email'];

// Prepare the query to get the user's numpage
$queryNum = $dbP->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT); // Assuming $email is the email you want to search for
$resultNum = $queryNum->execute();
$user = $resultNum->fetchArray(SQLITE3_ASSOC);

$numpage = $user['numpage'];

// Set the limit of images per page
$limit = empty($numpage) ? 50 : $numpage;

// Get the current page number from the query string
if (isset($_GET['page'])) {
  $page = $_GET['page'];
} else {
  $page = 1;
}

$page = isset($_GET['page']) ? $_GET['page'] : 1;

// Calculate the offset value for the SQL query
$offset = ($page - 1) * $limit;

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $existing_fav = $dbP->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = $image_id");

  if ($existing_fav == 0) {
    $dbP->exec("INSERT INTO favorites (email, image_id) VALUES ('{$_SESSION['email']}', $image_id)");
  }

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: popular.php?page=$page");
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $dbP->exec("DELETE FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = $image_id");

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: popular.php?page=$page"); 
  exit();
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <?php include('header.php'); ?>
    <h5 class="ms-2 mt-2 text-secondary fw-bold"><i class="bi bi-images"></i> Popular Images</h5>
    <div class="images mb-2">
      <?php
        $email = $_SESSION['email'];

        // Get the total number of images
        $total = $dbP->querySingle("SELECT COUNT(*) FROM images");

        // Calculate the total number of pages
        $totalPages = ceil($total / $limit);
 
        // Get the images for the current page using a parameterized query with LIMIT and OFFSET clauses
        $stmtP = $dbP->prepare("SELECT images.*, COUNT(favorites.id) AS favorite_count FROM images LEFT JOIN favorites ON images.id = favorites.image_id GROUP BY images.id ORDER BY favorite_count DESC LIMIT :limit OFFSET :offset");
        $stmtP->bindValue(':limit', $limit, SQLITE3_INTEGER);
        $stmtP->bindValue(':offset', $offset, SQLITE3_INTEGER);
        $resultP = $stmtP->execute();

        // Display the images for the current page
        while ($imageP = $resultP->fetchArray()):
      ?>
        
        <div class="image-container">
          <div class="position-relative">
            <a class="shadow rounded imagesA" href="image.php?artworkid=<?php echo $imageP['id']; ?>">
              <img class="lazy-load imagesImg  <?php echo ($imageP['type'] === 'nsfw') ? 'nsfw' : ''; ?>" data-src="thumbnails/<?php echo $imageP['filename']; ?>" alt="<?php echo $imageP['title']; ?>">
            </a>
            <div class="position-absolute top-0 start-0">
              <div class="dropdown">
                <button class="btn btn-sm btn-dark ms-1 mt-1 rounded-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <?php
                    $stmt = $dbP->prepare("SELECT COUNT(*) FROM favorites WHERE email = ? AND image_id = ?");
                    $stmt->bindValue(1, $email, SQLITE3_TEXT);
                    $stmt->bindValue(2, $imageP['id'], SQLITE3_INTEGER);
                    $result = $stmt->execute()->fetchArray();
                    $is_favorited = $result[0];
                    if ($is_favorited) {
                  ?>
                    <form method="POST">
                      <input type="hidden" name="image_id" value="<?php echo $imageP['id']; ?>">
                      <li><button type="submit" class="dropdown-item fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i> <small>unfavorite</small></button></li>
                    </form>
                  <?php } else { ?>
                    <form method="POST">
                      <input type="hidden" name="image_id" value="<?php echo $imageP['id']; ?>">
                      <li><button type="submit" class="dropdown-item fw-bold" name="favorite"><i class="bi bi-heart"></i> <small>favorite</small></button></li>
                    </form>
                  <?php } ?>
                  <li><button class="dropdown-item fw-bold" onclick="shareImage(<?php echo $imageP['id']; ?>)"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
                  <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $imageP['id']; ?>"><i class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
                </ul>

                <?php include('card_image_2.php'); ?>

              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div> 
    <?php
      $totalPages = ceil($total / $limit);
      $prevPage = $page - 1;
      $nextPage = $page + 1;
    ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
      <?php endif; ?>

      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?page=' . $i . '">' . $i . '</a>';
          }
        }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
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
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>