<?php
require_once('../auth.php');

// Connect to the database using PDO
$db = new PDO('sqlite:../database.sqlite');

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  // Redirect to index.php if not logged in
  header("Location: ../index.php");
  exit;
}

// Get the filename from the query string
$filename = $_GET['id'];

// Get the current image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id = :filename ");
$stmt->bindParam(':filename', $filename);
$stmt->execute();
$image = $stmt->fetch();

// Get the ID of the current image and the email of the owner
$image_id = $image['id'];
$email = $image['email'];

// Get all child images associated with the current image from the "image_child" table
$stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :image_id");
$stmt->bindParam(':image_id', $image_id);
$stmt->execute();
$child_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>All Images From <?php echo $image['title']; ?></title>
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('backheader.php'); ?>
    <div class="container-fluid my-5">
      <?php include('nav.php'); ?>
      <?php
      // Function to calculate the size of an image in MB
      function getImageSizeInMB($filename) {
        return round(filesize('../images/' . $filename) / (1024 * 1024), 2);
      }

      // Get the total size of images from 'images' table
      $stmt = $db->prepare("SELECT * FROM images WHERE id = :filename");
      $stmt->bindParam(':filename', $filename);
      $stmt->execute();
      $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Get the total size of images from 'image_child' table
      $stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :filename");
      $stmt->bindParam(':filename', $filename);
      $stmt->execute();
      $image_childs = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $images_total_size = 0;
      foreach ($images as $image) {
        $images_total_size += getImageSizeInMB($image['filename']);
      }

      $image_child_total_size = 0;
      foreach ($image_childs as $image_child) {
        $image_child_total_size += getImageSizeInMB($image_child['filename']);
      }

      $total_size = $images_total_size + $image_child_total_size;
      ?>
      <?php foreach ($child_images as $child_image) : ?>
        <?php if (empty($child_image['filename'])) : ?>
          <div class="position-absolute top-50 start-50 translate-middle text-nowrap">
            <h1 class="fw-bold">Image not found</h1>
            <div class="d-flex justify-content-center">
              <a class="btn btn-primary fw-bold" href="/">back to home</a>
            </div>
          </div>
        <?php else : ?>
          <div class="position-relative">
            <img data-src="../images/<?php echo $child_image['filename']; ?>" class="mb-1 rounded lazy-load" style="height: 100%; width: 100%;" alt="<?php echo $image['title']; ?>">
            <div class="d-flex position-absolute bottom-0 start-0 fw-bold w-100">
              <div>
                <div class="badge ms-2 bg-dark bg-opacity-50 rounded-pill"><small><?php echo getImageSizeInMB($child_image['filename']); ?> MB</small></div>
              </div>
              <button type="button" class="btn link-body-emphasis border-0 fw-bold ms-auto my-auto text-shadow" data-bs-toggle="modal" data-bs-target="#deleteImage_<?php echo $child_image['id']; ?>">
                <i class="bi bi-trash-fill"></i>
              </button>
            </div>
          </div>
          <div class="modal fade" id="deleteImage_<?php echo $child_image['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-body p-4 text-center">
                  <h5 class="mb-0">Delete this image "<?php echo $child_image['filename']; ?>"?</h5>
                  <p class="mb-0">This action can't be undone</p>
                </div>
                <form method="POST" action="delete_image_child.php">
                  <div class="modal-footer flex-nowrap p-0">
                    <input type="hidden" name="image_id" value="<?php echo $child_image['id']; ?>">
                    <button type="submit" class="btn btn-lg btn-link fs-6 text-danger text-decoration-none col-6 py-3 m-0 rounded-0 border-end"><strong>Yes, delete</strong></button>
                    <button type="button" class="btn btn-lg btn-link fs-6 text-decoration-none col-6 py-3 m-0 rounded-0" data-bs-dismiss="modal">Cancel</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <style>
      .text-shadow {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);
      }
    </style>
    <script>
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      let imageContainer = document.getElementById("image-container");

      // Set the default placeholder image
      const defaultPlaceholder = "../../icon/bg.png";

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
