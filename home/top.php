<?php
// Get the images for the current page
$stmt = $db->prepare("SELECT images.*, COUNT(favorites.id) AS favorite_count, SUM(images.view_count) AS total_view_count, users.id AS userid, users.artist, users.pic
                     FROM images 
                     LEFT JOIN favorites ON images.id = favorites.image_id 
                     LEFT JOIN users ON images.email = users.email
                     GROUP BY images.id 
                     ORDER BY (favorite_count + total_view_count) DESC 
                     LIMIT 24");
$result = $stmt->execute();
?>

    <div class="container-fluid mt-2">
      <h5 class="fw-bold mb-2">Top images</h5>
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xxl-4 g-1">
        <?php while ($image = $result->fetchArray()): ?>
          <div class="col">
            <div class="card border-0 bg-body-tertiary shadow h-100 rounded-4">
              <a class="text-decoration-none link-body-emphasis" href="../image.php?artworkid=<?php echo $image['id']; ?>">
                <div class="row g-0">
                  <div class="col-4">
                    <div class="ratio ratio-1x1 rounded-4">
                      <img class="object-fit-cover lazy-load h-100 w-100 rounded-start-4" data-src="../thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>">
                    </div>
                  </div>
                  <div class="col-8">
                    <div class="card-body d-flex align-items-center justify-content-start h-100">
                      <div>
                        <h6 class="card-title fw-bold"><?php echo (!is_null($image['title']) && mb_strlen($image['title'], 'UTF-8') > 20) ? mb_substr($image['title'], 0, 20, 'UTF-8') . '...' : $image['title']; ?></h6>
                        <h6 class="small fw-medium">image by <?php echo (!is_null($image['artist']) && mb_strlen($image['artist'], 'UTF-8') > 20) ? mb_substr($image['title'], 0, 20, 'UTF-8') . '...' : $image['artist']; ?></h6>
                      </div>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          </div>
        <?php endwhile; ?>
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