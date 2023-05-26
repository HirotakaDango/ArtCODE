<?php
// Set the limit of images per page
$limit = 100;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Get the total number of images
$total = $db->querySingle("SELECT COUNT(*) FROM images");

// Get the images for the current page
$stmt = $db->prepare("SELECT * FROM images ORDER BY id ASC LIMIT ?, ?");
$stmt->bindValue(1, $offset, SQLITE3_INTEGER);
$stmt->bindValue(2, $limit, SQLITE3_INTEGER);
$result = $stmt->execute();
?>

    <div class="dropdown">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item fw-bold" href="?by=newest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>">Newest</a></li>
        <li><a class="dropdown-item fw-bold active" href="?by=oldest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>">Oldest</a></li>
      </ul>  
    </div> 
    <div class="images mb-2 mt-2">
      <?php while ($image = $result->fetchArray()): ?>
        <div class="image-container">
          <div class="position-relative">
            <a class="shadow" href="image.php?artworkid=<?php echo $image['id']; ?>">
              <img class="lazy-load" data-src="thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>">
            </a> 
            <div class="position-absolute top-0 start-0">
              <div class="dropdown">
                <button class="btn btn-sm btn-secondary ms-1 mt-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <?php
                    $is_favorited = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = {$image['id']}");
                    if ($is_favorited) {
                  ?>
                    <form method="POST">
                      <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                      <li><button type="submit" class="dropdown-item fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i> <small>unfavorite</small></button></li>
                    </form>
                  <?php } else { ?>
                    <form method="POST">
                      <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                      <li><button type="submit" class="dropdown-item fw-bold" name="favorite"><i class="bi bi-heart"></i> <small>favorite</small></button></li>
                    </form>
                  <?php } ?>
                  <li><button class="dropdown-item fw-bold" onclick="shareImage(<?php echo $image['id']; ?>)"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
                  <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $image['id']; ?>"><i class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
                </ul>
                <!-- Modal -->
                <div class="modal fade" id="infoImage_<?php echo $image['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content rounded-3 shadow">
                      <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold"><?php echo $image['title']?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body p-4 text-center">
                        <img class="rounded object-fit-cover mb-3 shadow" src="thumbnails/<?php echo $image['filename']; ?>" style="width: 100%; height: 100%;">
                        <p class="text-start fw-semibold"><?php echo $image['imgdesc']?></p>
                        <div class="card container">
                          <p class="text-center fw-semibold mt-2">Image Information</p>
                          <p class="text-start fw-semibold">Image ID: "<?php echo $image['id']?>"</p>
                          <?php
                            // Get image size in megabytes
                            $image_size = round(filesize('images/' . $image['filename']) / (1024 * 1024), 2);

                            // Get image dimensions
                            list($width, $height) = getimagesize('images/' . $image['filename']);

                            // Display image information
                            echo "<p class='text-start fw-semibold'>Image data size: " . $image_size . " MB</p>";
                            echo "<p class='text-start fw-semibold'>Image dimensions: " . $width . "x" . $height . "</p>";
                            echo "<p class='text-start fw-semibold'><a class='text-decoration-none' href='images/" . $image['filename'] . "'>View original image</a></p>";
                          ?>
                        </div>
                        <div class="btn-group w-100 mt-2">
                          <a class="btn btn-primary fw-bold rounded-start-5" href="image.php?artworkid=<?php echo $image['id']; ?>"><i class="bi bi-eye-fill"></i> view</a>
                          <a class="btn btn-primary fw-bold" href="images/<?php echo $image['filename']; ?>" download><i class="bi bi-download"></i> download</a>
                          <button class="btn btn-primary fw-bold rounded-end-5" onclick="shareImage(<?php echo $image['id']; ?>)"><i class="bi bi-share-fill"></i> share</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
    <div class="mt-5 mb-2 d-flex justify-content-center btn-toolbar container">
      <?php
        $totalPages = ceil($total / $limit);
        $prevPage = $page - 1;
        $nextPage = $page + 1;

        if ($page > 1) {
          echo '<a type="button" class="btn rounded-pill fw-bold btn-secondary opacity-50 me-1" href="?by=oldest&page=' . $prevPage . '"><i class="bi bi-arrow-left-circle-fill"></i> prev</a>';
        }
        if ($page < $totalPages) {
          echo '<a type="button" class="btn rounded-pill fw-bold btn-secondary opacity-50 ms-1" href="?by=oldest&page=' . $nextPage . '">next <i class="bi bi-arrow-right-circle-fill"></i></a>';
        }
      ?>
    </div>
    <style>
      .images {
        display: grid;
        grid-template-columns: repeat(2, 1fr); /* Two columns in mobile view */
        grid-gap: 3px;
        justify-content: center;
        margin-right: 3px;
        margin-left: 3px;
      }

      @media (min-width: 768px) {
        /* For desktop view, change the grid layout */
        .images {
          grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
      }

      .images a {
        display: block;
        border-radius: 4px;
        overflow: hidden;
      }

      .images img {
        width: 100%;
        height: auto;
        object-fit: cover;
        height: 200px;
        transition: transform 0.5s ease-in-out;
      }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
          let lazyloadImages;
          if("IntersectionObserver" in window) {
            lazyloadImages = document.querySelectorAll(".lazy-load");
            let imageObserver = new IntersectionObserver(function(entries, observer) {
              entries.forEach(function(entry) {
                if(entry.isIntersecting) {
                  let image = entry.target;
                  image.src = image.dataset.src;
                  image.classList.remove("lazy-load");
                  imageObserver.unobserve(image);
                }
              });
            });
            lazyloadImages.forEach(function(image) {
              imageObserver.observe(image);
            });
          } else {
            let lazyloadThrottleTimeout;
            lazyloadImages = document.querySelectorAll(".lazy-load");

            function lazyload() {
              if(lazyloadThrottleTimeout) {
                clearTimeout(lazyloadThrottleTimeout);
              }
              lazyloadThrottleTimeout = setTimeout(function() {
                let scrollTop = window.pageYOffset;
                lazyloadImages.forEach(function(img) {
                  if(img.offsetTop < (window.innerHeight + scrollTop)) {
                    img.src = img.dataset.src;
                    img.classList.remove('lazy-load');
                  }
                });
                if(lazyloadImages.length == 0) {
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
        })
    </script>
    <script>
        if ('serviceWorker' in navigator) {
          window.addEventListener('load', function() {
            navigator.serviceWorker.register('sw.js').then(function(registration) {
              console.log('ServiceWorker registration successful with scope: ', registration.scope);
            }, function(err) {
              console.log('ServiceWorker registration failed: ', err);
            });
          });
        }
    </script>