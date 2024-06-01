<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_GET['title']; ?></title>
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
    </style>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container my-3">
      <?php
      // Check if title and uid parameters are provided
      if (isset($_GET['title']) && isset($_GET['uid'])) {
        $episode_name = $_GET['title'];
        $user_id = $_GET['uid'];
        
        // Fetch JSON data from api_manga_title.php with title and uid parameters
        $json = file_get_contents($web . '/api_manga_title.php?title=' . urlencode($episode_name) . '&uid=' . $user_id);
        $data = json_decode($json, true);

        // Check if the data is an array and not empty
        if (is_array($data) && !empty($data)) {
          $latest_cover = $data['latest_cover'];
          $images = $data['images'];
          $artist_name = $latest_cover['artist'];
          $artistImageCount = $data['artist_image_count'];
          $tags = $data['tags'];
          $tagString = implode(', ', array_keys($tags));
          ?>
          <div class="row">
            <div class="col-md-3">
              <div class="ratio ratio-cover">
                <img class="rounded object-fit-cover" src="<?= $web . '/thumbnails/' . $latest_cover['filename']; ?>" alt="<?= $latest_cover['title']; ?>">
              </div>
            </div>
            <div class="col-md-9">
              <h1 class="mb-4 fw-bold mt-4 mt-md-0"><?php echo $episode_name; ?></h1>
              <div class="mb-2 row">
                <label for="artist" class="col-2 col-form-label text-nowrap fw-medium">Artist</label>
                <div class="col-10">
                 <div class="btn-group">
                  <a href="index.php?artist=<?php echo $artist_name; ?>&uid=<?php echo $user_id; ?>" class="btn bg-secondary-subtle fw-bold"><?php echo $artist_name; ?></a>
                  <a href="#" class="btn bg-body-tertiary fw-bold" disabled><?php echo $artistImageCount; ?></a>
                </div>
              </div>
              <div class="mt-3 row">
                <label for="tags" class="col-2 col-form-label text-nowrap fw-medium">Tags</label>
                <div class="col-10">
                  <?php foreach($tags as $tag => $count): ?>
                    <div class="btn-group mb-2 me-1">
                      <a href="index.php?tag=<?php echo urlencode($tag); ?>" class="btn bg-secondary-subtle fw-bold">
                        <?php echo $tag; ?>
                      </a>
                      <a href="#" class="btn bg-body-tertiary fw-bold">
                        <?php echo $count; ?>
                      </a>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container mb-5">
      <div>
        <div>
          <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 g-1">
            <?php foreach ($images as $image) : ?>
              <div class="col">
                <div class="card border-0 rounded-4">
                  <a href="view.php?title=<?php echo $image['episode_name']; ?>&uid=<?php echo $image['userid']; ?>&id=<?php echo $image['id']; ?>&page=1" class="text-decoration-none">
                    <div class="ratio ratio-cover">
                      <img class="rounded rounded-bottom-0 object-fit-cover lazy-load" data-src="<?= $web . '/thumbnails/' . $image['filename']; ?>" alt="<?= $image['title']; ?>">
                    </div>
                    <h6 class="text-center fw-bold text-white text-decoration-none bg-dark-subtle p-2 rounded rounded-top-0"><?php echo $image['title']; ?></h6>
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php } else { ?>
          <p>No data found.</p>
        <?php }
      } else { ?>
        <p>Missing title or uid parameter.</p>
      <?php } ?>
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
    </script>
  </body>
</html>