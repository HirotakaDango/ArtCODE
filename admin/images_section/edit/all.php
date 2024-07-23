<?php
// admin/images_section/edit/index.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();

// Connect to SQLite database
$db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/database.sqlite');

// Check if the user is logged in
session_start();
if (!isset($_SESSION['admin']['email'])) {
  // Redirect to index.php if not logged in
  header("Location: /admin/");
  exit;
}

// Get the filename from the query string
$filename = isset($_GET['id']) ? $_GET['id'] : null;

if ($filename === null) {
  // Handle the error if no id is provided
  echo 'No image ID provided.';
  exit;
}

// Get the current image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id = :id");
$stmt->bindValue(':id', $filename, SQLITE3_TEXT);
$result = $stmt->execute();
$image = $result->fetchArray(SQLITE3_ASSOC);

if ($image === false) {
  // Handle the error if the image is not found
  echo 'Image not found.';
  exit;
}

// Get the ID of the current image and the email of the owner
$image_id = $image['id'];
$email = $image['email'];

// Get all child images associated with the current image from the "image_child" table
$stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :image_id");
$stmt->bindValue(':image_id', $image_id, SQLITE3_TEXT);
$result = $stmt->execute();
$child_images = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  $child_images[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <title>Edit <?php echo $image['title']; ?></title>
    <?php include('../../../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>
  <body>
    <div class="container-fluid px-0">
      <div class="row g-0">
        <div class="col-auto">
          <?php include('../../admin_header.php'); ?>
        </div>
        <div class="col overflow-auto vh-100">
          <?php include('../../navbar.php'); ?>
            <div class="container-fluid mt-3 mb-5">
              <div class="d-flex justify-content-center align-items-center mb-3">
                <a class="btn bg-secondary-subtle fw-medium rounded-pill me-auto" href="redirect.php?back=<?php echo urlencode(isset($_GET['back']) ? $_GET['back'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/admin/images_section/'); ?>"><i class="bi bi-arrow-left"></i> back to section</a>
                <a class="btn bg-secondary-subtle fw-medium rounded-pill ms-auto" href="/admin/images_section/edit/?id=<?php echo $image['id']; ?>&back=<?php echo urlencode(isset($_GET['back']) ? $_GET['back'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/admin/images_section/'); ?>">back to edit</a>
              </div>
              <?php
                // Function to calculate the size of an image in MB
                function getImageSizeInMB($filename) {
                  return round(filesize($_SERVER['DOCUMENT_ROOT'] . '/images/' . $filename) / (1024 * 1024), 2);
                }
          
                // Get the total size of images from 'images' table
                $stmt = $db->prepare("SELECT * FROM images WHERE id = :filename");
                $stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
                $result = $stmt->execute();
                $images = [];
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                  $images[] = $row;
                }
          
                // Get the total size of images from 'image_child' table
                $stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :filename");
                $stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
                $result = $stmt->execute();
                $image_childs = [];
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                  $image_childs[] = $row;
                }
          
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
                  <?php
                    $file_path = "../../../images/" . $child_image['filename'];
                    $file_info = stat($file_path);
                    $image_info = getimagesize($file_path);
                    $exif_data = exif_read_data($file_path, 'IFD0', true);
                  ?>
                  <div class="row mb-4">
                    <div class="col-md-6">
                      <div class="position-relative">
                        <img data-src="<?php echo $file_path; ?>" class="rounded-4 w-100 lazy-load" alt="<?php echo $image['title']; ?>">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="mt-3">
                        <h5 class="mb-3">Image Metadata</h5>
                      
                        <div class="mb-2 row">
                          <label class="col-4 col-form-label text-nowrap fw-medium">Filename</label>
                          <div class="col-8">
                            <p class="form-control-plaintext fw-bold text-white"><?php echo $child_image['filename']; ?></p>
                          </div>
                        </div>
                      
                        <div class="mb-2 row">
                          <label class="col-4 col-form-label text-nowrap fw-medium">File Size</label>
                          <div class="col-8">
                            <p class="form-control-plaintext fw-bold text-white"><?php echo number_format($file_info['size'] / (1024 * 1024), 2); ?> MB</p>
                          </div>
                        </div>
                      
                        <div class="mb-2 row">
                          <label class="col-4 col-form-label text-nowrap fw-medium">Dimensions</label>
                          <div class="col-8">
                            <p class="form-control-plaintext fw-bold text-white"><?php echo $image_info[0] . 'x' . $image_info[1]; ?> pixels</p>
                          </div>
                        </div>
                      
                        <div class="mb-2 row">
                          <label class="col-4 col-form-label text-nowrap fw-medium">MIME Type</label>
                          <div class="col-8">
                            <p class="form-control-plaintext fw-bold text-white"><?php echo $image_info['mime']; ?></p>
                          </div>
                        </div>
            
                        <div class="mb-2 row">
                          <label class="col-4 col-form-label text-nowrap fw-medium">Creation Date</label>
                          <div class="col-8">
                            <p class="form-control-plaintext fw-bold text-white"><?php echo date("l, d F, Y", $file_info['ctime']); ?></p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
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
    <?php include('../../../bootstrapjs.php'); ?>
  </body>
</html>