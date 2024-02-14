<?php
// Initialize variables
$websiteUrl = '';
$folderPath = '';
$thumbPath = '';
$numPage = '';

// SQLite database connection
$db = new SQLite3('your_database.sqlite'); // Replace with your actual database file

// Create settings table if it doesn't exist
$createTableQuery = "CREATE TABLE IF NOT EXISTS settings (
  id INTEGER PRIMARY KEY,
  website_url TEXT,
  folder_path TEXT,
  thumb_path TEXT,
  number_page TEXT
)";
$db->exec($createTableQuery);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $websiteUrl = $_POST['website_url'];
  $folderPath = $_POST['folder_path'];
  $thumbPath = $_POST['thumb_path'];
  $numPage = $_POST['number_page'];

  // Update or insert URL and path in the database
  $stmt = $db->prepare("INSERT OR REPLACE INTO settings (id, website_url, folder_path, thumb_path, number_page) VALUES (1, :website_url, :folder_path, :thumb_path, :number_page)");
  $stmt->bindValue(':website_url', $websiteUrl);
  $stmt->bindValue(':folder_path', $folderPath);
  $stmt->bindValue(':thumb_path', $thumbPath);
  $stmt->bindValue(':number_page', $numPage);
  $stmt->execute();

  // Redirect to prevent form resubmission
  header('Location: ' . $_SERVER['REQUEST_URI']);   
  exit();
}

// Fetch website URL, folder path, and thumb path from the database
$selectQuery = "SELECT website_url, folder_path, thumb_path, number_page FROM settings WHERE id = 1"; // Corrected this line
$result = $db->querySingle($selectQuery, true);

if ($result) {
  $websiteUrl = $result['website_url'];
  $folderPath = $result['folder_path'];
  $thumbPath = $result['thumb_path'];
  $numPage = $result['number_page'];
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE - Preview</title>
    <link rel="icon" type="image/png" href="<?php echo $websiteUrl; ?>/icon/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  </head>
  <body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary shadow">
      <div class="container-fluid gap-2 justify-content-end">
        <a class="navbar-brand me-auto fw-bold" href="index.php">ArtCODE</a>
        <button id="themeToggle" class="btn btn-primary fw-bold">
          <i id="themeIcon" class="bi"></i> toggle theme
        </button>
      </div>
    </nav>
    <button type="button" class="position-fixed z-3 bottom-0 end-0 m-2 btn btn-dark rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#settingsModal">
      settings
    </button>
    <?php
      $limit = !empty($numPage) ? $numPage : 10;
      $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
      $offset = ($page - 1) * $limit;

      $sourceApiUrl = $websiteUrl . '/api.php'; // Construct API URL based on user input

      try {
        $json = @file_get_contents($sourceApiUrl);
        if ($json === false) {
          throw new Exception("<h5 class='text-center'>Error fetching data from API</h5>");
        }

        $data = json_decode($json, true);

        if (!is_array($data) || empty($data)) {
          throw new Exception("<h5 class='text-center'>No data found</h5>");
        }

        $images = $data['images'];
        $imageChildData = $data['image_child'];

        $totalImages = count($images);
        $totalPages = ceil($totalImages / $limit); // Calculate total number of pages

        // Display images within the specified limit and offset
        $displayImages = array_slice($images, $offset, $limit);
      } catch (Exception $e) {
        echo "<div class='d-flex justify-content-center align-items-center vh-100'><h5 class='text-center mt-3 fw-bold'>Error or nothing found: </h5></div>" . $e->getMessage();
      }
    ?>
    <?php include('images.php'); ?>
    <?php include('settings.php'); ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if (isset($page) && isset($totalPages)): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
      <?php endif; ?>

      <?php if (isset($page) && $page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $page - 1; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
              echo '<a class="btn btn-sm btn-primary fw-bold" href="?page=' . $i . '">' . $i . '</a>';
            }
          }
        }
      ?>

      <?php if (isset($page) && isset($totalPages) && $page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $page + 1; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
      <?php endif; ?>

      <?php if (isset($page) && isset($totalPages)): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>
    <div class="my-5 text-center"><a class="text-decoration-none fw-medium" href="table.php"><i class="bi bi-table"></i> show all data</a></div>
    <style>
      .text-stroke {
        -webkit-text-stroke: 1px;
      }

      /* For Webkit-based browsers */
      ::-webkit-scrollbar {
        width: 0;
        height: 0;
        border-radius: 10px;
      }

      ::-webkit-scrollbar-track {
        border-radius: 0;
      }

      ::-webkit-scrollbar-thumb {
        border-radius: 0;
      }

      .text-shadow {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);
      }
    </style>
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
      const defaultPlaceholder = "<?php echo $websiteUrl; ?>/icon/bg.png";

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
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js" integrity="sha384-Rx+T1VzGupg4BHQYs2gCW9It+akI2MM/mndMCy36UVfodzcJcF0GGLxZIzObiEfa" crossorigin="anonymous"></script>
  </body>
</html>