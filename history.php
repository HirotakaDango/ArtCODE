<?php
require_once('auth.php');

// Connect to the database using PDO
$db = new PDO('sqlite:database.sqlite');

// Retrieve the user's email from the session
$email = $_SESSION['email'];

// Fetch the user's history with image details from the database
$stmt = $db->prepare("SELECT h.*, i.filename, i.tags, i.title, i.imgdesc 
                     FROM history h
                     JOIN images i ON h.image_artworkid = i.id
                     WHERE h.email = :email
                     ORDER BY h.id DESC");
$stmt->bindParam(':email', $email);
$stmt->execute();
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission to delete history
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_history'])) {
  // Delete all history entries for the user's email
  $deleteStmt = $db->prepare("DELETE FROM history WHERE email = :email");
  $deleteStmt->bindParam(':email', $email);
  $deleteStmt->execute();

  // Redirect back to the history page
  header("Location: history.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>History</title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container mt-2">
      <h5 class="mb-2 text-center fw-bold text-secondary"><i class="bi bi-clock-history"></i> History</h5>
      <p class="fw-semibold text-center text-secondary">All your activities of explore images will be recorded here. You can delete them if you want.</p>
      <!-- Delete All History Button -->
      <form class="mb-2" method="POST" action="history.php" onsubmit="return confirm('Are you sure you want to delete all history?');">
        <input type="hidden" name="delete_history" value="true">
        <button type="submit" class="btn btn-sm btn-danger w-100 fw-semibold">Delete All History</button>
      </form>
      <?php if (count($history) > 0) { ?>
        <?php foreach ($history as $item) { ?>
          <a class="list-group-item list-group-item-action" href="<?php echo $item['history']; ?>">
            <div class="card mb-3">
              <div class="row g-0">
                <div class="col-md-4 d-md-none d-lg-none">
                  <?php
                    // Display the image if the filename exists
                    if (!empty($item['filename'])) {
                      echo '<img src="thumbnails/' . $item['filename'] . '" class="object-fit-cover rounded-img-card" style="width: 100%; height: 300px;" alt="'.$item['title'].'">';
                    }
                  ?>
                </div>
                <div class="col-md-8 imgdesc-column">
                  <div class="card-body">
                    <h5 class="card-title fw-bold"><?php echo $item['title']; ?></h5>
                    <p class="card-text fw-bold">
                      <small>
                        <?php
                          $messageText = $item['imgdesc'];
                          $messageTextWithoutTags = strip_tags($messageText);
                          $pattern = '/\bhttps?:\/\/\S+/i';

                          $formattedText = preg_replace_callback($pattern, function ($matches) {
                            $url = htmlspecialchars($matches[0]);
                            return '<a href="' . $url . '">' . $url . '</a>';
                          }, $messageTextWithoutTags);

                          $formattedTextWithLineBreaks = nl2br($formattedText);
                          echo $formattedTextWithLineBreaks;
                        ?>
                      </small>
                    </p>
                    <p class="card-text fw-semibold"><small class="text-body-secondary">visited: <?php echo $item['date_history']; ?></small></p>
                  </div>
                </div>
                <div class="col-md-4 display-small-none">
                  <?php
                    // Display the image if the filename exists
                    if (!empty($item['filename'])) {
                      echo '<img src="thumbnails/' . $item['filename'] . '" class="object-fit-cover rounded-img-card" style="width: 100%; height: 100%;" alt="'.$item['title'].'">';
                    }
                  ?>
                </div>
              </div>
            </div>
          </a>
        <?php } ?>
      <?php } else { ?>
        <h5 class="position-absolute top-50 start-50 translate-middle fw-bold display-5">No history.</h5>
      <?php } ?>
    </div>
    <div class="mt-5"></div>
    <style>
      .imgdesc-column {
        width: 66.666667%;
      }
    
      @media (max-width: 767px) {
        .display-small-none {
          display: none;
        }
        
        .rounded-img-card {
          border-radius: 5px 5px 0 0;
        }
      }
      
      @media (min-width: 768px) {
        .rounded-img-card {
          border-radius: 0 5px 5px 0;
        }
      }
    </style>
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
