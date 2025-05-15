<?php
require_once('../auth.php');

$email = $_SESSION['email'];

// Connect to the SQLite database using parameterized query
$db = new SQLite3('../database.sqlite');

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

$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT);
$resultNum = $queryNum->execute();
$user = $resultNum->fetchArray(SQLITE3_ASSOC);

$numpage = null;
if ($user && isset($user['numpage'])) {
  $numpageValue = filter_var($user['numpage'], FILTER_VALIDATE_INT);
  if ($numpageValue !== false && $numpageValue > 0) {
    $numpage = $numpageValue;
  }
}

$limit = $numpage ? $numpage : 50;
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <link rel="stylesheet" href="/style.css">
    <?php include('../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <div class="mt-1 mx-2 mb-2 d-flex justify-content-between">
      <?php
        $currentPage = isset($_GET['page']) ? $_GET['page'] : '1';
        $currentBy = isset($_GET['by']) ? $_GET['by'] : 'newest';

        function buildSortLink($sortType) {
          // Preserve all current filters, but always set the 'by' param and reset page to 1
          $queryParams = $_GET;
          $queryParams['by'] = $sortType;
          $queryParams['page'] = isset($_GET['page']) ? $_GET['page'] : '1';
          return '?' . http_build_query($queryParams);
        }

        function isActive($sortType) {
          return (!isset($_GET['by']) && $sortType === 'newest') || (isset($_GET['by']) && $_GET['by'] === $sortType);
        }
      ?>
      <div class="dropdown">
        <button class="btn btn-sm fw-bold rounded-pill btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-images"></i> sort by
        </button>
        <ul class="dropdown-menu">
          <?php
          $sortOptions = [
            'newest' => 'newest',
            'oldest' => 'oldest',
            'popular' => 'popular',
            'view' => 'most viewed',
            'least' => 'least viewed',
            'liked' => 'liked',
            'order_asc' => 'from A to Z',
            'order_desc' => 'from Z to A',
            'daily' => 'daily',
            'week' => 'week',
            'month' => 'month',
            'year' => 'year'
          ];
    
          foreach ($sortOptions as $key => $label):
          ?>
            <li>
              <a href="<?php echo buildSortLink($key); ?>" class="dropdown-item fw-bold <?php echo isActive($key) ? 'active' : ''; ?>">
                <?php echo $label; ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <button type="button" class="btn btn-sm fw-bold rounded-pill btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" data-bs-toggle="modal" data-bs-target="#imageFilterModal">
        <i class="bi bi-filter-left"></i> filter
      </button>
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
        case 'daily':
        include "index_daily.php";
        break;
        case 'week':
        include "index_week.php";
        break;
        case 'month':
        include "index_month.php";
        break;
        case 'year':
        include "index_year.php";
        break;
      }
    }
    else {
      include "index_desc.php";
    }
    
    ?>
    <?php
      $totalPages = ceil($total / $limit);
      $prevPage = $page - 1;
      $nextPage = $page + 1;
    ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'newest'; ?>&parody=<?php echo isset($_GET['parody']) ? $_GET['parody'] : ''; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
      <?php endif; ?>

      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'newest'; ?>&parody=<?php echo isset($_GET['parody']) ? $_GET['parody'] : ''; ?>&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
      <?php endif; ?>

      <?php
        $tag = isset($_GET['parody']) ? 'tag=' . $_GET['parody'] . '&' : '';

        // Calculate the range of page numbers to display
        $startPage = max($page - 2, 1);
        $endPage = min($page + 2, $totalPages);

        // Display page numbers within the range
        for ($i = $startPage; $i <= $endPage; $i++) {
          if ($i === $page) {
            echo '<span class="btn btn-sm btn-primary active fw-bold">' . $i . '</span>';
          } else {
            $by = isset($_GET['by']) ? urlencode($_GET['by']) : 'newest';
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?by=' . $by . '&' . $tag . 'page=' . $i . '">' . $i . '</a>';
          }
        }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'newest'; ?>&parody=<?php echo isset($_GET['parody']) ? $_GET['parody'] : ''; ?>&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'newest'; ?>&parody=<?php echo isset($_GET['parody']) ? $_GET['parody'] : ''; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>

    <!-- Image Filter Modal -->
    <div class="modal fade" id="imageFilterModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0">
          <div class="modal-header border-0">
            <h5 class="modal-title">Filter</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="get" class="modal-body border-0">

            <?php if (isset($_GET['by'])): ?>
              <input type="hidden" name="by" value="<?php echo $_GET['by']; ?>">
            <?php endif; ?>
            <?php if (isset($_GET['page'])): ?>
              <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>">
            <?php endif; ?>
            <div class="form-floating mb-2">
              <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>" type="text" name="q" id="q" placeholder="General Search..." maxlength="500" value="<?php echo htmlspecialchars(isset($_GET['q']) ? $_GET['q'] : '', ENT_QUOTES); ?>">  
              <label for="q" class="fw-medium">General Search...</label>
            </div>
            <div class="form-floating mb-2">
              <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>" type="text" name="character" id="character" placeholder="Character" maxlength="500" value="<?php echo htmlspecialchars(isset($_GET['character']) ? $_GET['character'] : '', ENT_QUOTES); ?>">  
              <label for="character" class="fw-medium">Character</label>
            </div>
            <div class="form-floating mb-2">
              <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>" type="text" name="parody" id="parody" placeholder="Parody" maxlength="500" value="<?php echo htmlspecialchars(isset($_GET['parody']) ? $_GET['parody'] : '', ENT_QUOTES); ?>">  
              <label for="parody" class="fw-medium">Parody</label>
            </div>
            <div class="form-floating mb-2">
              <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>" type="text" name="group" id="group" placeholder="Group" maxlength="500" value="<?php echo htmlspecialchars(isset($_GET['group']) ? $_GET['group'] : '', ENT_QUOTES); ?>">  
              <label for="group" class="fw-medium">Group</label>
            </div>
            <div class="form-floating mb-2">
              <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>" type="text" name="tag" id="tag" placeholder="Tags (comma-separated)" maxlength="500" value="<?php echo htmlspecialchars(isset($_GET['tag']) ? $_GET['tag'] : '', ENT_QUOTES); ?>">  
              <label for="tag" class="fw-medium">Tags (comma-separated)</label>
            </div>
            <div class="form-floating mb-2">
              <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>" type="number" name="uid" id="uid" placeholder="User ID" maxlength="15" value="<?php echo htmlspecialchars(isset($_GET['uid']) ? $_GET['uid'] : '', ENT_QUOTES); ?>">  
              <label for="uid" class="fw-medium">User ID</label>
            </div>
    
            <div class="row g-2 mb-2">
              <div class="col-md-6">
                <select class="form-select border-0 bg-body-tertiary shadow focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?> rounded-3 fw-mediu" style="height: 58px;" name="artwork_type" aria-label="Large select example">
                  <option value="" <?php echo (!isset($_GET['artwork_type']) || $_GET['artwork_type'] === '') ? 'selected' : ''; ?>>Any Artwork Type</option>
                  <option value="illustration" <?php echo (isset($_GET['artwork_type']) && $_GET['artwork_type'] === 'illustration') ? 'selected' : ''; ?>>Illustration</option>
                  <option value="manga" <?php echo (isset($_GET['artwork_type']) && $_GET['artwork_type'] === 'manga') ? 'selected' : ''; ?>>Manga</option>
                </select>
              </div>
              <div class="col-md-6">
                <select class="form-select border-0 bg-body-tertiary shadow focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?> rounded-3 fw-medium" style="height: 58px;" name="type" aria-label="Large select example">
                  <option value="" <?php echo (!isset($_GET['type']) || $_GET['type'] === '') ? 'selected' : ''; ?>>Any Type</option>
                  <option value="safe" <?php echo (isset($_GET['type']) && $_GET['type'] === 'safe') ? 'selected' : ''; ?>>Safe For Works</option>
                  <option value="nsfw" <?php echo (isset($_GET['type']) && $_GET['type'] === 'nsfw') ? 'selected' : ''; ?>>NSFW/R-18</option>
                </select>
              </div>
            </div>
            <div class="row g-2 mb-2">
              <div class="col-md-6">
                <select class="form-select border-0 bg-body-tertiary shadow focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?> rounded-3 fw-mediu" style="height: 58px;" name="category" aria-label="Large select example">
                  <option value="" <?php echo (!isset($_GET['category']) || $_GET['category'] === '') ? 'selected' : ''; ?>>Any Category</option>
                  <option value="artworks/illustrations" <?php echo (isset($_GET['category']) && $_GET['category'] === 'artworks/illustrations') ? 'selected' : ''; ?>>artworks/illustrations</option>
                  <option value="3DCG" <?php echo (isset($_GET['category']) && $_GET['category'] === '3DCG') ? 'selected' : ''; ?>>3DCG</option>
                  <option value="real" <?php echo (isset($_GET['category']) && $_GET['category'] === 'real') ? 'selected' : ''; ?>>real</option>
                  <option value="MMD" <?php echo (isset($_GET['category']) && $_GET['category'] === 'MMD') ? 'selected' : ''; ?>>MMD</option>
                  <option value="multi-work series" <?php echo (isset($_GET['category']) && $_GET['category'] === 'multi-work series') ? 'selected' : ''; ?>>multi-work series</option>
                  <option value="manga series" <?php echo (isset($_GET['category']) && $_GET['category'] === 'manga series') ? 'selected' : ''; ?>>manga series</option>
                  <option value="doujinshi series" <?php echo (isset($_GET['category']) && $_GET['category'] === 'doujinshi series') ? 'selected' : ''; ?>>doujinshi series</option>
                  <option value="oneshot manga" <?php echo (isset($_GET['category']) && $_GET['category'] === 'oneshot manga') ? 'selected' : ''; ?>>oneshot manga</option>
                  <option value="oneshot doujinshi" <?php echo (isset($_GET['category']) && $_GET['category'] === 'oneshot doujinshi') ? 'selected' : ''; ?>>oneshot doujinshi</option>
                  <option value="doujinshi" <?php echo (isset($_GET['category']) && $_GET['category'] === 'doujinshi') ? 'selected' : ''; ?>>doujinshi</option>
                </select>
              </div>
              <div class="col-md-6">
                <select class="form-select border-0 bg-body-tertiary shadow focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?> rounded-3 fw-mediu" style="height: 58px;" name="language" aria-label="Large select example">
                  <option value="" <?php echo (!isset($_GET['language']) || $_GET['language'] === '') ? 'selected' : ''; ?>>Choose Language</option>
                  <option value="English" <?php echo (isset($_GET['language']) && $_GET['language'] === 'English') ? 'selected' : ''; ?>>English</option>
                  <option value="Japanese" <?php echo (isset($_GET['language']) && $_GET['language'] === 'Japanese') ? 'selected' : ''; ?>>Japanese</option>
                  <option value="Chinese" <?php echo (isset($_GET['language']) && $_GET['language'] === 'Chinese') ? 'selected' : ''; ?>>Chinese</option>
                  <option value="Korean" <?php echo (isset($_GET['language']) && $_GET['language'] === 'Korean') ? 'selected' : ''; ?>>Korean</option>
                  <option value="Russian" <?php echo (isset($_GET['language']) && $_GET['language'] === 'Russian') ? 'selected' : ''; ?>>Russian</option>
                  <option value="Indonesian" <?php echo (isset($_GET['language']) && $_GET['language'] === 'Indonesian') ? 'selected' : ''; ?>>Indonesian</option>
                  <option value="Spanish" <?php echo (isset($_GET['language']) && $_GET['language'] === 'Spanish') ? 'selected' : ''; ?>>Spanish</option>
                  <option value="Other" <?php echo (isset($_GET['language']) && $_GET['language'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                  <option value="None" <?php echo (isset($_GET['language']) && $_GET['language'] === 'None') ? 'selected' : ''; ?>>None</option>
                </select>
              </div>
            </div>
    
            <div class="btn-group gap-2 w-100">
              <button type="submit" class="btn btn-primary w-50 rounded fw-medium">Apply Filters</button>
              <a href="./" class="btn btn-outline-secondary w-50 rounded fw-medium">Clear All Filters</a>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- End of Image Filter Modal -->

    <script>
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      let imageContainer = document.getElementById("image-container");

      // Set the default placeholder image
      const defaultPlaceholder = "../icon/bg.png";

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
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>