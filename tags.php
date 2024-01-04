<?php
require_once('auth.php');

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Check if a search tag is provided
$searchTags = isset($_GET['tag']) ? explode(',', $_GET['tag']) : [];

// Retrieve the count of images for each tag
$query = "SELECT tags, COUNT(*) as count FROM images";
if (!empty($searchTags)) {
  $tagConditions = [];
  foreach ($searchTags as $searchTag) {
    $searchTag = trim($searchTag);
    $tagConditions[] = "tags LIKE '%" . $searchTag . "%'";
  }
  $query .= " WHERE " . implode(" OR ", $tagConditions);
}
$query .= " GROUP BY tags";

$result = $db->query($query);

// Store the tag counts as an associative array
$tagCounts = [];
while ($row = $result->fetchArray()) {
  $tagList = explode(',', $row['tags']);
  foreach ($tagList as $tag) {
    $trimmedTag = trim($tag);
    if (!isset($tagCounts[$trimmedTag])) {
      $tagCounts[$trimmedTag] = 0;
    }
    $tagCounts[$trimmedTag] += $row['count'];
  }
}

// Sort the tags alphabetically and numerically
ksort($tagCounts, SORT_NATURAL | SORT_FLAG_CASE);

// Group the tags by the first character
$groupedTags = [];
foreach ($tagCounts as $tag => $count) {
  $firstChar = strtoupper(mb_substr($tag, 0, 1));
  $groupedTags[$firstChar][$tag] = $count;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tags</title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <?php include('taguserheader.php'); ?>
    <div class="container-fluid mt-2">
      <div class="container-fluid">
        <div class="row justify-content-center">
          <?php foreach ($groupedTags as $group => $tags): ?>
            <div class="col-4 col-md-2 col-sm-5 px-0">
              <a class="btn btn-outline-dark border-0 fw-medium d-flex flex-column align-items-center" href="#category-<?php echo $group; ?>"><h6 class="fw-medium">Category</h6> <h6 class="fw-bold"><?php echo $group; ?></h6></a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php foreach ($groupedTags as $group => $tags): ?>
        <div id="category-<?php echo $group; ?>" class="category-section pt-5">
          <h5 class="fw-bold text-start">Category <?php echo $group; ?></h5>
          <div class="row">
            <?php foreach ($tags as $tag => $count): ?>
              <?php
                // Check if the tag has any associated images
                $stmt = $db->prepare("SELECT * FROM images WHERE tags LIKE ? ORDER BY id DESC LIMIT 1");
                $stmt->bindValue(1, '%' . $tag . '%');
                $imageResult = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
                if ($imageResult):
              ?>
              <div class="col-md-2 col-sm-5 px-0">
                <a href="tagged_images.php?tag=<?php echo str_replace('%27', "'", urlencode($tag)); ?>" class="m-1 d-block text-decoration-none">
                  <div class="card rounded-4 border-0 shadow text-bg-dark ratio ratio-1x1">
                    <img data-src="thumbnails/<?php echo $imageResult['filename']; ?>" alt="<?php echo $imageResult['title']; ?>" class="lazy-load card-img object-fit-cover rounded-4 w-100 h-100">
                    <div class="card-img-overlay d-flex align-items-center justify-content-center">
                      <span class="fw-bold text-center" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);">
                        <?php echo $tag . ' (' . $count . ')'; ?>
                      </span>
                    </div>
                  </div>
                </a>
              </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="mt-5"></div>
    <button class="z-3 btn btn-primary btn-md rounded-pill fw-bold position-fixed bottom-0 end-0 m-2" id="scrollToTopBtn" onclick="scrollToTop()"><i class="bi bi-chevron-up" style="-webkit-text-stroke: 3px;"></i></button>
    <script>
      // Show or hide the button based on scroll position
      window.onscroll = function() {
        showScrollButton();
      };

      // Function to show or hide the button based on scroll position
      function showScrollButton() {
        var scrollButton = document.getElementById("scrollToTopBtn");
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
          scrollButton.style.display = "block";
        } else {
          scrollButton.style.display = "none";
        }
      }

      // Function to scroll to the top of the page
      function scrollToTop() {
        document.body.scrollTop = 0; // For Safari
        document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE, and Opera
      }
    </script>
    <script>
      function updatePlaceholder(input) {
        input.setAttribute('placeholder', input.value.trim() !== '' ? input.value.trim() : 'Search by tags or title');
      }
    </script>
    <script>
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      let imageContainer = document.getElementById("image-container");

      // Set the default placeholder image
      const defaultPlaceholder = "icon/bg.png";

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
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
