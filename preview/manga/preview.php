<?php
try {
  if (isset($_GET['title']) && isset($_GET['id'])) {
    $episode_name = $_GET['title'];
    $image_id     = $_GET['id'];

    $db = new PDO('sqlite:../../database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "
      SELECT
        images.*,
        users.id AS userid,
        users.artist
      FROM images
      JOIN users ON images.email = users.email
      WHERE images.id = :image_id
        AND images.episode_name = :episode_name
        AND images.artwork_type = 'manga'
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':image_id', $image_id);
    $stmt->bindParam(':episode_name', $episode_name);
    $stmt->execute();
    $image_details = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$image_details) {
      echo '<p>Artwork details not found for the given title and ID.</p>';
      exit;
    }

    $query_child = "SELECT * FROM image_child WHERE image_id = :image_id ORDER BY id ASC";
    $stmt_child = $db->prepare($query_child);
    $stmt_child->bindParam(':image_id', $image_id);
    $stmt_child->execute();
    $image_child = $stmt_child->fetchAll(PDO::FETCH_ASSOC);

    $pageTitle = $image_details['title'];
    $user_id = $image_details['userid'];

    // Base URL for viewer (adjust path if necessary)
    $viewerBaseUrl = "/preview/manga/view.php";

  } else {
    echo '<p>Missing required title or id parameter.</p>';
    exit;
  }
} catch (PDOException $e) {
  error_log('Database Error: ' . $e->getMessage());
  echo '<p>An error occurred while retrieving preview details.</p>';
  exit;
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <style>
      .ratio-cover {
        position: relative;
        width: 100%;
        padding-bottom: 140%; /* Maintain aspect ratio */
      }
      .ratio-cover img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover; /* Cover the container */
      }
    </style>
  </head>
  <body>
    <div class="container-fluid p-3">
      <div class="d-flex justify-content-center align-items-center vh-100-sm">
        <div class="w-100">
          <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 g-1">
            <?php
            $link_page_1 = $viewerBaseUrl . '?title=' . urlencode($episode_name) . '&uid=' . urlencode($user_id) . '&id=' . urlencode($image_id) . '&page=1';
            ?>
            <div class="col">
              <a href="<?= $link_page_1 ?>" onclick="event.preventDefault(); window.top.location.href=this.href;">
                <div class="ratio-cover">
                  <img class="rounded lazy-load"
                       data-src="<?= '/thumbnails/' . $image_details['filename']; ?>"
                       alt="<?= htmlspecialchars($pageTitle) ?>">
                </div>
              </a>
            </div>

            <?php
            $page_number = 2;
            foreach ($image_child as $child) :
              $link_page_n = $viewerBaseUrl . '?title=' . urlencode($episode_name) . '&uid=' . urlencode($user_id) . '&id=' . urlencode($image_id) . '&page=' . $page_number;
              $alt_text = $pageTitle . ' - Page ' . $page_number;
            ?>
              <div class="col">
                <a href="<?= $link_page_n ?>" onclick="event.preventDefault(); window.top.location.href=this.href;">
                  <div class="ratio-cover">
                    <img class="rounded lazy-load"
                         data-src="<?= '/thumbnails/' . $child['filename']; ?>"
                         alt="<?= $alt_text ?>">
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

    <script>
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      let imageContainer = document.getElementById("image-container");

      // Set the default placeholder image
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
    </script>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>