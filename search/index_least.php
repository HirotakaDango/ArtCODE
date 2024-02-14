<?php
// Handle the search form submission
if (isset($_GET['q'])) {
  $searchTerm = $_GET['q'];

  // Check if the "year" parameter is set
  $yearFilter = isset($_GET['year']) ? $_GET['year'] : 'all';

  // Prepare the search term by removing leading/trailing spaces and converting to lowercase
  $searchTerm = trim(strtolower($searchTerm));

  // Split the search term by comma to handle multiple tags or titles
  $terms = array_map('trim', explode(',', $searchTerm));

  // Prepare the search query with placeholders for terms
  $query = "SELECT * FROM images WHERE ";

  // Create an array to hold the conditions for partial word matches
  $conditions = array();

  // Add conditions for tags and titles
  foreach ($terms as $index => $term) {
    $conditions[] = "(LOWER(tags) LIKE ? OR LOWER(title) LIKE ?)";
  }

  // Combine all conditions using OR
  $query .= implode(' OR ', $conditions);

  // Add the ORDER BY clause to order by ID in descending order
  $query .= " ORDER BY view_count DESC";

  // Prepare the SQL statement
  $statement = $database->prepare($query);

  // Bind the terms as parameters with wildcard matching for tags and titles
  $paramIndex = 1;
  foreach ($terms as $term) {
    $wildcardTerm = "%$term%";
    $statement->bindValue($paramIndex++, $wildcardTerm, SQLITE3_TEXT);
    $statement->bindValue($paramIndex++, $wildcardTerm, SQLITE3_TEXT);
  }

  // Execute the query
  $result = $statement->execute();

  // Filter the images by year if a year value is provided
  if (!empty($yearFilter) && $yearFilter !== 'all') {
    $filteredImages = array();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
      $imageYear = date('Y', strtotime($row['date']));
      if (strtolower($imageYear) === $yearFilter) {
        $filteredImages[] = $row;
      }
    }
    $resultArray = $filteredImages;
  } else {
    // Retrieve all images
    $resultArray = array();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
      $resultArray[] = $row;
    }
  }

  // Count the number of images found
  $numImages = count($resultArray);
} else {
  // Retrieve all images if no search term is provided
  $query = "SELECT * FROM images ORDER BY view_count ASC";
  $result = $database->query($query);
  $resultArray = array();
  while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $resultArray[] = $row;
  }
  $numImages = count($resultArray);
}
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
                $yearsResult = $database->query($yearsQuery);
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
        <div class="modal-dialog">
          <div class="modal-content">
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
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-1">
        <?php foreach ($resultArray as $row): ?>
          <?php
            $title = $row['title'];
            $filename = $row['filename'];
            $artist = '';
            $stmt = $database->prepare("SELECT id, artist FROM users WHERE email = ?");
            $stmt->bindValue(1, $email, SQLITE3_TEXT);
            $result2 = $stmt->execute();
            if ($user = $result2->fetchArray()) {
              $artist = $user['artist'];
              $id = $user['id'];
            }
          ?>
          <?php include($_SERVER['DOCUMENT_ROOT'] . '/search/card_search.php'); ?>
        <?php endforeach; ?>
      </div>
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