<?php
// admin/images_section/edit/index.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();

// Connect to SQLite database
$db = new PDO('sqlite:' . $_SERVER['DOCUMENT_ROOT'] . '/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check if the user is logged in
if (!isset($_SESSION['admin']['email'])) {
  // Redirect to index.php if not logged in
  header("Location: /admin/");
  exit;
}

$email = $_SESSION['admin']['email']; // Get the logged-in user's email

// Get the filename from the query string
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get the current image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$image = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$image) {
  // Handle case where image is not found
  echo "Image not found.";
  exit;
}

// Get the ID of the current image and the email of the owner
$image_id = $image['id'];

// Prepare the query to get the user's numpage
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindParam(':email', $email, PDO::PARAM_STR);
$queryNum->execute();
$user = $queryNum->fetch(PDO::FETCH_ASSOC);

$numpage = $user['numpage'];

// Pagination settings
$imagesPerPage = empty($numpage) ? 50 : $numpage;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $imagesPerPage;

// Get all child images associated with the current image from the "image_child" table
$stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :image_id LIMIT :start, :imagesPerPage");
$stmt->bindParam(':image_id', $image_id, PDO::PARAM_INT);
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':imagesPerPage', $imagesPerPage, PDO::PARAM_INT);
$stmt->execute();
$child_images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count the total number of child images
$stmt = $db->prepare("SELECT COUNT(*) FROM image_child WHERE image_id = :image_id");
$stmt->bindParam(':image_id', $image_id, PDO::PARAM_INT);
$stmt->execute();
$totalImages = $stmt->fetchColumn();
$totalPages = ceil($totalImages / $imagesPerPage);

function generateThumbnail($filePath, $thumbnailPath) {
  // Ensure the source file exists
  if (!file_exists($filePath)) {
    error_log("Source file does not exist: " . $filePath);
    return false;
  }

  // Get the image information
  $image_info = getimagesize($filePath);
  if (!$image_info) {
    error_log("Unable to get image info for: " . $filePath);
    return false;
  }

  $mime_type = $image_info['mime'];
  $source = false;

  // Create image resource from file
  switch ($mime_type) {
    case 'image/jpeg':
      $source = imagecreatefromjpeg($filePath);
      break;
    case 'image/png':
      $source = imagecreatefrompng($filePath);
      break;
    case 'image/gif':
      $source = imagecreatefromgif($filePath);
      break;
    case 'image/webp':
      $source = imagecreatefromwebp($filePath);
      break;
    case 'image/avif':
      $source = imagecreatefromavif($filePath);
      break;
    default:
      error_log("Unsupported image type: " . $mime_type);
      return false;
  }

  if ($source === false) {
    error_log("Failed to create image resource from: " . $filePath);
    return false;
  }

  $original_width = imagesx($source);
  $original_height = imagesy($source);
  $thumbnail_width = 300;
  $thumbnail_height = intval($thumbnail_width * $original_height / $original_width);

  $thumbnail = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
  if ($thumbnail === false) {
    error_log("Failed to create thumbnail resource");
    imagedestroy($source);
    return false;
  }

  // Preserve transparency for PNG and GIF
  if ($mime_type == 'image/png' || $mime_type == 'image/gif') {
    imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
    imagealphablending($thumbnail, false);
    imagesavealpha($thumbnail, true);
  }

  // Copy and resize the original image
  if (!imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $original_width, $original_height)) {
    error_log("Failed to resize image: " . $filePath);
    imagedestroy($source);
    imagedestroy($thumbnail);
    return false;
  }

  // Save the thumbnail
  $result = false;
  switch (pathinfo($filePath, PATHINFO_EXTENSION)) {
    case 'jpg':
    case 'jpeg':
      $result = imagejpeg($thumbnail, $thumbnailPath);
      break;
    case 'png':
      $result = imagepng($thumbnail, $thumbnailPath);
      break;
    case 'gif':
      $result = imagegif($thumbnail, $thumbnailPath);
      break;
    case 'webp':
      $result = imagewebp($thumbnail, $thumbnailPath);
      break;
    case 'avif':
      $result = imageavif($thumbnail, $thumbnailPath);
      break;
  }

  if (!$result) {
    error_log("Failed to save thumbnail: " . $thumbnailPath);
  }

  // Clean up resources
  imagedestroy($source);
  imagedestroy($thumbnail);

  return $result;
}

// Generate thumbnails if they do not exist
foreach ($child_images as $child_image) {
  $file_path = "../../../images/" . $child_image['filename'];
  $thumbnail_path = "../../../thumbnails/" . $child_image['filename'];

  // Create the thumbnail directory if it doesn't exist
  $thumbnailDir = dirname($thumbnail_path);
  if (!is_dir($thumbnailDir)) {
    mkdir($thumbnailDir, 0755, true);
  }

  // Check if the thumbnail exists
  if (!file_exists($thumbnail_path)) {
    if (!generateThumbnail($file_path, $thumbnail_path)) {
      error_log("Error generating thumbnail for " . $child_image['filename']);
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <title>All Images From <?php echo $image['title']; ?></title>
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
          <div>
            <div class="container-fluid mt-3 mb-5">
              <div class="d-flex justify-content-center align-items-center mb-3">
                <a class="btn bg-secondary-subtle fw-medium rounded-pill me-auto" href="redirect.php?back=<?php echo urlencode(isset($_GET['back']) ? $_GET['back'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/admin/images_section/'); ?>"><i class="bi bi-arrow-left"></i> back to section</a>
                <a class="btn bg-secondary-subtle fw-medium rounded-pill ms-auto" href="/admin/images_section/edit/?id=<?php echo $image['id']; ?>&back=<?php echo urlencode(isset($_GET['back']) ? $_GET['back'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/admin/images_section/'); ?>">back to edit</a>
              </div>
              <?php
              // Function to calculate the size of an image in MB
              function getImageSizeInMB($filePath) {
                return round(filesize($filePath) / (1024 * 1024), 2);
              }
              
              // Connect to SQLite database
              $db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/database.sqlite');
              
              // Get the filename from the query string
              $id = $_GET['id'];
              
              // Get the total size of images from 'images' table
              $stmt = $db->prepare("SELECT * FROM images WHERE id = :id");
              $stmt->bindValue(':id', $id, SQLITE3_TEXT);
              $result = $stmt->execute();
              $images = [];
              while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $images[] = $row;
              }
              
              // Get the total size of images from 'image_child' table
              $stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :id");
              $stmt->bindValue(':id', $id, SQLITE3_TEXT);
              $result = $stmt->execute();
              $image_childs = [];
              while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $image_childs[] = $row;
              }
              
              $images_total_size = 0;
              foreach ($images as $image) {
                $filePath = '../../../images/' . $image['filename'];
                if (file_exists($filePath)) {
                  $images_total_size += getImageSizeInMB($filePath);
                }
              }
              
              $image_child_total_size = 0;
              foreach ($image_childs as $image_child) {
                $filePath = '../../../images/' . $image_child['filename'];
                if (file_exists($filePath)) {
                  $image_child_total_size += getImageSizeInMB($filePath);
                }
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
                  $exif_data = [];
          
                  // Check if the file is a supported image type before reading EXIF data
                  if ($image_info && $image_info['mime'] === 'image/jpeg') {
                    $exif_data = exif_read_data($file_path, 'IFD0', true);
                  }
                  ?>
                  <div class="row mb-4">
                    <div class="col-md-6">
                      <div class="position-relative">
                        <a data-bs-toggle="modal" data-bs-target="#originalImage_<?php echo urlencode($child_image['id']); ?>"><img data-src="../../../thumbnails/<?php echo $child_image['filename']; ?>" class="rounded-4 w-100 lazy-load" alt="<?php echo $image['title']; ?>"></a>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="mt-3">
                        <h5 class="mb-3">Image Metadata</h5>
          
                        <div class="mb-2 row">
                          <label class="col-4 col-form-label text-nowrap fw-medium">Filename</label>
                          <div class="col-8">
                            <input type="text" readonly class="form-control-plaintext fw-bold text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" value="<?php echo $child_image['filename']; ?>">
                          </div>
                        </div>
          
                        <div class="mb-2 row">
                          <label class="col-4 col-form-label text-nowrap fw-medium">Original</label>
                          <div class="col-8">
                            <input type="text" readonly class="form-control-plaintext fw-bold text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" value="<?php echo $child_image['original_filename']; ?>">
                          </div>
                        </div>
                        
                        <div class="mb-2 row">
                          <label class="col-4 col-form-label text-nowrap fw-medium">File Size</label>
                          <div class="col-8">
                            <input type="text" readonly class="form-control-plaintext fw-bold text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" value="<?php echo number_format($file_info['size'] / (1024 * 1024), 2); ?> MB">
                          </div>
                        </div>
                        
                        <div class="mb-2 row">
                          <label class="col-4 col-form-label text-nowrap fw-medium">Dimensions</label>
                          <div class="col-8">
                            <input type="text" readonly class="form-control-plaintext fw-bold text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" value="<?php echo $image_info[0] . 'x' . $image_info[1]; ?> pixels">
                          </div>
                        </div>
                        
                        <div class="mb-2 row">
                          <label class="col-4 col-form-label text-nowrap fw-medium">MIME Type</label>
                          <div class="col-8">
                            <input type="text" readonly class="form-control-plaintext fw-bold text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" value="<?php echo $image_info['mime']; ?>">
                          </div>
                        </div>
                        
                        <div class="mb-2 row">
                          <label class="col-4 col-form-label text-nowrap fw-medium">Creation Date</label>
                          <div class="col-8">
                            <input type="text" readonly class="form-control-plaintext fw-bold text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" value="<?php echo date("l, d F, Y", $file_info['ctime']); ?>">
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>
                  <div class="modal fade" id="originalImage_<?php echo urlencode($child_image['id']); ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                      <div class="modal-content bg-transparent border-0 rounded-0">
                        <div class="modal-body position-relative">
                          <img class="object-fit-contain h-100 w-100 rounded lazy-load" data-src="../../../images/<?php echo $child_image['filename']; ?>">
                          <button type="button" class="btn border-0 position-absolute end-0 top-0 m-2" data-bs-dismiss="modal"><i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i></button>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="modal fade" id="deleteImage_<?php echo urlencode($child_image['id']); ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
            <div class="pagination d-flex gap-1 justify-content-center mt-3">
              <?php if ($page > 1): ?>
                <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, ['page' => 1])); ?>">
                  <i class="bi text-stroke bi-chevron-double-left"></i>
                </a>
              <?php endif; ?>
        
              <?php if ($page > 1): ?>
                <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                  <i class="bi text-stroke bi-chevron-left"></i>
                </a>
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
                    echo '<a class="btn btn-sm btn-primary fw-bold" href="' . $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, ['page' => $i])) . '">' . $i . '</a>';
                  }
                }
              ?>
        
              <?php if ($page < $totalPages): ?>
                <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                  <i class="bi text-stroke bi-chevron-right"></i>
                </a>
              <?php endif; ?>
        
              <?php if ($page < $totalPages): ?>
                <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>">
                  <i class="bi text-stroke bi-chevron-double-right"></i>
                </a>
              <?php endif; ?>
            </div>
            <div class="mt-5"></div>
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