<?php
include 'connect.php';

// Retrieve and sanitize parameters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$search = isset($_GET['search']) ? $_GET['search'] : ''; // Updated to handle search as string
$display = isset($_GET['display']) ? urlencode($_GET['display']) : 'all_images';
$sortBy = isset($_GET['sortby']) ? urlencode($_GET['sortby']) : 'newest';
$artworkType = isset($_GET['artwork_type']) ? urlencode($_GET['artwork_type']) : '';
$type = isset($_GET['type']) ? urlencode($_GET['type']) : '';
$character = isset($_GET['character']) ? urlencode($_GET['character']) : '';
$parody = isset($_GET['parody']) ? urlencode($_GET['parody']) : '';
$tag = isset($_GET['tag']) ? urlencode($_GET['tag']) : '';
$group = isset($_GET['group']) ? urlencode($_GET['group']) : '';
$uid = isset($_GET['uid']) ? intval($_GET['uid']) : 0;
$rankings = isset($_GET['rankings']) ? urlencode($_GET['rankings']) : ''; // Include rankings in the API URL

// Construct API URL with encoded parameters
$apiUrl = $baseUrl . '/api.php';

$apiUrl .= "?display=$display&sortby=$sortBy";
if ($uid > 0) $apiUrl .= "&uid=$uid";
if ($artworkType) $apiUrl .= "&artwork_type=$artworkType";
if ($type) $apiUrl .= "&type=$type";
if ($character) $apiUrl .= "&character=$character";
if ($parody) $apiUrl .= "&parody=$parody";
if ($tag) $apiUrl .= "&tag=$tag";
if ($group) $apiUrl .= "&group=$group";
if ($rankings) $apiUrl .= "&rankings=$rankings";
if ($search) $apiUrl .= "&search=" . urlencode($search); // Include search in the API URL

// Fetch and decode JSON data
$jsonData = @file_get_contents($apiUrl);
$data = json_decode($jsonData, true);

if ($jsonData === false || $data === null) {
  // Handle the error
  die('Error fetching or decoding JSON data.');
}

// Handle data based on display type
if ($display === 'info') {
  // Assuming the 'info' display needs to fetch specific details, adjust as necessary
  $artworkData = $data;
} else {
  if (!isset($data['images']) || !is_array($data['images'])) {
    die('Invalid data format received from API.');
  }

  $allImages = $data['images'];

  // Handle pagination
  $totalImages = count($allImages);
  $imagesPerPage = 18;
  $totalPages = ceil($totalImages / $imagesPerPage);
  $startIndex = ($page - 1) * $imagesPerPage;
  $currentPageImages = array_slice($allImages, $startIndex, $imagesPerPage);
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArtCODE API</title>
    <?php include('bootstrap.php'); ?>
    <style>
      .text-stroke {
        -webkit-text-stroke: 1px;
      }
    </style>
  </head>
  <body>
    <?php include('navbar.php'); ?>
    <div class="my-2">
      <button type="button" class="btn bg-body-tertiary ms-2 border-0 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#settingsModal">
        <i class="bi bi-filter-left"></i> filter
      </button>
    </div>
    <div class="w-100 px-1 mt-1">
      <!-- Image Grid -->
      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 g-1">
        <?php if (isset($currentPageImages) && is_array($currentPageImages)): ?>
          <?php foreach ($currentPageImages as $image): ?>
            <div class="col">
              <div class="card border-0 rounded-4">
                <a href="view.php?artworkid=<?php echo $image['id']; ?>&display=info&back=<?php echo urlencode('http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>">
                  <div class="ratio ratio-1x1">
                    <img data-src="<?php echo $baseUrl; ?>/thumbnails/<?php echo $image['filename']; ?>" class="rounded object-fit-cover lazy-load" alt="<?php echo $image['title']; ?>">
                  </div>
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No images found.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Pagination -->
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <?php
          // Calculate previous page URLs
          $prevPage = $page - 1;
          $prevPageUrl = http_build_query(array_merge($_GET, ['page' => 1]));
          $prevUrl = "?$prevPageUrl";
          $prevPageUrl = http_build_query(array_merge($_GET, ['page' => $prevPage]));
          $prevPageUrl = "?$prevPageUrl";
        ?>
        <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $prevUrl; ?>"><i class="bi text-stroke bi-chevron-double-left"></i></a>
        <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $prevPageUrl; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
      <?php endif; ?>
    
      <?php
        // Calculate the range of page numbers to display
        $startPage = max($page - 2, 1);
        $endPage = min($page + 2, $totalPages);
    
        // Display page numbers within the range
        for ($i = $startPage; $i <= $endPage; $i++) {
          $queryParams = array_merge($_GET, ['page' => $i]);
          $pageUrl = http_build_query($queryParams);
          $url = "?$pageUrl";
          if ($i === $page) {
            echo '<span class="btn btn-sm btn-primary active fw-bold">' . $i . '</span>';
          } else {
            echo '<a class="btn btn-sm btn-primary fw-bold" href="' . $url . '">' . $i . '</a>';
          }
        }
      ?>
    
      <?php if ($page < $totalPages): ?>
        <?php
          // Calculate next and last page URLs
          $nextPage = $page + 1;
          $nextPageUrl = http_build_query(array_merge($_GET, ['page' => $nextPage]));
          $nextPageUrl = "?$nextPageUrl";
          $lastPageUrl = http_build_query(array_merge($_GET, ['page' => $totalPages]));
          $lastPageUrl = "?$lastPageUrl";
        ?>
        <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $nextPageUrl; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
        <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $lastPageUrl; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>

    <!-- Settings Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Filter</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="" method="get" class="modal-body border-0">
            <input type="hidden" name="display" class="form-control p-3 fw-medium bg-body-tertiary border-0" placeholder="Display" value="<?php echo $display; ?>">
            <div class="mb-2">
              <input type="text" name="search" class="form-control p-3 fw-medium bg-body-tertiary border-0" placeholder="Search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="input-group gap-2 mb-2">
              <select name="sortby" class="form-select p-3 rounded fw-medium bg-body-tertiary border-0">
                <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest</option>
                <option value="oldest" <?php echo $sortBy === 'oldest' ? 'selected' : ''; ?>>Oldest</option>
                <option value="popular" <?php echo $sortBy === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                <option value="view" <?php echo $sortBy === 'view' ? 'selected' : ''; ?>>Most Viewed</option>
                <option value="least" <?php echo $sortBy === 'least' ? 'selected' : ''; ?>>Least Viewed</option>
              </select>
              <select name="rankings" class="form-select p-3 rounded fw-medium bg-body-tertiary border-0">
                <option value="" <?php echo empty($rankings) ? 'selected' : ''; ?>>No Ranking</option>
                <option value="daily" <?php echo $rankings === 'daily' ? 'selected' : ''; ?>>Daily</option>
                <option value="weekly" <?php echo $rankings === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                <option value="monthly" <?php echo $rankings === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                <option value="yearly" <?php echo $rankings === 'yearly' ? 'selected' : ''; ?>>Yearly</option>
              </select>
            </div>
            <div class="input-group gap-2 mb-2">
              <select name="artwork_type" class="form-select p-3 rounded fw-medium bg-body-tertiary border-0">
                <option value="" <?php echo empty($rankings) ? 'selected' : ''; ?>>No Artwork Type</option>
                <option value="illustration" <?php echo $artworkType === 'illustration' ? 'selected' : ''; ?>>Illustration</option>
                <option value="manga" <?php echo $artworkType === 'manga' ? 'selected' : ''; ?>>Manga</option>
              </select>
              <select name="type" class="form-select p-3 rounded fw-medium bg-body-tertiary border-0">
                <option value="" <?php echo empty($rankings) ? 'selected' : ''; ?>>No Type</option>
                <option value="safe" <?php echo $type === 'safe' ? 'selected' : ''; ?>>Safe</option>
                <option value="nsfw" <?php echo $type === 'nsfw' ? 'selected' : ''; ?>>NSFW</option>
              </select>
            </div>
            <div class="mb-2">
              <input type="text" name="tag" class="form-control p-3 fw-medium bg-body-tertiary border-0" placeholder="Tag" value="<?php echo urldecode($tag); ?>">
            </div>
            <div class="mb-2">
              <input type="text" name="character" class="form-control p-3 fw-medium bg-body-tertiary border-0" placeholder="Character" value="<?php echo $character; ?>">
            </div>
            <div class="mb-2">
              <input type="text" name="parody" class="form-control p-3 fw-medium bg-body-tertiary border-0" placeholder="Parody" value="<?php echo $parody; ?>">
            </div>
            <div class="mb-2">
              <input type="number" name="uid" class="form-control p-3 fw-medium bg-body-tertiary border-0" placeholder="User ID" value="<?php echo $uid; ?>">
            </div>
            <div class="mb-2">
              <button type="submit" class="btn btn-primary fw-medium w-100">Apply Filters</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="mt-5"></div>
    <script>
      // Get the theme toggle button, icon element, and html element
      const themeToggle = document.getElementById('themeToggle');
      const themeIcon = document.getElementById('themeIcon');
      const htmlElement = document.documentElement;

      // Check if the user's preference is stored in localStorage
      const savedTheme = localStorage.getItem('theme');
      if (savedTheme) {
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        updateThemeIcon(savedTheme);
      }

      // Add an event listener to the theme toggle button
      themeToggle.addEventListener('click', () => {
        // Toggle the theme
        const currentTheme = htmlElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
        // Apply the new theme
        htmlElement.setAttribute('data-bs-theme', newTheme);
        updateThemeIcon(newTheme);

        // Store the user's preference in localStorage
        localStorage.setItem('theme', newTheme);
      });

      // Function to update the theme icon
      function updateThemeIcon(theme) {
        if (theme === 'dark') {
          themeIcon.classList.remove('bi-moon-fill');
          themeIcon.classList.add('bi-sun-fill');
        } else {
          themeIcon.classList.remove('bi-sun-fill');
          themeIcon.classList.add('bi-moon-fill');
        }
      }
    </script>
    <script>
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      let imageContainer = document.getElementById("image-container");

      // Set the default placeholder image
      const defaultPlaceholder = "<?php echo $baseUrl; ?>/icon/bg.png";

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