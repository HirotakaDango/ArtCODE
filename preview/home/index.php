<?php
// Connect to the SQLite database using parameterized query
$db = new SQLite3('../../database.sqlite');
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS daily (id INTEGER PRIMARY KEY AUTOINCREMENT, image_id TEXT NOT NULL, views INT DEFAULT 0, date DATETIME)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS images (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, email TEXT, tags TEXT, title TEXT, imgdesc TEXT, link TEXT, date DATETIME, view_count INT DEFAULT 0, type TEXT, episode_name TEXT, artwork_type TEXT, `group` TEXT, categories TEXT, language TEXT, parodies TEXT, characters TEXT, original_filename TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS messages (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, message TEXT, date DATETIME, to_user_email TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS chat_group (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, group_message TEXT, date DATETIME)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS image_child (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT NOT NULL, image_id INTEGER NOT NULL, email TEXT NOT NULL, original_filename TEXT NOT NULL, FOREIGN KEY (image_id) REFERENCES images (id))");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS comments (id INTEGER PRIMARY KEY AUTOINCREMENT, imageid TEXT, email TEXT, comment TEXT, created_at DATETIME)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS reply_comments (id INTEGER PRIMARY KEY AUTOINCREMENT, comment_id INTEGER, email TEXT, reply TEXT, date DATETIME, FOREIGN KEY (comment_id) REFERENCES comments(id))");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS favorites (id INTEGER PRIMARY KEY AUTOINCREMENT, image_id INTEGER, email TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS following (id INTEGER PRIMARY KEY AUTOINCREMENT, follower_email TEXT NOT NULL, following_email TEXT NOT NULL)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS news (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, description TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, ver TEXT, verlink TEXT, preview TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS status (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT, message TEXT, date DATETIME)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS image_album (id INTEGER PRIMARY KEY AUTOINCREMENT, image_id INTEGER NOT NULL, email TEXT NOT NULL, album_id INTEGER NOT NULL, FOREIGN KEY (image_id) REFERENCES image(id), FOREIGN KEY (album_id) REFERENCES album(id))");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS album (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, album_name TEXT NOT NULL);");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS episode (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, episode_name TEXT);");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS posts (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, content TEXT NOT NULL, email INTEGER NOT NULL, tags TEXT NOT NULL, date DATETIME, category TEXT, FOREIGN KEY (email) REFERENCES users(id))");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS category (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT, category_name TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS starred (id INTEGER PRIMARY KEY AUTOINCREMENT, note_id INTEGER, email TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS novel (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, email TEXT, title TEXT, description TEXT, content TEXT, tags TEXT, date DATETIME, view_count INT DEFAULT 0)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS comments_novel (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, email TEXT, comment TEXT, created_at TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS favorites_novel (id INTEGER PRIMARY KEY AUTOINCREMENT, novel_id INTEGER, email TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS comments_novel (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, email TEXT, comment TEXT, created_at TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS chapter (id INTEGER PRIMARY KEY AUTOINCREMENT, novel_id TEXT, email TEXT, title TEXT, content TEXT, FOREIGN KEY (novel_id) REFERENCES novel(id), FOREIGN KEY (email) REFERENCES users(email));");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS comments_novel (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, email TEXT, comment TEXT, created_at TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS reply_comments_novel (id INTEGER PRIMARY KEY AUTOINCREMENT, comment_id INTEGER, email TEXT, reply TEXT, date DATETIME, FOREIGN KEY (comment_id) REFERENCES comments(id))");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS favorites_videos (id INTEGER PRIMARY KEY AUTOINCREMENT, video_id INTEGER, email TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS videos (id INTEGER PRIMARY KEY AUTOINCREMENT, video TEXT, email TEXT, thumb TEXT, title TEXT, description TEXT, date DATETIME, view_count INT DEFAULT 0)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS comments_minutes (id INTEGER PRIMARY KEY AUTOINCREMENT, minute_id TEXT, email TEXT, comment TEXT, created_at TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS reply_comments_minutes (id INTEGER PRIMARY KEY AUTOINCREMENT, comment_id INTEGER, email TEXT, reply TEXT, date DATETIME, FOREIGN KEY (comment_id) REFERENCES comments(id))");
$stmt->execute();

// Create the "visit" table if it doesn't exist
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS visit (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  visit_count INTEGER,
  visit_date DATE DEFAULT CURRENT_DATE,
  UNIQUE(visit_date)
)");
$stmt->execute();

// Process any visit requests
$stmt = $db->prepare("SELECT id, visit_count FROM visit WHERE visit_date = CURRENT_DATE");
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);

if ($row) {
  // If the record for the current date exists, increment the visit_count
  $visitCount = $row['visit_count'] + 1;
  $stmt = $db->prepare("UPDATE visit SET visit_count = :visitCount WHERE id = :id");
  $stmt->bindValue(':visitCount', $visitCount, SQLITE3_INTEGER);
  $stmt->bindValue(':id', $row['id'], SQLITE3_INTEGER);
  $stmt->execute();
} else {
  // If the record for the current date doesn't exist, insert a new record
  $stmt = $db->prepare("INSERT INTO visit (visit_count) VALUES (:visitCount)");
  $stmt->bindValue(':visitCount', 1, SQLITE3_INTEGER);
  $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <link rel="stylesheet" href="/style.css">
    <?php include('../../bootstrapcss.php'); ?>
    <script>
      // Inline manifest
      const manifest = {
        "name": "ArtCODE",
        "short_name": "ArtCODE",
        "start_url": ".",
        "display": "standalone",
        "background_color": "#ffffff",
        "theme_color": "#000000",
        "icons": [
          {
            "src": "/icon/favicon.png",
            "sizes": "192x192",
            "type": "image/png"
          }
        ]
      };

      const manifestBlob = new Blob([JSON.stringify(manifest)], { type: 'application/json' });
      const manifestURL = URL.createObjectURL(manifestBlob);
      const link = document.createElement('link');
      link.rel = 'manifest';
      link.href = manifestURL;
      document.head.appendChild(link);

      // Inline service worker registration
      if ('serviceWorker' in navigator) {
        const swBlob = new Blob([`
          self.addEventListener('install', function(event) {
            event.waitUntil(
              caches.open('ArtCODE-v0.1.13').then(function(cache) {
                return cache.addAll([
                  '/',
                  '/preview/home/index.php',
                  '/icon/favicon.png'
                ]);
              })
            );
          });

          self.addEventListener('fetch', function(event) {
            event.respondWith(
              caches.match(event.request).then(function(response) {
                return response || fetch(event.request);
              })
            );
          });
        `], { type: 'application/javascript' });

        const swURL = URL.createObjectURL(swBlob);
        navigator.serviceWorker.register(swURL).then(function(registration) {
          console.log('ServiceWorker registration successful with scope: ', registration.scope);
        }).catch(function(error) {
          console.log('ServiceWorker registration failed: ', error);
        });
      }
    </script>
  </head>
  <body>
    <?php include('../header_preview.php'); ?>
    <?php
    // Get the current page number from the query parameter, defaulting to 1 if not set
    $pageNumber = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $display = isset($_GET['display']) ? $_GET['display'] : '';
    ?>
    
    <!-- Conditionally include sections based on the page number and mobile display -->
    <?php if ($pageNumber === 1 && $display !== 'mobile'): ?>
      <div class="d-none d-md-block">
        <?php include('best/index.php'); ?>
      </div>
      <div class="d-none d-md-block">
        <?php include('best_manga/index.php'); ?>
      </div>
    <?php endif; ?>

    <!-- Conditionally include sections based on the page number and desktop display -->
    <?php if ($pageNumber === 1 && $display !== 'desktop'): ?>
      <div class="d-md-none">
        <?php include('best_mobile/index.php'); ?>
      </div>
      <div class="d-md-none">
        <?php include('best_manga_mobile/index.php'); ?>
      </div>
    <?php endif; ?>

    <?php include('tags_group.php'); ?>
    <h3 class="px-2 mt-3 fw-bold">Discover</h3>
    <div class="dropdown">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <?php
        // Get current query parameters, excluding 'by' and 'page'
        $queryParams = array_diff_key($_GET, array('by' => '', 'page' => ''));
        
        // Define sorting options and labels
        $sortOptions = [
          'newest' => 'newest',
          'oldest' => 'oldest',
          'popular' => 'popular',
          'view' => 'most viewed',
          'least' => 'least viewed',
          'liked' => 'liked',
          'order_asc' => 'from A to Z',
          'order_desc' => 'from Z to A',
          'top' => 'top images'
        ];
    
        // Loop through each sort option
        foreach ($sortOptions as $key => $label) {
          // Determine if the current option is active
          $activeClass = (!isset($_GET['by']) && $key === 'newest') || (isset($_GET['by']) && $_GET['by'] === $key) ? 'active' : '';
          
          // Generate the dropdown item with the appropriate active class
          echo '<li><a href="?' . http_build_query(array_merge($queryParams, ['by' => $key, 'page' => isset($_GET['page']) ? $_GET['page'] : '1'])) . '" class="dropdown-item fw-bold ' . $activeClass . '">' . $label . '</a></li>';
        }
        ?>
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
    <?php
    // Check if 'by' is set to 'top' and skip pagination if so
    if (isset($_GET['by']) && $_GET['by'] == 'top') {
      // Skip pagination
    } else {
      $totalPages = ceil($total / $limit);
      $prevPage = $page - 1;
      $nextPage = $page + 1;
      
      // Get current URL without query parameters
      $currentUrl = strtok($_SERVER["REQUEST_URI"], '?');
      
      // Build the query string for current parameters
      $queryParams = array_diff_key($_GET, array('page' => ''));
      ?>
      
      <div class="pagination d-flex gap-1 justify-content-center mt-3">
        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $currentUrl . '?' . http_build_query(array_merge($queryParams, ['page' => 1])); ?>">
            <i class="bi text-stroke bi-chevron-double-left"></i>
          </a>
        <?php endif; ?>
    
        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $currentUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $prevPage])); ?>">
            <i class="bi text-stroke bi-chevron-left"></i>
          </a>
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
              echo '<a class="btn btn-sm btn-primary fw-bold" href="' . $currentUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $i])) . '">' . $i . '</a>';
            }
          }
        ?>
    
        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $currentUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $nextPage])); ?>">
            <i class="bi text-stroke bi-chevron-right"></i>
          </a>
        <?php endif; ?>
    
        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $currentUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $totalPages])); ?>">
            <i class="bi text-stroke bi-chevron-double-right"></i>
          </a>
        <?php endif; ?>
      </div>
      <?php
    }
    ?>
    <div class="mt-5"></div>
    <script>
      function adjustDisplay() {
        const bestElement = document.querySelector('.best');
        const bestMangaElement = document.querySelector('.best_manga');
        const isMobile = window.innerWidth <= 767;
      
        // Extract current display mode from the URL
        const urlParams = new URLSearchParams(window.location.search);
        const currentDisplay = urlParams.get('display');
      
        if (isMobile && currentDisplay !== 'mobile') {
          // Redirect to mobile view if not already on mobile view
          window.location.href = "http://myray.rf.gd/preview/home/?page=1&display=mobile";
        } else if (!isMobile && currentDisplay !== 'desktop') {
          // Redirect to desktop view if not already on desktop view
          window.location.href = "http://myray.rf.gd/preview/home/?page=1&display=desktop";
        }
    
        // Update visibility based on the current display mode
        if (isMobile) {
          if (bestElement) bestElement.style.display = 'none';
          if (bestMangaElement) bestMangaElement.style.display = 'none';
        } else {
          if (bestElement) bestElement.style.display = 'block';
          if (bestMangaElement) bestMangaElement.style.display = 'block';
        }
      }
    
      // Adjust on page load
      window.addEventListener('load', adjustDisplay);
      // Adjust on window resize
      window.addEventListener('resize', adjustDisplay);

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
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>