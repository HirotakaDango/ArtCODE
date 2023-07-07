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
  $yearFilter = $_GET['year']; // Retrieve the year filter value

  // Prepare the search term by removing leading/trailing spaces and converting to lowercase
  $searchTerm = trim(strtolower($searchTerm));

  // Split the search term by comma to handle multiple tags or titles
  $terms = array_map('trim', explode(',', $searchTerm));

  // Prepare the search query with placeholders for terms
  $query = "SELECT * FROM images WHERE ";

  // Create an array to hold the conditions for partial word matches
  $conditions = array();

  // Add conditions for tags and titles
  foreach ($terms as $index => $term) {
    $conditions[] = "(LOWER(tags) LIKE ? OR LOWER(title) LIKE ?)";
  }

  // Combine all conditions using OR
  $query .= implode(' OR ', $conditions);

  // Add the ORDER BY clause to order by ID in descending order
  $query .= " ORDER BY id DESC";

  // Prepare the SQL statement
  $statement = $database->prepare($query);

  // Bind the terms as parameters with wildcard matching for tags and titles
  $paramIndex = 1;
  foreach ($terms as $term) {
    $wildcardTerm = "%$term%";
    $statement->bindValue($paramIndex++, $wildcardTerm, SQLITE3_TEXT);
    $statement->bindValue($paramIndex++, $wildcardTerm, SQLITE3_TEXT);
  }

  // Execute the query
  $result = $statement->execute();

  // Filter the images by year if a year value is provided
  if (!empty($yearFilter) && $yearFilter !== 'all') {
    $filteredImages = array();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
      $imageYear = date('Y', strtotime($row['date']));
      if (strtolower($imageYear) === $yearFilter) {
        $filteredImages[] = $row;
      }
    }
    $resultArray = $filteredImages;
  } else {
    // Retrieve all images
    $resultArray = array();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
      $resultArray[] = $row;
    }
  }

  // Count the number of images found
  $numImages = count($resultArray);
} else {
  // Retrieve all images if no search term is provided
  $query = "SELECT * FROM images ORDER BY id DESC";
  $result = $database->query($query);
  $resultArray = array();
  while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $resultArray[] = $row;
  }
  $numImages = count($resultArray);
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
      <div class="mb-2">
        <form action="search.php" method="GET">
          <div class="input-group">
            <input type="text" name="search" class="form-control fw-bold" placeholder="Search tags or title" value="<?php echo isset($searchTerm) ? $searchTerm : ''; ?>" maxlength="30" required onfocus="this.oldValue = this.value;" oninput="updatePlaceholder(this);">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search text-stroke"></i></button>
          </div>
        </form>
      </div>
      <div class="mb-2">
        <form action="search.php" method="GET">
          <div class="input-group">
            <select name="year" class="form-control fw-bold" onchange="this.form.submit()">
              <option value="all" <?php echo ($yearFilter === 'all') ? 'selected' : ''; ?>>All Years</option>
              <?php
                // Fetch distinct years from the "date" column in the images table
                $yearsQuery = "SELECT DISTINCT strftime('%Y', date) AS year FROM images";
                $yearsResult = $database->query($yearsQuery);
                while ($yearRow = $yearsResult->fetchArray(SQLITE3_ASSOC)) {
                  $year = $yearRow['year'];
                  $selected = ($year == $yearFilter) ? 'selected' : '';
                  echo '<option value="' . $year . '"' . $selected . '>' . $year . '</option>';
                }
              ?>
            </select>
            <input type="hidden" name="search" value="<?php echo isset($searchTerm) ? $searchTerm : ''; ?>">
            <div class="input-group-prepend">
              <span class="input-group-text rounded-start-0">
                <i class="bi bi-calendar-fill"></i>
              </span>
            </div>
          </div>
        </form>
      </div>
      <div class="d-flex mb-1">
        <p class="fw-bold text-secondary mb-1 mt-1">search for "<?php echo $searchTerm; ?>"</p>
        <button type="button" class="btn btn-sm btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#infoSearchA">
          <i class="bi bi-info-circle-fill"></i> 
        </button>
      </div>
      <h6 class="badge bg-primary"><?php echo $numImages; ?> images found</h6>
      <div class="modal fade" id="infoSearchA" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5 fw-semibold" id="exampleModalLabel"><i class="bi bi-info-circle-fill"></i> Search Tips</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p class="fw-semibold text-center">"You can search multi tags or title using comma to get multiple result!"</p>
              <p class="fw-semibold">example:</p>
              <input class="form-control text-dark fw-bold" placeholder="tags, title (e.g: white, sky)" readonly>
            </div>
          </div>
        </div>
      </div>
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-1">
        <?php
          // Display the search results as image cards
          foreach ($resultArray as $row) {
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
                  <p class="card-text fw-bold">
                    <?php
                      foreach ($tags as $tag) {
                        echo '<a href="tagged_images.php?tag=' . $tag . '" class="badge bg-secondary opacity-50">' . $tag . '</a> ';
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
      function updatePlaceholder(input) {
        input.setAttribute('placeholder', input.value.trim() !== '' ? input.value.trim() : 'Search by tags or title');
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