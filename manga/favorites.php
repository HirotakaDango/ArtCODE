<?php
session_start();

$db = new PDO('sqlite:forum/database.db');

// Get user_id from URL parameter
$user_id = isset($_GET['uid']) ? intval($_GET['uid']) : null;

// Validate user_id or fallback to session
if (!$user_id && isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];
}

// Fetch username associated with user_id
$user_stmt = $db->prepare('SELECT username FROM users WHERE id = :user_id');
$user_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$user_stmt->execute();
$user_result = $user_stmt->fetch(PDO::FETCH_ASSOC);
$username = $user_result ? $user_result['username'] : 'Unknown User';

// Prepare SQL query to fetch favorites
$stmt = $db->prepare('SELECT * FROM favorites WHERE user_id = :user_id ORDER BY id DESC');
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pagination setup
$itemsPerPage = 24; // Number of items per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $itemsPerPage;

// Prepare SQL query to fetch favorites with pagination
$stmt = $db->prepare('SELECT * FROM favorites WHERE user_id = :user_id ORDER BY id DESC LIMIT :limit OFFSET :offset');
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total number of favorites for pagination
$countStmt = $db->prepare('SELECT COUNT(*) AS total FROM favorites WHERE user_id = :user_id');
$countStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$countStmt->execute();
$totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalCount / $itemsPerPage);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $username; ?>'s Favorites</title>
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
      <h6 class="fw-bold mb-3"><?php echo $username; ?>'s Favorites (<?php echo count($favorites); ?>)</h6>
      <?php if ($favorites): ?>
        <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 g-1">
          <?php foreach ($favorites as $favorite): ?>
            <div class="col">
              <div class="card border-0 rounded-4">
                <a href="<?php echo $favorite['link']; ?>" class="text-decoration-none">
                  <div class="ratio ratio-cover">
                    <?php if (!empty($favorite['image_cover'])): ?>
                      <img data-src="<?php echo $favorite['image_cover']; ?>" class="rounded rounded-bottom-0 object-fit-cover lazy-load" alt="Cover Image">
                    <?php endif; ?>
                  </div>
                  <h6 class="text-center fw-bold text-white text-decoration-none bg-dark-subtle p-2 rounded rounded-top-0" id="episode-name_img<?= $favorite['id']; ?>"><?php echo $favorite['episode_name']; ?></h6>
                </a>
              </div>
            </div>
            <style>
              #episode-name_img<?php echo $favorite['id']; ?> {
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
                max-width: 24ch; /* Limit to 20 characters */
                transition: max-width 0.3s ease;
              }
            
              #episode-name_img<?php echo $favorite['id']; ?>.expand {
                max-width: none; /* Expand to full width */
                white-space: normal;
              }
            </style>
            <script>
              document.addEventListener("DOMContentLoaded", function() {
                const episodeName = document.getElementById('episode-name_img<?php echo $favorite['id']; ?>');
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
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p>No favorites found for this user.</p>
      <?php endif; ?>
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