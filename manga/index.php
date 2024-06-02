<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
      <?php
      if (isset($_GET['search'])) {
        echo 'Search "' . $_GET['search'] . '"';
      } elseif (isset($_GET['artist'])) {
        echo 'Artist "' . $_GET['artist'] . '"';
      } elseif (isset($_GET['tag'])) {
        echo 'Tag "' . $_GET['tag'] . '"';
      } else {
        echo 'ArtCODE - Manga';
      }
      ?>
    </title>
    <link rel="icon" type="image/png" href="<?php echo $web; ?>/icon/favicon.png">
    <?php include('bootstrap.php'); ?>
    <?php include('connection.php'); ?>
    <style>
      .ratio-cover {
        position: relative;
        width: 100%;
        padding-bottom: 130%;
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
        'tag' => $_GET['tag'] ?? null
      ]));
      if ($queryString) {
        $apiUrl .= '?' . $queryString;
      }
      
      // Fetch JSON data from api_manga.php
      $json = file_get_contents($apiUrl);
      $images = json_decode($json, true);
      $totalImages = count($images);
      $limit = 20;
      $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
      $offset = ($page - 1) * $limit;
      $totalPages = ceil($totalImages / $limit);
      $displayImages = array_slice($images, $offset, $limit);
      ?>
      
      <h6 class="fw-bold mb-4">
        <?php
          if (isset($_GET['search'])) {
            echo 'Search: "' . $_GET['search'] . '" (' . $totalImages . ')';
          } elseif (isset($_GET['artist'])) {
            echo 'Artist: "' . $_GET['artist'] . '" (' . $totalImages . ')';
          } elseif (isset($_GET['tag'])) {
            echo 'Tag: "' . $_GET['tag'] . '" (' . $totalImages . ')';
          } else {
            echo 'All (' . $totalImages . ')';
          }
        ?>
      </h6>
      
      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 g-1">
        <?php
        // Check if the images data is an array and not empty
        if (is_array($displayImages) && !empty($displayImages)) {
          foreach ($displayImages as $image) : ?>
            <div class="col">
              <div class="card border-0 rounded-4">
                <a href="title.php?title=<?php echo $image['episode_name']; ?>&uid=<?php echo $image['userid']; ?>" class="text-decoration-none">
                  <div class="ratio ratio-cover">
                    <img class="rounded rounded-bottom-0 object-fit-cover lazy-load" data-src="<?= $web . '/thumbnails/' . $image['filename']; ?>" alt="<?= $image['title']; ?>">
                  </div>
                  <h6 class="text-center fw-bold text-white text-decoration-none bg-dark-subtle p-2 rounded rounded-top-0">「<?php echo $image['artist']; ?>」<?php echo $image['episode_name']; ?></h6>
                </a>
              </div>
            </div>
          <?php endforeach;
        } else { ?>
          <p>No images found.</p>
        <?php } ?>
      </div>
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
    </div>
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
  </body>
</html>
