<?php

// Connect to the SQLite database using parameterized query
$db = new SQLite3('database.sqlite');

// Pagination variables
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = 60;
$offset = ($page - 1) * $limit;

// Get the total number of images from the database
$countStmt = $db->prepare("SELECT COUNT(*) FROM images");
$countResult = $countStmt->execute();
$total = $countResult->fetchArray()[0];

// Get 25 images from the database using parameterized query
$stmt = $db->prepare("SELECT images.*, users.email FROM images INNER JOIN users ON images.email = users.email ORDER BY images.id ASC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();
?>

    <div class="dropdown ms-3 mb-2">
      <button class="form-control text-secondary fw-bold width-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        Sort by
      </button>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item fw-bold" href="?by=newest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>">Newest</a></li>
        <li><a class="dropdown-item fw-bold active" href="?by=oldest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>">Oldest</a></li>
      </ul>
    </div>
    <div class="container-fluid">
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-1">
        <?php while ($image = $result->fetchArray()): ?>
          <?php
            $title = $image['title'];
            $filename = $image['filename'];
            $email = $image['email'];
            $artist = '';
            $stmt = $db->prepare("SELECT id, artist FROM users WHERE email = ?");
            $stmt->bindValue(1, $email, SQLITE3_TEXT);
            $result2 = $stmt->execute();
            if ($user = $result2->fetchArray()) {
              $artist = $user['artist'];
              $id = $user['id'];
            }
          ?>
          <div class="col">
            <div class="card h-100 shadow-sm rounded-1">
              <a class="d-block" href="#" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $image['id']; ?>">
                <img class="lazy-load object-fit-cover" style="width: 100%; height: 300px; border-radius: 3px 3px 0 0;" data-src="thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>">
              </a>
              <div class="card-body">
                <a class="text-decoration-none" href="image.php?artworkid=<?php echo $image['id']; ?>">
                  <h5 class="text-dark fw-bold"><?= $title ?></h5>
                </a>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-secondary disabled fw-bold">by</button>
                    <a class="btn btn-sm btn-secondary fw-bold" href="artist.php?id=<?= $id ?>"><?php echo (strlen($artist) > 25) ? substr($artist, 0, 25) . '...' : $artist; ?></a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Modal -->
          <div class="modal fade" id="infoImage_<?php echo $image['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen" role="document">
              <div class="modal-content shadow">
                <div class="modal-body p-4 text-center">
                  <h5 class="modal-title fw-bold text-start mb-2"><?php echo $image['title']?></h5>
                  <div class="row featurette">
                    <div class="col-md-5 order-md-1 mb-2">
                      <div class="position-relative">
                        <a href="image.php?artworkid=<?php echo $image['id']; ?>">
                          <img class="rounded object-fit-cover mb-3 shadow lazy-load" data-src="thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>" style="width: 100%; height: 100%;">
                        </a>
                        <button type="button" class="btn btn-dark rounded fw-bold opacity-75 position-absolute top-0 end-0 mt-1 me-1" data-bs-dismiss="modal"><i class="bi bi-x text-stroke"></i></button>
                      </div>
                    </div>
                    <div class="col-md-7 order-md-2">
                      <p class="text-start fw-semibold">share to:</p>
                      <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
                        <!-- Twitter -->
                        <a class="btn btn-outline-dark" href="https://twitter.com/intent/tweet?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                          <i class="bi bi-twitter"></i>
                        </a>
                        
                        <!-- Line -->
                        <a class="btn btn-outline-dark" href="https://social-plugins.line.me/lineit/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                          <i class="bi bi-line"></i>
                        </a>
                        
                        <!-- Email -->
                        <a class="btn btn-outline-dark" href="mailto:?body=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>">
                          <i class="bi bi-envelope-fill"></i>
                        </a>
                        
                        <!-- Reddit -->
                        <a class="btn btn-outline-dark" href="https://www.reddit.com/submit?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                          <i class="bi bi-reddit"></i>
                        </a>
                        
                        <!-- Instagram -->
                        <a class="btn btn-outline-dark" href="https://www.instagram.com/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                          <i class="bi bi-instagram"></i>
                        </a>
                        
                        <!-- Facebook -->
                        <a class="btn btn-outline-dark" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                          <i class="bi bi-facebook"></i>
                        </a>
                      </div>
                      <div class="btn-group w-100 mt-2 mb-3">
                        <a class="btn btn-outline-dark fw-bold" href="image.php?artworkid=<?php echo $image['id']; ?>"><i class="bi bi-eye-fill"></i> view</a>
                        <a class="btn btn-outline-dark fw-bold" href="images/<?php echo $image['filename']; ?>" download><i class="bi bi-download text-stroke"></i> download</a>
                        <button class="btn btn-outline-dark fw-bold" onclick="shareImage(<?php echo $image['id']; ?>)"><i class="bi bi-share-fill text-stroke"></i> share</button>
                      </div>
                      <p class="text-start fw-semibold" style="word-wrap: break-word;">
                        <?php
                          $messageText = $image['imgdesc'];
                          $messageTextWithoutTags = strip_tags($messageText);
                          $pattern = '/\bhttps?:\/\/\S+/i';

                          $formattedText = preg_replace_callback($pattern, function ($matches) {
                            $url = htmlspecialchars($matches[0]);
                            return '<a href="' . $url . '">' . $url . '</a>';
                          }, $messageTextWithoutTags);

                          $formattedTextWithLineBreaks = nl2br($formattedText);
                          echo $formattedTextWithLineBreaks;
                        ?>
                      </p>
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
                      <div class="container mt-2">
                        <?php
                          $tags = explode(',', $image['tags']);
                          foreach ($tags as $tag) {
                            $tag = trim($tag);
                            if (!empty($tag)) {
                        ?>
                          <a href="tagged_images.php?tag=<?php echo urlencode($tag); ?>"
                            class="btn btn-sm btn-secondary mb-1 rounded-3 fw-bold opacity-50">
                            <?php echo $tag; ?>
                          </a>
                        <?php }
                        } ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
    <?php include('session_user.php'); ?>
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
    <div class="mt-5"></div>
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