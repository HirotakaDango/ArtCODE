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
                            <a href="image.php?artworkid=<?php echo $image['id']; ?>">
                              <img class="rounded object-fit-cover mb-3 shadow lazy-load" data-src="thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>" style="width: 100%; height: 100%;">
                            </a>
                          </div>
                          <div class="col-md-7 order-md-2">
                            <button type="button" class="btn btn-secondary rounded w-100 fw-bold opacity-50 mb-2" data-bs-dismiss="modal">close</button>
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
                            <p class="text-start fw-semibold">
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
          </div>
        </div>
      <?php endwhile; ?>
    </div> 
    <div class="mt-3 mb-5 d-flex justify-content-center btn-toolbar container">
      <?php
        $totalPages = ceil($total / $limit);
        $prevPage = $page - 1;
        $nextPage = $page + 1;

        if ($page > 1) {
          echo '<a type="button" class="btn rounded-pill fw-bold btn-secondary opacity-50 me-1" href="?tag=' . $tag . '&page=' . $prevPage . '"><i class="bi bi-arrow-left-circle-fill"></i> prev</a>';
        }
        if ($page < $totalPages) {
          echo '<a type="button" class="btn rounded-pill fw-bold btn-secondary opacity-50 ms-1" href="?tag=' . $tag . '&page=' . $nextPage . '">next <i class="bi bi-arrow-right-circle-fill"></i></a>';
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
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>