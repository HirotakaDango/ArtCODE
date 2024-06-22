<?php
session_start();

$db = new PDO('sqlite:forum/database.db');
$db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT NOT NULL, password TEXT NOT NULL)");
$db->exec("CREATE TABLE IF NOT EXISTS posts (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, content TEXT NOT NULL, user_id INTEGER NOT NULL, date DATETIME, category TEXT, FOREIGN KEY (user_id) REFERENCES users(id))");
$db->exec("CREATE TABLE IF NOT EXISTS comments (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT, comment TEXT, date DATETIME, post_id TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS category (id INTEGER PRIMARY KEY AUTOINCREMENT, category_name TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS category (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, link)");
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
      <?php
      if (isset($_GET['search'])) {
        echo 'Search: "' . $_GET['search'] . '"';
      } elseif (isset($_GET['artist'])) {
        echo 'Artist: "' . $_GET['artist'] . '"';
      } elseif (isset($_GET['tag'])) {
        echo 'Tag: "' . $_GET['tag'] . '"';
      } elseif (isset($_GET['parody'])) {
        echo 'Parody: "' . $_GET['parody'] . '"';
      } elseif (isset($_GET['group'])) {
        echo 'Group: "' . $_GET['group'] . '"';
      } elseif (isset($_GET['categories'])) {
        echo 'Categories: "' . $_GET['categories'] . '"';
      } elseif (isset($_GET['language'])) {
        echo 'Language: "' . $_GET['language'] . '"';
      } else {
        echo 'ArtCODE - Manga';
      }
      ?>
    </title>
    <?php include('bootstrap.php'); ?>
    <?php include('connection.php'); ?>
    <link rel="icon" type="image/png" href="<?php echo $web; ?>/icon/favicon.png">
    <meta property="og:url" content="<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Manga-API">
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

      .text-stroke {
        -webkit-text-stroke: 1px;
      }
    </style>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container-fluid mb-5 mt-3">
      <?php
      // Build the API URL with query parameters
      $apiUrl = $web . '/api_manga.php';
      $queryString = http_build_query(array_filter([
        'search' => $_GET['search'] ?? null,
        'artist' => $_GET['artist'] ?? null,
        'uid' => $_GET['uid'] ?? null,
        'tag' => $_GET['tag'] ?? null,
        'parody' => $_GET['parody'] ?? null,
        'by' => $_GET['by'] ?? null,
        'group' => $_GET['group'] ?? null,
        'categories' => $_GET['categories'] ?? null,
        'language' => $_GET['language'] ?? null
      ]));
      if ($queryString) {
        $apiUrl .= '?' . $queryString;
      }

      // Fetch JSON data from api_manga.php
      $json = file_get_contents($apiUrl);
      $images = json_decode($json, true);
      $totalImages = count($images);
      $limit = 24;
      $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
      $offset = ($page - 1) * $limit;
      $totalPages = ceil($totalImages / $limit);
      $displayImages = array_slice($images, $offset, $limit);
      ?>
      
      <h6 class="fw-bold mb-3">
        <?php
          if (isset($_GET['search'])) {
            echo 'Search: "' . $_GET['search'] . '" (' . $totalImages . ')';
          } elseif (isset($_GET['artist'])) {
            echo 'Artist: "' . $_GET['artist'] . '" (' . $totalImages . ')';
          } elseif (isset($_GET['tag'])) {
            echo 'Tag: "' . $_GET['tag'] . '" (' . $totalImages . ')';
          } elseif (isset($_GET['parody'])) {
            echo 'Parody: "' . $_GET['parody'] . '" (' . $totalImages . ')';
          } elseif (isset($_GET['group'])) {
            echo 'Group: "' . $_GET['group'] . '" (' . $totalImages . ')';
          } elseif (isset($_GET['categories'])) {
            echo 'Categories: "' . $_GET['categories'] . '" (' . $totalImages . ')';
          } elseif (isset($_GET['language'])) {
            echo 'Language: "' . $_GET['language'] . '" (' . $totalImages . ')';
          } else {
            echo 'All (' . $totalImages . ')';
          }
        ?>
      </h6>
      <div class="dropdown mb-3">
        <button class="btn btn-sm btn-outline-light rounded-5 dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <?php
          $sortingLabel = '';
          if (!isset($_GET['by']) || $_GET['by'] == 'newest') {
            $sortingLabel = 'Sorted by newest';
          } elseif ($_GET['by'] == 'oldest') {
            $sortingLabel = 'Sorted by oldest';
          } elseif ($_GET['by'] == 'popular') {
            $sortingLabel = 'Sorted by popular';
          }
          
          echo $sortingLabel;
          ?>
        </button>
        <ul class="dropdown-menu rounded-4">
          <li><a class="dropdown-item fw-bold <?php echo (!isset($_GET['by']) || $_GET['by'] == 'newest') ? 'active' : ''; ?>" href="?<?php echo http_build_query(array_merge($_GET, ['by' => 'newest'])); ?>">Newest</a></li>
          <li><a class="dropdown-item fw-bold <?php echo ($_GET['by'] ?? '') == 'oldest' ? 'active' : ''; ?>" href="?<?php echo http_build_query(array_merge($_GET, ['by' => 'oldest'])); ?>">Oldest</a></li>
          <li><a class="dropdown-item fw-bold <?php echo ($_GET['by'] ?? '') == 'popular' ? 'active' : ''; ?>" href="?<?php echo http_build_query(array_merge($_GET, ['by' => 'popular'])); ?>">Popular</a></li>
        </ul>
      </div>
      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 g-1">
        <?php
        // Check if the images data is an array and not empty
        if (is_array($displayImages) && !empty($displayImages)) {
          foreach ($displayImages as $image) : ?>
            <div class="col">
              <div class="card border-0 rounded-4">
                <a href="title.php?title=<?= urlencode($image['episode_name']); ?>&uid=<?= $image['userid']; ?>" class="text-decoration-none">
                  <div class="ratio ratio-cover">
                    <img class="rounded rounded-bottom-0 object-fit-cover lazy-load" data-src="<?= $web . '/thumbnails/' . $image['filename']; ?>" alt="<?= $image['title']; ?>">
                  </div>
                  <h6 class="text-center fw-bold text-white text-decoration-none bg-dark-subtle p-2 rounded rounded-top-0" id="episode-name_img<?= $image['id']; ?>_<?= $image['userid']; ?>">「<?= $image['artist']; ?>」<?= $image['episode_name']; ?></h6>
                </a>
              </div>
            </div>
            <style>
              #episode-name_img<?php echo $image['id']; ?>_<?php echo $image['userid']; ?> {
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
                max-width: 24ch; /* Limit to 20 characters */
                transition: max-width 0.3s ease;
              }
            
              #episode-name_img<?php echo $image['id']; ?>_<?php echo $image['userid']; ?>.expand {
                max-width: none; /* Expand to full width */
                white-space: normal;
              }
            </style>
            <script>
              document.addEventListener("DOMContentLoaded", function() {
                const episodeName = document.getElementById('episode-name_img<?php echo $image['id']; ?>_<?php echo $image['userid']; ?>');
                const image = episodeName.closest('.card').querySelector('img');
            
                let timeout; // Variable to store timeout ID for delaying collapse
            
                const expandText = () => {
                  clearTimeout(timeout); // Clear any existing timeout
                  episodeName.classList.add('expand');
                };
            
                const collapseText = () => {
                  // Delay collapsing text to make it sensitive to slight touch
                  timeout = setTimeout(() => {
                    episodeName.classList.remove('expand');
                  }, 200); // Adjust delay time as needed (200ms here)
                };
            
                // Use mouseover and mouseleave for desktop hover sensitivity
                episodeName.addEventListener('mouseover', expandText);
                episodeName.addEventListener('mouseleave', collapseText);
                image.addEventListener('mouseover', expandText);
                image.addEventListener('mouseleave', collapseText);
            
                // Use touchstart and touchend for touch sensitivity
                episodeName.addEventListener('touchstart', expandText);
                episodeName.addEventListener('touchend', collapseText);
                image.addEventListener('touchstart', expandText);
                image.addEventListener('touchend', collapseText);
              });
            </script>
          <?php endforeach;
        } else { ?>
          <p class="fw-bold">No data found.</p>
        <?php } ?>
      </div>
      <div class="pagination d-flex gap-1 justify-content-center mt-3">
        <?php if (isset($page) && isset($totalPages)): ?>
          <a class="btn btn-sm btn-primary fw-bold <?php if($startPage <= 1) echo 'd-none'; ?>" href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>"><i class="bi text-stroke bi-chevron-double-left"></i></a>
        <?php endif; ?>
    
        <?php if (isset($page) && $page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
        <?php endif; ?>
    
        <?php
        if (isset($page) && isset($totalPages)) {
          // Calculate the range of page numbers to display
          $startPage = max($page - 2, 1);
          $endPage = min($page + 2, $totalPages);
    
          // Display page numbers within the range
          for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i === $page) {
              echo '<span class="btn btn-sm btn-primary active fw-bold">' . $i . '</span>';
            } else {
              echo '<a class="btn btn-sm btn-primary fw-bold" href="?' . http_build_query(array_merge($_GET, ['page' => $i])) . '">' . $i . '</a>';
            }
          }
        }
        ?>
    
        <?php if (isset($page) && isset($totalPages) && $page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
        <?php endif; ?>
    
        <?php if (isset($page) && isset($totalPages)): ?>
          <a class="btn btn-sm btn-primary fw-bold <?php if($totalPages <= 1) echo 'd-none'; ?>" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
        <?php endif; ?>
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