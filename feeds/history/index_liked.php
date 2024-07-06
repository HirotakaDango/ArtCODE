<?php
// Fetch the user's history with image details from the database
$stmt = $db->prepare("
    SELECT images.*
    FROM images
    JOIN favorites ON images.id = favorites.image_id
    WHERE favorites.email = :email
    ORDER BY images.id DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindParam(':email', $email);
$stmt->execute();
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

    <div class="container mt-2">
      <div class="dropdown mt-1 mb-3">
        <button class="btn btn-sm fw-bold rounded-pill btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-images"></i> sort by
        </button>
        <ul class="dropdown-menu">
          <li><a href="?by=newest" class="dropdown-item fw-bold active">newest</a></li>
          <li><a href="?by=oldest" class="dropdown-item fw-bold">oldest</a></li>
          <li><a href="?by=popular" class="dropdown-item fw-bold">popular</a></li>
          <li><a href="?by=view" class="dropdown-item fw-bold">most viewed</a></li>
        </ul> 
      </div>
      <?php if (count($history) > 0) { ?>
        <?php foreach ($history as $item) { ?>
          <a class="list-group-item list-group-item-action" href="<?php echo $item['history']; ?>">
            <div class="card mb-2 border-0 shadow rounded-4">
              <div class="row g-0">
                <div class="col-md-8 d-md-none d-lg-none">
                  <?php
                    // Display the image if the filename exists
                    if (!empty($item['filename'])) {
                      echo '<img src="../../thumbnails/' . $item['filename'] . '" class="object-fit-cover rounded-top-4" style="width: 100%; height: 300px;" alt="'.$item['title'].'">';
                    }
                  ?>
                </div>
                <div class="col-md-10">
                  <div class="card-body">
                    <h5 class="card-title fw-bold"><?php echo $item['title']; ?></h5>
                    <p class="card-text fw-medium"><small class="text-body-secondary">visited: <?php echo date('Y/m/d', strtotime($item['date_history'])); ?></small></p>
                  </div>
                </div>
                <div class="col-md-2 d-none d-md-block d-lg-block">
                  <div class="ratio ratio-1x1">
                  <?php
                    // Display the image if the filename exists
                    if (!empty($item['filename'])) {
                      echo '<img src="../../thumbnails/' . $item['filename'] . '" class="object-fit-cover rounded-end-4 w-100 h-100" alt="'.$item['title'].'">';
                    }
                  ?>
                  </div>
                </div>
              </div>
            </div>
          </a>
        <?php } ?>
      <?php } else { ?>
        <h5 class="position-absolute top-50 start-50 translate-middle fw-bold display-5">No history.</h5>
      <?php } ?>
    </div>
    <div class="mt-5"></div>
    <script>
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      let imageContainer = document.getElementById("image-container");

      // Set the default placeholder image
      const defaultPlaceholder = "../../icon/bg.png";

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