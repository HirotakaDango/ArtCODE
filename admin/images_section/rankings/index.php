<?php
// admin/images_section/rankings/index.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();

// Retrieve the email from the session
$email = $_SESSION['admin']['email'];

// Connect to the SQLite database
$db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/database.sqlite');

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $existing_fav = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = $image_id");

  if ($existing_fav == 0) {
    $db->exec("INSERT INTO favorites (email, image_id) VALUES ('$email', $image_id)");
  }
  
  // Redirect to the same page with the appropriate sorting parameter
  $page = isset($_GET['page']) ? $_GET['page'] : 1; // check if page is set, default to 1
  $by = isset($_GET['by']) ? $_GET['by'] : 'day'; // check if by is set, default to day
  header('Location: ?by=' . $by . '&page=' . $page);
  exit(); 
  
} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $db->exec("DELETE FROM favorites WHERE email = '$email' AND image_id = $image_id");

  // Redirect to the same page with the appropriate sorting parameter
  $page = isset($_GET['page']) ? $_GET['page'] : 1; // check if page is set, default to 1
  $by = isset($_GET['by']) ? $_GET['by'] : 'day'; // check if by is set, default to day
  header('Location: ?by=' . $by . '&page=' . $page);
  exit();
}

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
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <title>Rankings</title>
    <?php include('../../../bootstrapcss.php'); ?>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
  </head>
  <body>
    <div class="container-fluid px-0">
      <div class="row g-0">
        <div class="col-auto">
          <?php include('../../admin_header.php'); ?>
        </div>
        <div class="col overflow-auto vh-100">
          <?php include('../../navbar.php'); ?>
          <div>
            <div class="dropdown mt-2">
              <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-images"></i> sort by
              </button>
              <ul class="dropdown-menu">
                <li><a href="?by=day&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'day') echo 'active'; ?>">this day</a></li>
                <li><a href="?by=week&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'week') echo 'active'; ?>">this week</a></li>
                <li><a href="?by=month&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'month') echo 'active'; ?>">this month</a></li>
                <li><a href="?by=year&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'year') echo 'active'; ?>">this year</a></li>
                <li><a href="?by=alltime&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'alltime') echo 'active'; ?>">all time</a></li>
              </ul> 
            </div> 
            <?php 
            if(isset($_GET['by'])){
              $sort = $_GET['by'];
            
              switch ($sort) {
                case 'day':
                  include "daily.php";
                  break;
                case 'week':
                  include "week.php";
                  break;
                case 'month':
                  include "month.php";
                  break;
                case 'year':
                  include "year.php";
                  break;
                case 'alltime':
                  include "alltime.php";
                  break;
              }
            }
            else {
              include "daily.php";
            }
            ?>
            <?php
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
            <div class="mt-5"></div>
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
    <?php include('../../../bootstrapjs.php'); ?>
  </body>
</html>