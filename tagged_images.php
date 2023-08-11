<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}

$email = $_SESSION['email'];

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Retrieve the tag from the URL parameter and sanitize it
$tag = trim($_GET['tag']);
$tag = filter_var($tag, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

// Remove any additional spaces from the tag
$tagWithoutSpaces = str_replace(' ', '', $tag);

$stmt = $db->prepare("SELECT COUNT(*) FROM images WHERE REPLACE(tags, ' ', '') LIKE :tagWithoutSpaces ESCAPE '\\' OR REPLACE(tags, ' ', '') LIKE :tag_start ESCAPE '\\' OR REPLACE(tags, ' ', '') LIKE :tag_end ESCAPE '\\' OR tags = :tag_exact");
$stmt->bindValue(':tagWithoutSpaces', "{$tagWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':tag_start', "%,{$tagWithoutSpaces}", SQLITE3_TEXT);
$stmt->bindValue(':tag_end', "%,{$tagWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':tag_exact', $tag, SQLITE3_TEXT);
$count = $stmt->execute()->fetchArray()[0];

// Define the limit and offset for the query
$limit = 100; // Number of items per page
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Get the current page number from the URL parameter
$offset = ($page - 1) * $limit; // Calculate the offset based on the page number and limit

// Retrieve the total number of images with the specified tag
$stmt = $db->prepare("SELECT COUNT(*) FROM images WHERE REPLACE(tags, ' ', '') LIKE :tagWithoutSpaces ESCAPE '\\' OR REPLACE(tags, ' ', '') LIKE :tag_start ESCAPE '\\' OR REPLACE(tags, ' ', '') LIKE :tag_end ESCAPE '\\' OR tags = :tag_exact");
$stmt->bindValue(':tagWithoutSpaces', "{$tagWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':tag_start', "%,{$tagWithoutSpaces}", SQLITE3_TEXT);
$stmt->bindValue(':tag_end', "%,{$tagWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':tag_exact', $tag, SQLITE3_TEXT);
$total = $stmt->execute()->fetchArray()[0];

// Retrieve the images for the current page
$stmt = $db->prepare("SELECT * FROM images WHERE REPLACE(tags, ' ', '') LIKE :tagWithoutSpaces ESCAPE '\\' OR REPLACE(tags, ' ', '') LIKE :tag_start ESCAPE '\\' OR REPLACE(tags, ' ', '') LIKE :tag_end ESCAPE '\\' OR tags = :tag_exact ORDER BY id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':tagWithoutSpaces', "{$tagWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':tag_start', "%,{$tagWithoutSpaces}", SQLITE3_TEXT);
$stmt->bindValue(':tag_end', "%,{$tagWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':tag_exact', $tag, SQLITE3_TEXT);
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE email = :email AND image_id = :image_id");
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':image_id', $image_id, SQLITE3_INTEGER);
  $existing_fav = $stmt->execute()->fetchArray()[0];

  if ($existing_fav == 0) {
    $stmt = $db->prepare("INSERT INTO favorites (email, image_id) VALUES (:email, :image_id)");
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':image_id', $image_id, SQLITE3_INTEGER);
    $stmt->execute();
  }

  // Redirect to the same page to prevent duplicate form submissions
  $currentUrl = strtok($_SERVER["REQUEST_URI"], '?');
  header("Location: {$currentUrl}?tag={$tag}&page={$page}");
  exit();
} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $stmt = $db->prepare("DELETE FROM favorites WHERE email = :email AND image_id = :image_id");
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':image_id', $image_id, SQLITE3_INTEGER);
  $stmt->execute();

  // Redirect to the same page to prevent duplicate form submissions
  $currentUrl = strtok($_SERVER["REQUEST_URI"], '?');
  header("Location: {$currentUrl}?tag={$tag}&page={$page}");
  exit();
}
?>

<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $tag; ?></title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>  
    <?php include('header.php'); ?>
    <h5 class="text-secondary ms-2 mt-2 fw-bold"><i class="bi bi-tags-fill"></i> <?php echo $tag; ?> (<?php echo $count; ?>)</h5>
    <!-- Display the images -->
    <div class="images">
      <?php while ($image = $result->fetchArray()): ?>
        <div class="image-container">
          <div class="position-relative">
            <a class="shadow rounded imagesA" href="image.php?artworkid=<?php echo $image['id']; ?>">
              <img class="lazy-load imagesImg" data-src="thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>">
            </a> 
            <div class="position-absolute top-0 start-0">
              <div class="dropdown">
                <button class="btn btn-sm btn-dark ms-1 mt-1 rounded-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                              <a class="btn btn-outline-dark fw-bold" href="image.php?artworkid=<?php echo $image['id']; ?>"><i class="bi bi-eye-fill"></i> view</a>
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
                                  $image_size = round(filesize('images/' . $image['filename']) / (1024 * 1024), 2);
                                  $total_image_size += $image_size; // Add the main image size to the total
                                  list($width, $height) = getimagesize('images/' . $image['filename']);
                                  echo "<p class='text-start fw-semibold'>Image data size: " . $image_size . " MB</p>";
                                  echo "<p class='text-start fw-semibold'>Image dimensions: " . $width . "x" . $height . "</p>";
                                  echo "<p class='text-start fw-semibold'><a class='text-decoration-none' href='images/" . $image['filename'] . "'>View original image</a></p>";
                                
                                  // Assuming you have a separate query to fetch child images
                                  $child_images_result = $db1->query("SELECT filename FROM image_child WHERE image_id = " . $image['id']);
                                
                                  while ($child_image = $child_images_result->fetchArray()) {
                                    $child_image_size = round(filesize('images/' . $child_image['filename']) / (1024 * 1024), 2);
                                    $total_image_size += $child_image_size; // Add child image size to the total
                                    list($child_width, $child_height) = getimagesize('images/' . $child_image['filename']);
                                    echo "<p class='text-start fw-semibold'>Child Image data size: " . $child_image_size . " MB</p>";
                                    echo "<p class='text-start fw-semibold'>Child Image dimensions: " . $child_width . "x" . $child_height . "</p>";
                                    echo "<p class='text-start fw-semibold'><a class='text-decoration-none' href='images/" . $child_image['filename'] . "'>View original child image</a></p>";
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
                                xhr.open('GET', 'download_images.php?artworkid=' + artworkId, true);

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
                              <small>2. If you found download error or failed, <a class="text-decoration-none" href="download_images.php?artworkid=<?php echo $image['id']; ?>">click this link</a> for third option if download all images error or failed.</small>
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
                                  foreach ($tags as $tag1) {
                                    $tag1 = trim($tag1);
                                      if (!empty($tag1)) {
                                  ?>
                                    <a href="tagged_images.php?tag=<?php echo urlencode($tag1); ?>"
                                      class="btn btn-sm btn-secondary mb-1 rounded-3 fw-bold opacity-50">
                                      <i class="bi bi-tags-fill"></i> <?php echo $tag1; ?>
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
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div> 
    <?php
      $totalPages = ceil($total / $limit);
      $prevPage = $page - 1;
      $nextPage = $page + 1;
    ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?tag=<?php echo $tag; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
      <?php endif; ?>

      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?tag=<?php echo $tag; ?>&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?tag=<?php echo $tag; ?>&page=' . $i . '">' . $i . '</a>';
          }
        }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?tag=<?php echo $tag; ?>&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?tag=<?php echo $tag; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>
    <style>
      .images {
        display: grid;
        grid-template-columns: repeat(2, 1fr); /* Two columns in mobile view */
        grid-gap: 3px;
        justify-content: center;
        margin-right: 3px;
        margin-left: 3px;
      }

      .text-stroke {
        -webkit-text-stroke: 1px;
      }
      
      @media (min-width: 768px) {
        /* For desktop view, change the grid layout */
        .images {
          grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
      }

      .imagesA {
        display: block;
        border-radius: 4px;
        overflow: hidden;
      }

      .imagesImg {
        width: 100%;
        height: auto;
        object-fit: cover;
        height: 200px;
        transition: transform 0.5s ease-in-out;
      }
    </style>
    <script>
      function shareImage(userId) {
        // Compose the share URL
        var shareUrl = 'image.php?artworkid=' + userId;

        // Check if the Share API is supported by the browser
        if (navigator.share) {
          navigator.share({
          url: shareUrl
        })
          .then(() => console.log('Shared successfully.'))
          .catch((error) => console.error('Error sharing:', error));
        } else {
          console.log('Share API is not supported in this browser.');
          // Provide an alternative action for browsers that do not support the Share API
          // For example, you can open a new window with the share URL
          window.open(shareUrl, '_blank');
        }
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
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>