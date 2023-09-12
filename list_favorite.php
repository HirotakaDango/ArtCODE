<?php
require_once('auth.php');

$email = $_SESSION['email'];

// Connect to the database
$db = new PDO('sqlite:database.sqlite');

// Get the current user's ID
$current_user_id = $_GET['id'];

// Get the current user's email and artist
$query = $db->prepare('SELECT email, artist FROM users WHERE id = :id');
$query->bindParam(':id', $current_user_id);
$query->execute();
$current_user = $query->fetch();
$current_email = $current_user['email'];
$current_artist = $current_user['artist'];

// Get the total count of favorite images for the current user
$query = $db->prepare('SELECT COUNT(*) FROM images JOIN favorites ON images.id = favorites.image_id WHERE favorites.email = :email');
$query->bindParam(':email', $current_email);
$query->execute();
$total = $query->fetchColumn();

$limit = 100; // Set the limit of images per page

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Get favorite images with pagination
$query = $db->prepare('SELECT images.filename, images.id, images.imgdesc, images.title, images.tags, images.type FROM images JOIN favorites ON images.id = favorites.image_id WHERE favorites.email = :email ORDER BY favorites.id DESC LIMIT :limit OFFSET :offset');
$query->bindParam(':email', $current_email);
$query->bindParam(':limit', $limit, PDO::PARAM_INT);
$query->bindParam(':offset', $offset, PDO::PARAM_INT);
$query->execute();
$favorite_images = $query->fetchAll(PDO::FETCH_ASSOC);

// Process any favorite/unfavorite requests
if (isset($_POST['favorite']) || isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  
  // Check if the image ID is valid
  $query = $db->prepare('SELECT COUNT(*) FROM images WHERE id = :id');
  $query->bindParam(':id', $image_id);
  $query->execute();
  $valid_image_id = $query->fetchColumn();
  
  if ($valid_image_id) {
    // Check if the image has already been favorited by the current user
    $query = $db->prepare('SELECT COUNT(*) FROM favorites WHERE email = :email AND image_id = :image_id');
    $query->bindParam(':email', $email);
    $query->bindParam(':image_id', $image_id);
    $query->execute();
    $existing_fav = $query->fetchColumn();

    if (isset($_POST['favorite'])) {
      if ($existing_fav == 0) {
        $query = $db->prepare('INSERT INTO favorites (email, image_id) VALUES (:email, :image_id)');
        $query->bindParam(':email', $email);
        $query->bindParam(':image_id', $image_id);
        $query->execute();
      }
    } elseif (isset($_POST['unfavorite'])) {
      if ($existing_fav > 0) {
        $query = $db->prepare('DELETE FROM favorites WHERE email = :email AND image_id = :image_id');
        $query->bindParam(':email', $email);
        $query->bindParam(':image_id', $image_id);
        $query->execute();
      }
    }
  }
  
  // Redirect to the same page to prevent duplicate form submissions
  header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $current_user_id . '&page=' . $page);
  exit();
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $current_artist; ?>'s favorite</title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <?php include('header.php'); ?>
    <h5 class="text-secondary fw-bold text-center text-break mt-2"><a class="text-decoration-none link-secondary" href="artist.php?id=<?php echo $current_user_id; ?>"><?php echo $current_artist; ?>'s</a> Favorites</h5>
    <?php if (count($favorite_images) > 0): ?>
      <div class="images">
        <?php foreach ($favorite_images as $image): ?>
          <div class="image-container">
            <div class="position-relative">
              <a class="shadow rounded imagesA" href="image.php?artworkid=<?php echo $image['id']; ?>">
                <img class="lazy-load imagesImg <?php echo ($image['type'] === 'nsfw') ? 'nsfw' : ''; ?>" data-src="thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>">
              </a> 
              <div class="position-absolute top-0 start-0">
                <div class="dropdown">
                  <button class="btn btn-sm btn-dark ms-1 mt-1 rounded-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                  </button>
                  <ul class="dropdown-menu">
                    <?php
                      $is_favorited = $db->query("SELECT COUNT(*) FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = {$image['id']}")->fetchColumn();
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
                                    foreach ($tags as $tag) {
                                      $tag = trim($tag);
                                        if (!empty($tag)) {
                                    ?>
                                      <a href="tagged_images.php?tag=<?php echo urlencode($tag); ?>"
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
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class='container'>
        <p class="text-secondary text-center fw-bold">Oops... sorry, no favorited images!</p>
        <p class='text-secondary text-center fw-bold'>The one that make sense is, this user hasn't favorited any image...</p>
        <img src='icon/Empty.svg' style='width: 100%; height: 100%;'>
      </div>
    <?php endif; ?>
    <div style="position: fixed; bottom: 20px; right: 20px;">
      <button class="btn btn-primary rounded-pill fw-bold btn-md" onclick="goBack()">
        <i class="bi bi-chevron-left text-stroke"></i> back
      </button>
    </div> 
    <?php
      $totalPages = ceil($total / $limit);
      $prevPage = $page - 1;
      $nextPage = $page + 1;
    ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?id=<?php echo $current_user_id; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
      <?php endif; ?>

      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?id=<?php echo $current_user_id; ?>&page=<?php echo $page - 1; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?id=' . $current_user_id . '&page=' . $i . '">' . $i . '</a>';
          }
        }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?id=<?php echo $current_user_id; ?>&page=<?php echo $page + 1; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?id=<?php echo $current_user_id; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>
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
      function goBack() {
        window.location.href = "artist.php?id=<?php echo $current_user_id; ?>";
      }
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html> 