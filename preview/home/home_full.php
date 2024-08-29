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

    <?php include('option.php'); ?>
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
          window.location.href = "/preview/home/?" + "<?php echo http_build_query(array_merge($_GET, ['display' => 'mobile'])); ?>";
        } else if (!isMobile && currentDisplay !== 'desktop') {
          // Redirect to desktop view if not already on desktop view
          window.location.href = "/preview/home/?" + "<?php echo http_build_query(array_merge($_GET, ['display' => 'desktop'])); ?>";
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