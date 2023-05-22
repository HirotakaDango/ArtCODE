<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}

$dbP = new SQLite3('database.sqlite'); 

// Define the number of images to display per page
$limit = 100;

// Get the current page number from the query string
if (isset($_GET['page'])) {
  $page = $_GET['page'];
} else {
  $page = 1;
}

$page = isset($_GET['page']) ? $_GET['page'] : 1;

// Calculate the offset value for the SQL query
$offset = ($page - 1) * $limit;

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $existing_fav = $dbP->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = $image_id");

  if ($existing_fav == 0) {
    $dbP->exec("INSERT INTO favorites (email, image_id) VALUES ('{$_SESSION['email']}', $image_id)");
  }

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: popular.php?page=$page");
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $dbP->exec("DELETE FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = $image_id");

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: popular.php?page=$page"); 
  exit();
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
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
  </head>
  <body>
    <?php include('header.php'); ?>
    <h5 class="ms-2 mt-2 text-secondary fw-bold"><i class="bi bi-images"></i> Popular Images</h5>
    <div class="images mb-2">
      <?php
        $email = $_SESSION['email'];

        // Get the total number of images
        $total = $dbP->querySingle("SELECT COUNT(*) FROM images");

        // Calculate the total number of pages
        $totalPages = ceil($total / $limit);
 
        // Get the images for the current page using a parameterized query with LIMIT and OFFSET clauses
        $stmtP = $dbP->prepare("SELECT images.id, images.filename, images.tags, images.title, COUNT(favorites.id) AS favorite_count FROM images LEFT JOIN favorites ON images.id = favorites.image_id GROUP BY images.id ORDER BY favorite_count DESC LIMIT :limit OFFSET :offset");
        $stmtP->bindValue(':limit', $limit, SQLITE3_INTEGER);
        $stmtP->bindValue(':offset', $offset, SQLITE3_INTEGER);
        $resultP = $stmtP->execute();

        // Display the images for the current page
        while ($imageP = $resultP->fetchArray()):
      ?>
        <div class="image-container d-none">
          <a class="shadow" href="image.php?artworkid=<?php echo $imageP['id']; ?>">
            <img class="lazy-load hori" data-src="thumbnails/<?php echo $imageP['filename']; ?>" alt="<?php echo $imageP['title']; ?>">
          </a>
          <div class="favorite-btn">
            <?php
              $stmt = $dbP->prepare("SELECT COUNT(*) FROM favorites WHERE email = ? AND image_id = ?");
              $stmt->bindValue(1, $email, SQLITE3_TEXT);
              $stmt->bindValue(2, $imageP['id'], SQLITE3_INTEGER);
              $result = $stmt->execute()->fetchArray();
              $is_favorited = $result[0];
              if ($is_favorited) {
            ?>
              <form method="POST">
                <input type="hidden" name="image_id" value="<?php echo $imageP['id']; ?>">
                <button style="margin-top: -68px;" type="submit" class="p-b3 btn btn-sm rounded btn-dark opacity-50 fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i></button>
              </form>
            <?php } else { ?>
              <form method="POST">
                <input type="hidden" name="image_id" value="<?php echo $imageP['id']; ?>">
                <button type="submit" class="p-b3 btn btn-sm rounded btn-dark opacity-50 fw-bold" name="favorite"><i class="bi bi-heart"></i></button>
              </form>
            <?php } ?> 
          </div>
        </div>
        
        <div class="image-container">
          <div class="position-relative">
            <a class="shadow" href="image.php?artworkid=<?php echo $imageP['id']; ?>">
              <img class="lazy-load hori" data-src="thumbnails/<?php echo $imageP['filename']; ?>" alt="<?php echo $imageP['title']; ?>">
            </a>
            <div class="position-absolute top-0 start-0">
              <div class="dropdown">
                <button class="btn btn-sm btn-secondary ms-1 mt-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <?php
                    $stmt = $dbP->prepare("SELECT COUNT(*) FROM favorites WHERE email = ? AND image_id = ?");
                    $stmt->bindValue(1, $email, SQLITE3_TEXT);
                    $stmt->bindValue(2, $imageP['id'], SQLITE3_INTEGER);
                    $result = $stmt->execute()->fetchArray();
                    $is_favorited = $result[0];
                    if ($is_favorited) {
                  ?>
                    <form method="POST">
                      <input type="hidden" name="image_id" value="<?php echo $imageP['id']; ?>">
                      <li><button type="submit" class="dropdown-item fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i> unfavorite</button></li>
                    </form>
                  <?php } else { ?>
                    <form method="POST">
                      <input type="hidden" name="image_id" value="<?php echo $imageP['id']; ?>">
                      <li><button type="submit" class="dropdown-item fw-bold" name="favorite"><i class="bi bi-heart"></i> favorite</button></li>
                    </form>
                  <?php } ?>
                  <li><button class="dropdown-item fw-bold" onclick="shareImage(<?php echo $imageP['id']; ?>)"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
                </ul>
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
          echo '<a type="button" class="btn rounded-pill fw-bold btn-secondary opacity-50 me-1" href="?page=' . $prevPage . '"><i class="bi bi-arrow-left-circle-fill"></i> prev</a>';
        }
        if ($page < $totalPages) {
          echo '<a type="button" class="btn rounded-pill fw-bold btn-secondary opacity-50 ms-1" href="?page=' . $nextPage . '">next <i class="bi bi-arrow-right-circle-fill"></i></a>';
        }
      ?>
    </div>
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