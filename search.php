<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}

$email = $_SESSION['email'];

// Establish a connection to the SQLite database
$database = new SQLite3('database.sqlite');

// Handle the search form submission
if (isset($_GET['search'])) {
  $searchTerm = $_GET['search'];

  // Prepare the search term by removing leading/trailing spaces and converting to lowercase
  $searchTerm = trim(strtolower($searchTerm));

  // Split the search term by comma to handle multiple tags
  $tags = array_map('trim', explode(',', $searchTerm));

  // Prepare the search query with placeholders for tags
  $placeholders = implode(',', array_fill(0, count($tags), '?'));

  // Build the SQL query to search for images by tags and title
  $query = "SELECT * FROM images WHERE ";

  // Create an array to hold the conditions for partial word matches
  $conditions = array();

  // Add conditions for tags
  foreach ($tags as $index => $tag) {
    $conditions[] = "LOWER(tags) LIKE ?";
  }

  // Add condition for title
  $conditions[] = "LOWER(title) LIKE ?";

  // Combine all conditions using OR
  $query .= implode(' OR ', $conditions);

  // Add the ORDER BY clause to order the results by id and tags
  $query .= " ORDER BY id DESC, tags ASC";

  // Prepare the SQL statement
  $statement = $database->prepare($query);

  // Bind the tags as parameters with wildcard matching
  foreach ($tags as $index => $tag) {
    $statement->bindValue($index + 1, "%$tag%", SQLITE3_TEXT);
  }

  // Bind the search term for title as the last parameter with wildcard matching
  $statement->bindValue(count($tags) + 1, "%$searchTerm%", SQLITE3_TEXT);

  // Execute the query
  $result = $statement->execute();
} else {
  // Display all images if no search term is provided
  $result = $database->query("SELECT * FROM images ORDER BY id DESC");
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $searchTerm; ?></title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container-fluid mt-2">
      <form action="search.php" method="GET" class="mb-2">
        <div class="input-group">
          <input type="text" name="search" class="form-control fw-bold" placeholder="Search by tags or title" maxlength="40" required>
          <button type="submit" class="btn btn-primary"><i class="bi bi-search text-stroke"></i></button>
        </div>
      </form>
      <p class="fw-bold text-secondary mb-2">search for "<?php echo $searchTerm; ?>"</p>
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-1">
        <?php
          // Display the search results as image cards
          while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $tags = explode(',', $row['tags']);
            $tags = array_map('trim', $tags);
          ?>
            <div class="col">
              <div class="card h-100 shadow-sm rounded-1">
                <a class="d-block" href="#" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $row['id']; ?>">
                  <img class="lazy-load object-fit-cover" style="width: 100%; height: 300px; border-radius: 3px 3px 0 0;" data-src="thumbnails/<?php echo $row['filename']; ?>" alt="<?php echo $row['title']; ?>">
                </a>
                <div class="card-body">
                  <h5 class="card-title fw-bold"><?php echo $row['title']; ?></h5>
                  <p class="card-text fw-bold">Tags:
                    <?php
                      foreach ($tags as $tag) {
                        echo '<a href="tagged_images.php?tag=' . $tag . '" class="badge bg-secondary">' . $tag . '</a> ';
                      }
                    ?>
                  </p>
                </div>
              </div>
              <!-- Modal -->
              <div class="modal fade" id="infoImage_<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen" role="document">
                  <div class="modal-content shadow">
                    <div class="modal-body p-4 text-center">
                      <h5 class="modal-title fw-bold text-start mb-2"><?php echo $row['title']?></h5>
                      <div class="row featurette">
                        <div class="col-md-5 order-md-1 mb-2">
                          <div class="position-relative">
                            <a href="image.php?artworkid=<?php echo $row['id']; ?>">
                              <img class="rounded object-fit-cover mb-3 shadow lazy-load" data-src="thumbnails/<?php echo $row['filename']; ?>" alt="<?php echo $row['title']; ?>" style="width: 100%; height: 100%;">
                            </a>
                            <button type="button" class="btn btn-dark rounded fw-bold opacity-75 position-absolute top-0 end-0 mt-1 me-1" data-bs-dismiss="modal"><i class="bi bi-x text-stroke"></i></button>
                          </div>
                        </div>
                        <div class="col-md-7 order-md-2">
                          <p class="text-start fw-semibold">share to:</p>
                          <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
                            <!-- Twitter -->
                            <a class="btn btn-outline-dark" href="https://twitter.com/intent/tweet?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                              <i class="bi bi-twitter"></i>
                            </a>
                        
                            <!-- Line -->
                            <a class="btn btn-outline-dark" href="https://social-plugins.line.me/lineit/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                              <i class="bi bi-line"></i>
                            </a>
                        
                            <!-- Email -->
                            <a class="btn btn-outline-dark" href="mailto:?body=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $row['id']; ?>">
                              <i class="bi bi-envelope-fill"></i>
                            </a>
                        
                            <!-- Reddit -->
                            <a class="btn btn-outline-dark" href="https://www.reddit.com/submit?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                              <i class="bi bi-reddit"></i>
                            </a>
                        
                            <!-- Instagram -->
                            <a class="btn btn-outline-dark" href="https://www.instagram.com/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                              <i class="bi bi-instagram"></i>
                            </a>
                        
                            <!-- Facebook -->
                            <a class="btn btn-outline-dark" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/image.php?artworkid=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                              <i class="bi bi-facebook"></i>
                            </a>
                          </div>
                          <div class="btn-group w-100 mt-2 mb-3">
                            <a class="btn btn-outline-dark fw-bold" href="image.php?artworkid=<?php echo $row['id']; ?>"><i class="bi bi-eye-fill"></i> view</a>
                            <a class="btn btn-outline-dark fw-bold" href="images/<?php echo $row['filename']; ?>" download><i class="bi bi-download text-stroke"></i> download</a>
                            <button class="btn btn-outline-dark fw-bold" onclick="shareImage(<?php echo $row['id']; ?>)"><i class="bi bi-share-fill text-stroke"></i> share</button>
                          </div>
                          <p class="text-start fw-semibold" style="word-wrap: break-word;">
                            <?php
                              $messageText = $row['imgdesc'];
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
                            <p class="text-start fw-semibold">Image ID: "<?php echo $row['id']?>"</p>
                            <?php
                              // Get image size in megabytes
                              $row_size = round(filesize('images/' . $row['filename']) / (1024 * 1024), 2);

                              // Get image dimensions
                              list($width, $height) = getimagesize('images/' . $row['filename']);
                      
                              // Display image information
                              echo "<p class='text-start fw-semibold'>Image data size: " . $row_size . " MB</p>";
                              echo "<p class='text-start fw-semibold'>Image dimensions: " . $width . "x" . $height . "</p>";
                              echo "<p class='text-start fw-semibold'><a class='text-decoration-none' href='images/" . $row['filename'] . "'>View original image</a></p>";
                            ?>
                          </div>
                          <div class="container mt-2">
                            <?php
                              $tags = explode(',', $row['tags']);
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
            </div>
          <?php
          }
        ?>
      </div>
    </div>
    <div class="mt-5"></div>
    <style>
      .text-stroke {
        -webkit-text-stroke: 1px;
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
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>