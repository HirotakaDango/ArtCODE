<?php
// Prepare the query to get the user's numpage
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT); // Assuming $email is the email you want to search for
$resultNum = $queryNum->execute();
$user = $resultNum->fetchArray(SQLITE3_ASSOC);

$numpage = isset($user['numpage']) ? $user['numpage'] : 50;

// Determine the number of items per page
$itemsPerPage = empty($numpage) ? PHP_INT_MAX : $numpage;

$yearFilter = isset($_GET['year']) ? $_GET['year'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// Prepare the search term by removing leading/trailing spaces and converting to lowercase
$searchTerm = trim(strtolower($searchTerm));

// Split the search term by comma to handle multiple tags or titles
$terms = array_map('trim', explode(',', $searchTerm));

// Prepare the search query with placeholders for terms
$query = "SELECT * FROM images WHERE 1=1";

// Create an array to hold the conditions for partial word matches
$conditions = array();

// Add conditions for tags and titles
foreach ($terms as $index => $term) {
  if (!empty($term)) {
    $conditions[] = "(LOWER(tags) LIKE ? OR LOWER(title) LIKE ? OR LOWER(characters) LIKE ? OR LOWER(parodies) LIKE ? OR LOWER(`group`) LIKE ?)";
  }
}

if (!empty($conditions)) {
  $query .= " AND (" . implode(' OR ', $conditions) . ")";
}

// Check if q (search term) is empty
if (empty($searchTerm)) {
  // If q is empty, order by view_count DESC
  $query .= " ORDER BY title ASC";
} else {
  // Otherwise, order by id DESC
  $query .= " ORDER BY title ASC";
}

// Prepare the SQL statement
$statement = $db->prepare($query);

// Bind the terms as parameters with wildcard matching for tags and titles
$paramIndex = 1;
foreach ($terms as $term) {
  if (!empty($term)) {
    $wildcardTerm = "%$term%";
    for ($i = 0; $i < 5; $i++) {
      $statement->bindValue($paramIndex++, $wildcardTerm, SQLITE3_TEXT);
    }
  }
}

// Execute the query
$result = $statement->execute();

// Retrieve all images and filter by year if necessary
$resultArray = array();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  $imageYear = date('Y', strtotime($row['date']));
  if ($yearFilter === 'all' || strtolower($imageYear) === $yearFilter) {
    $resultArray[] = $row;
  }
}

// Count the number of images found
$numImages = count($resultArray);

// Calculate total pages
$totalPages = ceil($numImages / $itemsPerPage);

// Slice the array to get the items for the current page
$resultArray = array_slice($resultArray, $offset, $itemsPerPage);
?>

    <div class="container-fluid">
      <div class="mb-2">
        <form action="" method="GET">
          <div class="input-group">
            <input type="text" name="q" class="form-control text-lowercase fw-bold" placeholder="Search tags or title" value="<?php echo isset($searchTerm) ? $searchTerm : ''; ?>" maxlength="30" required onfocus="this.oldValue = this.value;" oninput="updatePlaceholder(this);" onkeyup="debouncedShowSuggestions(this, 'suggestions3')" />
            <button type="submit" class="btn btn-primary"><i class="bi bi-search text-stroke"></i></button>
          </div>
          <div id="suggestions3"></div>
        </form>
      </div>
      <div class="mb-2">
        <form action="" method="GET">
          <div class="input-group">
            <select name="year" class="form-control fw-bold" onchange="this.form.submit()">
              <option value="all" <?php echo ($yearFilter === 'all') ? 'selected' : ''; ?>>All Years</option>
              <?php
              // Fetch distinct years from the "date" column in the images table
              $yearsQuery = "SELECT DISTINCT strftime('%Y', date) AS year FROM images";
              $yearsResult = $db->query($yearsQuery);
              while ($yearRow = $yearsResult->fetchArray(SQLITE3_ASSOC)) {
                $year = $yearRow['year'];
                $selected = ($year == $yearFilter) ? 'selected' : '';
                echo '<option value="' . $year . '"' . $selected . '>' . $year . '</option>';
              }
              ?>
            </select>
            <input type="hidden" name="q" value="<?php echo isset($searchTerm) ? $searchTerm : ''; ?>">
            <div class="input-group-prepend">
              <span class="input-group-text rounded-start-0">
                <i class="bi bi-calendar-fill"></i>
              </span>
            </div>
          </div>
        </form>
      </div>
      <div class="d-flex mb-1">
        <p class="fw-bold text-secondary mb-1 mt-1">search for "<?php echo $searchTerm; ?>"</p>
        <button type="button" class="btn btn-sm btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#infoSearchA">
          <i class="bi bi-info-circle-fill"></i> 
        </button>
      </div>
      <h6 class="badge bg-primary"><?php echo $numImages; ?> images found</h6>
      <div class="modal fade" id="infoSearchA" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header">
              <h1 class="modal-title fs-5 fw-semibold" id="exampleModalLabel"><i class="bi bi-info-circle-fill"></i> Search Tips</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p class="fw-semibold text-center">"You can search multi tags or title using comma to get multiple result!"</p>
              <p class="fw-semibold">example:</p>
              <input class="form-control text-dark fw-bold" placeholder="tags, title (e.g: white, sky)" readonly>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include('image_card_search.php'); ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo $by; ?>&q=<?php echo $searchTerm; ?>&year=<?php echo $yearFilter; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
      <?php endif; ?>

      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo $by; ?>&q=<?php echo $searchTerm; ?>&year=<?php echo $yearFilter; ?>&page=<?php echo $page - 1; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
          echo '<a class="btn btn-sm btn-primary fw-bold" href="?by=' . $by . '&q=' . $searchTerm . '&year=' . $yearFilter . '&page=' . $i . '">' . $i . '</a>';
        }
      }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo $by; ?>&q=<?php echo $searchTerm; ?>&year=<?php echo $yearFilter; ?>&page=<?php echo $page + 1; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo $by; ?>&q=<?php echo $searchTerm; ?>&year=<?php echo $yearFilter; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>
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
              overlay.classList.add("overlay", "rounded-custom");
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
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
          navigator.serviceWorker.register('../sw.js').then(function(registration) {
            console.log('ServiceWorker registration successful with scope: ', registration.scope);
          }, function(err) {
            console.log('ServiceWorker registration failed: ', err);
          });
        });
      }
    </script>