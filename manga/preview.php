<?php
session_start();

$db = new PDO('sqlite:forum/database.db');
$db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT NOT NULL, password TEXT NOT NULL)");
$db->exec("CREATE TABLE IF NOT EXISTS posts (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, content TEXT NOT NULL, user_id INTEGER NOT NULL, date DATETIME, category TEXT, FOREIGN KEY (user_id) REFERENCES users(id))");
$db->exec("CREATE TABLE IF NOT EXISTS comments (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT, comment TEXT, date DATETIME, post_id TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS category (id INTEGER PRIMARY KEY AUTOINCREMENT, category_name TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS favorites (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, link TEXT, image_cover TEXT, episode_name TEXT)");
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_GET['title']; ?></title>
    <?php include('bootstrap.php'); ?>
    <?php include('connection.php'); ?>
    <link rel="icon" type="image/png" href="<?php echo $web; ?>/icon/favicon.png">
    <meta property="og:url" content="<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo $_GET['title']; ?>">
    <meta property="og:image" content="<?php echo $web; ?>/icon/favicon.png">
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
    <?php include('header.php'); ?>
    <div class="container-fluid my-5">
      <a class="btn bg-body-tertiary link-body-emphasis fw-bold mb-5" href="title.php?title=<?php echo urlencode($_GET['title']); ?>&uid=<?php echo $_GET['uid']; ?>&id=<?php echo $_GET['id']; ?>">Back to Title</a>
      <div class="d-flex justify-content-center align-items-center vh-100-sm">
        <div class="w-100">
          <?php
          // Check if title, uid, and id parameters are provided
          if (isset($_GET['title']) && isset($_GET['uid']) && isset($_GET['id'])) {
            $episode_name = $_GET['title'];
            $user_id = $_GET['uid'];
            $image_id = $_GET['id'];

            // Set page to 1 if not provided (default to main image)
            $page = isset($_GET['page']) ? $_GET['page'] : 1;

            // Fetch JSON data from api_manga_view.php with title, uid, and id parameters
            $json = file_get_contents($web . '/api_manga_preview.php?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id);
            $data = json_decode($json, true);

            // Check if the data is an array and not empty
            if (is_array($data) && !empty($data)) {
              $image_details = $data['image_details'];
              $image_child = $data['image_child'];

              // Create the grid layout
              echo '<div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 g-1">';

              // Main image is always displayed as page=1
              if (!empty($image_details['filename']) && $page == 1) {
                ?>
                <div class="col">
                  <a href="view.php?title=<?php echo urlencode($_GET['title']); ?>&uid=<?php echo $_GET['uid']; ?>&id=<?php echo $_GET['id']; ?>&page=1">
                    <div class="ratio-cover">
                      <img class="rounded lazy-load" data-src="<?= $web . '/images/' . $image_details['filename']; ?>" alt="Main Image for <?= $episode_name; ?>">
                    </div>
                  </a>
                </div>
                <?php
              }

              // Loop through all child images and display them
              $page_number = 2; // Start with page=2 for the child images
              foreach ($image_child as $image) {
                ?>
                <div class="col">
                  <a href="view.php?title=<?php echo urlencode($_GET['title']); ?>&uid=<?php echo $_GET['uid']; ?>&id=<?php echo $_GET['id']; ?>&page=<?php echo $page_number; ?>">
                    <div class="ratio-cover">
                      <img class="w-100 rounded lazy-load" data-src="<?= $web . '/thumbnails/' . $image['filename']; ?>" alt="<?= $episode_name; ?>">
                    </div>
                  </a>
                </div>
                <?php
                $page_number++; // Increment the page number for the next image
              }

              echo '</div>'; // Close the row
            } else {
              echo '<p class="position-absolute top-50 start-50">No data found.</p>';
            }
          } else {
            echo '<p>Missing title, uid, or id parameter.</p>';
          }
          ?>
        </div>
      </div>
    </div>
    <script>
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      let imageContainer = document.getElementById("image-container");

      // Set the default placeholder image
      const defaultPlaceholder = "<?php echo $web; ?>/icon/bg.png";

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
  </body>
</html>