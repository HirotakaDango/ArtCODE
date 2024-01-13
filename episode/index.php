<?php
require_once('../auth.php');

$email = $_SESSION['email'];

// Connect to the SQLite database using parameterized query
$db = new SQLite3('../database.sqlite');

// Retrieve episode name from the URL
$episodeName = isset($_GET['episode']) ? $_GET['episode'] : '';

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $existing_fav = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = $image_id");

  if ($existing_fav == 0) {
    $db->exec("INSERT INTO favorites (email, image_id) VALUES ('$email', $image_id)");
  }

  // Get the current page URL
  $currentUrl = $_SERVER['REQUEST_URI'];

  // Redirect to the current page to prevent duplicate form submissions
  header("Location: $currentUrl");
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $db->exec("DELETE FROM favorites WHERE email = '$email' AND image_id = $image_id");

  // Get the current page URL
  $currentUrl = $_SERVER['REQUEST_URI'];

  // Redirect to the current page to prevent duplicate form submissions
  header("Location: $currentUrl");
  exit();
}

// Prepare the query to get the user's numpage
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT); // Assuming $email is the email you want to search for
$resultNum = $queryNum->execute();
$user = $resultNum->fetchArray(SQLITE3_ASSOC);

$numpage = $user['numpage'];

// Set the limit of images per page
$limit = empty($numpage) ? 50 : $numpage;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Get the total number of images for the specified episode
$total = $db->querySingle("SELECT COUNT(*) FROM images WHERE episode_name = '$episodeName'");

// Count the total number of images for the specified episode with non-empty episode names
$countStmt = $db->prepare("
    SELECT COUNT(*) as count
    FROM images
    WHERE episode_name = :episodeName AND episode_name != ''
");
$countStmt->bindValue(':episodeName', $episodeName, SQLITE3_TEXT);
$totalCount = $countStmt->execute()->fetchArray(SQLITE3_ASSOC)['count'];

// Get the images for the current page and specified episode with non-empty episode names
$stmt = $db->prepare("
    SELECT images.*, users.artist, users.id AS userid, users.pic
    FROM images
    JOIN users ON images.email = users.email
    WHERE images.episode_name = :episodeName AND images.episode_name != ''
    ORDER BY images.id DESC
    LIMIT :offset, :limit
");
$stmt->bindValue(':episodeName', $episodeName, SQLITE3_TEXT);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$result = $stmt->execute();
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $episodeName; ?></title>
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <div class="container mb-2 mt-2">
      <div class="bg-body-tertiary rounded-4 p-5 w-100 mb-4 shadow position-relative">
        <h5 class="fw-bold text-start">All episodes from <?php echo $episodeName; ?></h5>
        <h6 class="fw-bold text-start"><?php echo $totalCount; ?> artworks</h6>
        <button class="btn border-0 fw-bold position-absolute end-0 top-0 m-3" onclick="sharePage()"><small><i class="bi bi-share-fill text-stroke"></i> Share</small></button>
      </div>
      <?php while ($image = $result->fetchArray()): ?>
        <div class="card rounded-4 border-0 bg-body-tertiary shadow h-100 mb-1">
          <div class="row">
            <div class="col-md-3">
              <div class="position-relative">
                <a class="shadow ratio ratio-1x1" href="../image.php?artworkid=<?php echo $image['id']; ?>">
                  <img class="object-fit-cover img-size lazy-load <?php echo ($image['type'] === 'nsfw') ? 'nsfw' : ''; ?>" data-src="../thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>">
                </a> 
                <div class="position-absolute top-0 start-0">
                  <div class="dropdown">
                    <button class="btn btn-sm btn-dark m-2 rounded-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
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

                    <?php include('../contents/card_image_3.php'); ?>
                
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-9">
              <div class="d-flex align-items-center justify-content-start h-100">
                <div class="p-3 p-md-0">
                  <div class="row">
                    <label for="" class="col-4 col-form-label text-nowrap fw-medium">Title</label>
                    <div class="col-8">
                      <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php echo $image['title']; ?>" readonly>
                    </div>
                  </div>
                  <div class="row">
                    <label for="" class="col-4 col-form-label text-nowrap fw-medium">View</label>
                    <div class="col-8">
                      <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php echo $image['view_count']; ?>" readonly>
                    </div>
                  </div>
                  <div class="row">
                    <label for="" class="col-4 col-form-label text-nowrap fw-medium">Release</label>
                    <div class="col-8">
                      <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php echo date('Y/m/d', strtotime($image['date'])); ?>" readonly>
                    </div>
                  </div>
                  <div class="row">
                    <label for="artist" class="col-4 col-form-label text-nowrap fw-medium">User</label>
                    <div class="col-8">
                      <p class="form-control-plaintext fw-bold" id="artist"><img class="object-fit-cover border border-2 rounded-circle" height="26" width="26" src="../<?php echo $image['pic']; ?>"> <a class="text-decoration-none text-dark" href="../artist.php?id=<?php echo $image['userid']; ?>"><?php echo $image['artist']; ?></a></p>
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
        <a class="btn btn-sm btn-primary fw-bold" href="?episode=<?php echo $episodeName; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
      <?php endif; ?>

      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?episode=<?php echo $episodeName; ?>&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?episode=' . $episodeName . '&page=' . $i . '">' . $i . '</a>';
          }
        }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?episode=<?php echo $episodeName; ?>&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?episode=<?php echo $episodeName; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>
    <style>
      @media (min-width: 768px) {
        .img-size {
          border-radius: 13px 0 0 13px;
        }
      }
      
      @media (max-width: 767px) {
        .img-size {
          border-radius: 13px 13px 0 0;
        }
      }
    </style>
    <script>
      function sharePage() {
        if (navigator.share) {
          navigator.share({
            title: document.title,
            url: window.location.href
          }).then(() => {
            console.log('Page shared successfully.');
          }).catch((error) => {
            console.error('Error sharing page:', error);
          });
        } else {
          console.log('Web Share API not supported.');
        }
      }
    </script>
    <script>
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      let imageContainer = document.getElementById("image-container");

      // Set the default placeholder image
      const defaultPlaceholder = "../icon/bg.png";

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
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>
