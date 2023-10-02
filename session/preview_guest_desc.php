<?php

// Connect to the SQLite database using parameterized query
$db = new SQLite3('../database.sqlite');

// Pagination variables
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = 60;
$offset = ($page - 1) * $limit;

// Get the total number of images from the database
$countStmt = $db->prepare("SELECT COUNT(*) FROM images");
$countResult = $countStmt->execute();
$total = $countResult->fetchArray()[0];

// Get 25 images from the database using parameterized query
$stmt = $db->prepare("SELECT images.*, users.email FROM images INNER JOIN users ON images.email = users.email ORDER BY images.id DESC LIMIT :limit OFFSET :offset");
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
                <img class="lazy-load object-fit-cover <?php echo ($image['type'] === 'nsfw') ? 'nsfw' : ''; ?>" style="width: 100%; height: 300px; border-radius: 3px 3px 0 0;" data-src="../thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>">
              </a>
              <div class="card-body card-round bg-light z-2">
                <a class="text-decoration-none" href="../image.php?artworkid=<?php echo $image['id']; ?>">
                  <h5 class="text-dark fw-bold"><?= $title ?></h5>
                </a>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-secondary disabled fw-bold">by</button>
                    <a class="btn btn-sm btn-secondary fw-bold" href="../artist.php?id=<?= $id ?>"><?php echo (strlen($artist) > 25) ? substr($artist, 0, 25) . '...' : $artist; ?></a>
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
                          <a href="../image.php?artworkid=<?php echo $image['id']; ?>">
                            <img class="rounded object-fit-cover mb-3 shadow lazy-load" data-src="../thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>" style="width: 100%; height: 100%;">
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
                        <div class="btn-group w-100" role="group" aria-label="Share Buttons">
                          <!-- WhatsApp -->
                          <a class="btn btn-outline-dark" href="https://wa.me/?text=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-whatsapp"></i>
                          </a>
  
                          <!-- Pinterest -->
                          <a class="btn btn-outline-dark" href="https://pinterest.com/pin/create/button/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-pinterest"></i>
                          </a>

                          <!-- LinkedIn -->
                          <a class="btn btn-outline-dark" href="https://www.linkedin.com/shareArticle?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-linkedin"></i>
                          </a>
  
                          <!-- Messenger -->
                          <a class="btn btn-outline-dark" href="https://www.facebook.com/dialog/send?link=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-messenger"></i>
                          </a>
  
                          <!-- Telegram -->
                          <a class="btn btn-outline-dark" href="https://telegram.me/share/url?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-telegram"></i>
                          </a>
  
                          <!-- Snapchat -->
                          <a class="btn btn-outline-dark" href="https://www.snapchat.com/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-snapchat"></i>
                          </a>
                        </div>
                        <div class="btn-group w-100 mt-2 mb-3">
                          <a class="btn btn-outline-dark fw-bold" href="../image.php?artworkid=<?php echo $image['id']; ?>"><i class="bi bi-eye-fill"></i> view</a>
                          <button class="btn btn-outline-dark fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInfo" aria-expanded="false" aria-controls="collapseExample">
                            <i class="bi bi-info-circle-fill"></i> more info
                          </button>
                          <button class="btn btn-outline-dark fw-bold" onclick="shareImage(<?php echo $image['id']; ?>)"><i class="bi bi-share-fill text-stroke"></i> share</button>
                        </div>
                        <p class="text-start fw-bold" style="word-wrap: break-word;">
                          <?php
                            if (!empty($image['imgdesc'])) {
                              $messageText = $image['imgdesc'];
                              $messageTextWithoutTags = strip_tags($messageText);
                              $pattern = '/\bhttps?:\/\/\S+/i';

                              $formattedText = preg_replace_callback($pattern, function ($matches) {
                                $url = htmlspecialchars($matches[0]);
                                return '<a href="' . $url . '">' . $url . '</a>';
                              }, $messageTextWithoutTags);

                              $formattedTextWithLineBreaks = nl2br($formattedText);
                              echo $formattedTextWithLineBreaks;
                            } else {
                              echo "Image description is empty.";
                            }
                          ?>
                        </p>
                        <div class="collapse mt-2 mb-2" id="collapseInfo">
                          <div class="card container">
                            <p class="text-center fw-semibold mt-2">Image Information</p>
                            <p class="text-start fw-semibold">Image ID: "<?php echo $image['id']?>"</p>
                            <?php
                              $total_image_size = 0; // Initialize a variable to keep track of total image size
                                
                              // Calculate and display image size and dimensions for the main image
                              $image_size = round(filesize('../images/' . $image['filename']) / (1024 * 1024), 2);
                              $total_image_size += $image_size; // Add the main image size to the total
                              list($width, $height) = getimagesize('../images/' . $image['filename']);
                              echo "<p class='text-start fw-semibold'>Image data size: " . $image_size . " MB</p>";
                              echo "<p class='text-start fw-semibold'>Image dimensions: " . $width . "x" . $height . "</p>";
                              echo "<p class='text-start fw-semibold'><a class='text-decoration-none' href='../images/" . $image['filename'] . "'>View original image</a></p>";
                                
                              // Assuming you have a separate query to fetch child images
                              $child_images_result = $db->query("SELECT filename FROM image_child WHERE image_id = " . $image['id']);
                                
                              while ($child_image = $child_images_result->fetchArray()) {
                                $child_image_size = round(filesize('../images/' . $child_image['filename']) / (1024 * 1024), 2);
                                $total_image_size += $child_image_size; // Add child image size to the total
                                list($child_width, $child_height) = getimagesize('../images/' . $child_image['filename']);
                                echo "<p class='text-start fw-semibold'>Child Image data size: " . $child_image_size . " MB</p>";
                                echo "<p class='text-start fw-semibold'>Child Image dimensions: " . $child_width . "x" . $child_height . "</p>";
                                echo "<p class='text-start fw-semibold'><a class='text-decoration-none' href='../images/" . $child_image['filename'] . "'>View original child image</a></p>";
                              }
                                
                              // Display the total image size after processing all images
                              echo "<p class='text-start fw-semibold'>Total Image data size: " . $total_image_size . " MB</p>";
                            ?>
                          </div>
                        </div>
                        <a class="btn btn-primary fw-bold rounded-4 w-100" href="#" onclick="downloadWithProgressBar(<?php echo $image['id']; ?>, '<?php echo $image['title']; ?>')">
                          <i class="bi bi-download text-stroke"></i> download all images (<?php echo $total_image_size; ?> MB)
                        </a>
                        <div class="progress fw-bold mt-2 rounded-4" id="progressBarContainer_<?php echo $image['id']; ?>" style="height: 30px; display: none;">
                          <div id="progressBar_<?php echo $image['id']; ?>" class="progress-bar progress-bar progress-bar-animated fw-bold" style="width: 0; height: 30px;">0%</div>
                        </div>
                          <script>
                            function downloadWithProgressBar(artworkId, title) {
                            var progressBar = document.getElementById('progressBar_' + artworkId);
                            var progressBarContainer = document.getElementById('progressBarContainer_' + artworkId);
                            title = title.replace(/\s+/g, '_');

                            // Create a new XMLHttpRequest object
                            var xhr = new XMLHttpRequest();

                            // Function to update the progress bar
                            function updateProgress(event) {
                              if (event.lengthComputable) {
                                var percentComplete = (event.loaded / event.total) * 100;
                                progressBar.style.width = percentComplete + '%';
                                progressBar.innerHTML = percentComplete.toFixed(2) + '%';
                              }
                            }

                            // Set up the XMLHttpRequest object
                            xhr.open('GET', '../download_images.php?artworkid=' + artworkId, true);

                            // Set the responseType to 'blob' to handle binary data
                            xhr.responseType = 'blob';

                            // Track progress with the updateProgress function
                            xhr.addEventListener('progress', updateProgress);

                            // On successful download completion
                            xhr.onload = function () {
                              progressBar.innerHTML = '100%';
                              // Delay hiding the progress bar to show 100% for a brief moment
                              setTimeout(function () {
                                progressBarContainer.style.display = 'none';
                              }, 1000);

                              // Create a download link for the downloaded file
                              var downloadLink = document.createElement('a');
                              downloadLink.href = URL.createObjectURL(xhr.response);
                              downloadLink.download = title + '_image_id_' + artworkId + '.zip';
                              downloadLink.style.display = 'none';
                              document.body.appendChild(downloadLink);
                              downloadLink.click(); // Trigger the click event to download the file
                              document.body.removeChild(downloadLink); // Remove the link from the document
                            };

                            // Show the progress bar container
                            progressBarContainer.style.display = 'block';

                            // Send the XMLHttpRequest to start the download
                            xhr.send();
                          }
                        </script>
                        <h5 class="fw-bold text-center mt-2">Please Note!</h5>
                        <p class="fw-bold text-center container">
                          <small>1. Download can take a really long time, wait until progress bar reach 100% or appear download pop up in the notification.</small>
                        </p>
                        <p class="fw-bold text-center container">
                          <small>2. If you found download error or failed, <a class="text-decoration-none" href="../download_images.php?artworkid=<?php echo $image['id']; ?>">click this link</a> for third option if download all images error or failed.</small>
                        </p>
                        <p class="fw-bold text-center container">
                          <small>3. If you found problem where the zip contain empty file or 0b, download the images manually.</small>
                        </p>
                        <p class="fw-bold text-center container">
                          <small>4. Server sometimes have problem with file and folder path, download manually is the best option if this happening.</small>
                        </p>
                        <div class="container mt-2">
                          <?php
                            if (!empty($image['tags'])) {
                              $tags = explode(',', $image['tags']);
                              foreach ($tags as $tag) {
                                $tag = trim($tag);
                                  if (!empty($tag)) {
                              ?>
                                <a href="../tagged_images.php?tag=<?php echo urlencode($tag); ?>"
                                  class="btn btn-sm btn-secondary mb-1 rounded-3 fw-bold opacity-50">
                                  <i class="bi bi-tags-fill"></i> <?php echo $tag; ?>
                                </a>
                              <?php
                                }
                              }
                            } else {
                              echo "No tags available.";
                            }
                          ?>
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
    </div>
    <?php
      $totalPages = ceil($total / $limit);
      $prevPage = $page - 1;
      $nextPage = $page + 1;
    ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=newest&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
      <?php endif; ?>

      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=newest&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?by=newest&page=' . $i . '">' . $i . '</a>';
          }
        }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=newest&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=newest&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>
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
            navigator.serviceWorker.register('sw.js').then(function(registration) {
              console.log('ServiceWorker registration successful with scope: ', registration.scope);
            }, function(err) {
              console.log('ServiceWorker registration failed: ', err);
            });
          });
        }
    </script>