<?php
require_once('../../auth.php');

try {
  // Check if artworkid parameter is provided
  if (isset($_GET['artworkid'])) {
    $image_id = $_GET['artworkid'];
    
    // Connect to the SQLite database
    $db = new PDO('sqlite:../../database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get the image details from the images table
    $query = "
      SELECT 
        images.*, 
        users.id AS userid, 
        users.artist
      FROM images
      JOIN users ON images.email = users.email
      WHERE images.id = :image_id
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':image_id', $image_id);
    $stmt->execute();
    $image_details = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get all child images for the current image ID
    $query_child = "SELECT * FROM image_child WHERE image_id = :image_id";
    $stmt_child = $db->prepare($query_child);
    $stmt_child->bindParam(':image_id', $image_id);
    $stmt_child->execute();
    $image_child = $stmt_child->fetchAll(PDO::FETCH_ASSOC);
    
    // Variables for preview page URLs.
    $pageTitle = $image_details['title'];
    $uid = $image_details['userid'];
    
    // Base URL for images. Adjust if needed.
    $web = "/";
  } else {
    echo '<p>Missing artworkid parameter.</p>';
    exit;
  }
} catch (PDOException $e) {
  echo '<p>Error: ' . $e->getMessage() . '</p>';
  exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <style>
      .ratio-cover {
        position: relative;
        width: 100%;
        padding-bottom: 140%;
      }
      .ratio-cover img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
    </style>
  </head>
  <body>
    <div class="container-fluid p-3">
      <div class="d-flex justify-content-center align-items-center vh-100-sm">
        <div class="w-100">
          <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 g-1">
            <!-- Main image thumbnail (Page 1) -->
            <div class="col">
              <a href="/view/manga/?artworkid=<?php echo $image_id; ?>&page=1" onclick="event.preventDefault(); window.top.location.href=this.href;">
                <div class="ratio-cover">
                  <img class="rounded lazy-load" data-src="<?= '/thumbnails/' . $image_details['filename']; ?>" alt="<?php echo $pageTitle; ?>">
                </div>
              </a>
            </div>
            <!-- Child image thumbnails -->
            <?php
              $page_number = 2; // Child images start with page 2
              foreach ($image_child as $child) :
            ?>
              <div class="col">
                <a href="/view/manga/?artworkid=<?php echo $image_id; ?>&page=<?= $page_number; ?>" onclick="event.preventDefault(); window.top.location.href=this.href;">
                  <div class="ratio-cover">
                    <img class="rounded lazy-load" 
                         data-src="<?= '/thumbnails/' . $child['filename']; ?>" 
                         alt="<?php echo $pageTitle; ?> - Page <?php echo $page_number; ?>">
                  </div>
                </a>
              </div>
            <?php 
              $page_number++;
              endforeach;
            ?>
          </div>
        </div>
      </div>
    </div>
    <!-- Lazy-load script -->
    <script>
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      // Default placeholder image; adjust the path as needed.
      const defaultPlaceholder = "/icon/bg.png";

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
          image.src = defaultPlaceholder;
          imageObserver.observe(image);
          image.style.filter = "blur(5px)";
          image.addEventListener("load", function() {
            image.style.filter = "none";
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
            lazyloadImages = document.querySelectorAll(".lazy-load");
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
    </script>
  </body>
</html>