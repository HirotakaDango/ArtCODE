<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  header('Location: session.php');
  exit();
}

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Check if an album has been specified
if (isset($_GET['album'])) {
  $album_id = $_GET['album'];
  $email = $_SESSION['email'];
  
  // Get the images for the specified album
  $stmt = $db->prepare('SELECT images.id, images.filename, images.title, images.imgdesc, images.link, album.album_name, image_album.id AS image_album_id FROM image_album INNER JOIN images ON image_album.image_id = images.id INNER JOIN album ON image_album.album_id = album.id WHERE image_album.album_id = :album_id AND image_album.email = :email ORDER BY image_album.id DESC');
  $stmt->bindValue(':album_id', $album_id, SQLITE3_INTEGER);
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $results = $stmt->execute();

  // Store images in an array for later use
  $images = [];
  while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $images[] = $row;
    
    $album_name = $row['album_name'];
  }
  
} else {
  // If no album is specified, redirect to album.php
  header('Location: album.php');
  exit();
}

// Handle deleting an image from the album if the delete button was clicked
if (isset($_POST['delete_image'])) {
  $image_album_id = $_POST['image_album_id'];
  $stmt = $db->prepare('DELETE FROM image_album WHERE id = :id AND email = :email');
  $stmt->bindValue(':id', $image_album_id, SQLITE3_INTEGER);
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->execute();
  
  // Redirect back to the same page to update the displayed images
  header("Location: album_images.php?album=$album_id");
  exit();
}

// Close the database connection 
$db->close();
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Album Images</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include 'header.php'; ?>
    <?php if (empty($images)): ?>
      <h5 class="text-secondary fw-bold text-center mt-2">This album is empty</h5>
    <?php else: ?>
      <h5 class="text-secondary fw-bold text-center mt-2">My Album <?php echo htmlspecialchars($album_name); ?></h5>
    <?php endif; ?>
    <div class="images">
      <?php foreach ($images as $image): ?>
        <div class="image-container">
          <div class="position-relative">
            <a class="shadow" href="image.php?artworkid=<?php echo htmlspecialchars($image['id']); ?>">
              <img class="lazy-load" data-src="thumbnails/<?php echo htmlspecialchars($image['filename']); ?>" alt="<?php echo $image['title']; ?>">
            </a>
            <div class="position-absolute top-0 start-0">
              <div class="dropdown">
                <button class="btn btn-sm btn-secondary ms-1 mt-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <form method="POST" onsubmit="return confirm('Are you sure you want to delete this image from the album?');">
                    <input type="hidden" name="image_album_id" value="<?php echo htmlspecialchars($image['image_album_id']); ?>">
                    <li><button type="submit" name="delete_image" class="dropdown-item fw-bold"><i class="bi bi-trash-fill"></i> delete</button></li>
                  </form> 
                  <li><button class="dropdown-item fw-bold" onclick="shareImage(<?php echo $image['id']; ?>)"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
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