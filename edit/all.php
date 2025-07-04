<?php
require_once('../auth.php');

$db = new PDO('sqlite:../database.sqlite');

if (!isset($_SESSION['email'])) {
  header("Location: ../index.php");
  exit;
}

$id = $_GET['id'];

$stmt = $db->prepare("SELECT * FROM images WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$image = $stmt->fetch();

$image_id = $image['id'];
$email = $image['email'];

$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindParam(':email', $email, PDO::PARAM_STR);
$queryNum->execute();
$user = $queryNum->fetch(PDO::FETCH_ASSOC);

$numpage = $user['numpage'];

$imagesPerPage = empty($numpage) ? 50 : $numpage;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $imagesPerPage;

$stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :image_id LIMIT :start, :limit");
$stmt->bindParam(':image_id', $image_id);
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $imagesPerPage, PDO::PARAM_INT);
$stmt->execute();
$child_images = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT COUNT(*) FROM image_child WHERE image_id = :image_id");
$stmt->bindParam(':image_id', $image_id);
$stmt->execute();
$totalImages = $stmt->fetchColumn();
$totalPages = ceil($totalImages / $imagesPerPage);

function generateThumbnail($filePath, $thumbnailPath) {
  if (!file_exists($filePath)) {
    error_log("Source file does not exist: " . $filePath);
    return false;
  }

  $image_info = getimagesize($filePath);
  if (!$image_info) {
    error_log("Unable to get image info for: " . $filePath);
    return false;
  }

  $mime_type = $image_info['mime'];
  $source = false;

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

  if ($mime_type == 'image/png' || $mime_type == 'image/gif') {
    imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
    imagealphablending($thumbnail, false);
    imagesavealpha($thumbnail, true);
  }

  if (!imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $original_width, $original_height)) {
    error_log("Failed to resize image: " . $filePath);
    imagedestroy($source);
    imagedestroy($thumbnail);
    return false;
  }

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

  imagedestroy($source);
  imagedestroy($thumbnail);

  return $result;
}

foreach ($child_images as $child_image) {
  $file_path = "../images/" . $child_image['filename'];
  $thumbnail_path = "../thumbnails/" . $child_image['filename'];

  $thumbnailDir = dirname($thumbnail_path);
  if (!is_dir($thumbnailDir)) {
    mkdir($thumbnailDir, 0755, true);
  }

  if (!file_exists($thumbnail_path)) {
    if (!generateThumbnail($file_path, $thumbnail_path)) {
      error_log("Error generating thumbnail for " . $child_image['filename']);
    }
  }
}

// Prepare theme variables for use in the HTML
ob_start();
include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php');
$theme_mode = ob_get_clean();

ob_start();
include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php');
$opposite_theme = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo $theme_mode; ?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>All Images From <?php echo htmlspecialchars($image['title']); ?></title>
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <div class="container mb-5">
      <?php include('nav.php'); ?>
      <?php
      function getImageSizeInMB($filename) {
        $filepath = '../images/' . $filename;
        if (file_exists($filepath)) {
          return round(filesize($filepath) / (1024 * 1024), 2);
        }
        return 0;
      }

      // Calculate total size of parent image
      $images_total_size = getImageSizeInMB($image['filename']);

      // Get all child image records for size calculation
      $stmt = $db->prepare("SELECT filename FROM image_child WHERE image_id = :id");
      $stmt->bindParam(':id', $id);
      $stmt->execute();
      $all_child_images = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $image_child_total_size = 0;
      foreach ($all_child_images as $child) {
        $image_child_total_size += getImageSizeInMB($child['filename']);
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
          $file_path = "../images/" . $child_image['filename'];
          $file_info = stat($file_path);
          $image_info = getimagesize($file_path);
          $exif_data = [];
          
          if ($image_info && $image_info['mime'] === 'image/jpeg') {
            $exif_data = @exif_read_data($file_path, 'IFD0', true);
          }
          ?>
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="position-relative">
                <a data-bs-toggle="modal" data-bs-target="#originalImage_<?php echo urlencode($child_image['id']); ?>"><img data-src="../thumbnails/<?php echo htmlspecialchars($child_image['filename']); ?>" class="rounded-4 w-100 lazy-load" alt="<?php echo htmlspecialchars($image['title']); ?>"></a>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mt-3">
                <h5 class="mb-3">Image Metadata</h5>
                <div class="mb-2 row">
                  <label class="col-4 col-form-label text-nowrap fw-medium">Filename</label>
                  <div class="col-8">
                    <input type="text" readonly class="form-control-plaintext fw-bold text-<?php echo $opposite_theme; ?>" value="<?php echo htmlspecialchars($child_image['filename']); ?>">
                  </div>
                </div>
                <div class="mb-2 row">
                  <label class="col-4 col-form-label text-nowrap fw-medium">Original</label>
                  <div class="col-8">
                    <input type="text" readonly class="form-control-plaintext fw-bold text-<?php echo $opposite_theme; ?>" value="<?php echo htmlspecialchars($child_image['original_filename']); ?>">
                  </div>
                </div>
                <div class="mb-2 row">
                  <label class="col-4 col-form-label text-nowrap fw-medium">File Size</label>
                  <div class="col-8">
                    <input type="text" readonly class="form-control-plaintext fw-bold text-<?php echo $opposite_theme; ?>" value="<?php echo number_format($file_info['size'] / (1024 * 1024), 2); ?> MB">
                  </div>
                </div>
                <div class="mb-2 row">
                  <label class="col-4 col-form-label text-nowrap fw-medium">Dimensions</label>
                  <div class="col-8">
                    <input type="text" readonly class="form-control-plaintext fw-bold text-<?php echo $opposite_theme; ?>" value="<?php echo $image_info[0] . 'x' . $image_info[1]; ?> pixels">
                  </div>
                </div>
                <div class="mb-2 row">
                  <label class="col-4 col-form-label text-nowrap fw-medium">MIME Type</label>
                  <div class="col-8">
                    <input type="text" readonly class="form-control-plaintext fw-bold text-<?php echo $opposite_theme; ?>" value="<?php echo $image_info['mime']; ?>">
                  </div>
                </div>
                <div class="mb-2 row">
                  <label class="col-4 col-form-label text-nowrap fw-medium">Creation Date</label>
                  <div class="col-8">
                    <input type="text" readonly class="form-control-plaintext fw-bold text-<?php echo $opposite_theme; ?>" value="<?php echo date("l, d F, Y", $file_info['ctime']); ?>">
                  </div>
                </div>
                <a href="replace_image_child.php?id=<?php echo urlencode($_GET['id']); ?>&child_id=<?php echo urlencode($child_image['id']); ?>&page=<?php echo isset($_GET['page']) ? urlencode($_GET['page']) : '1'; ?>" class="btn btn-sm mt-3 btn-<?php echo $opposite_theme; ?> rounded-pill fw-medium">
                  Replace Image
                </a>
                <button type="button" class="btn btn-sm mt-3 btn-<?php echo $opposite_theme; ?> rounded-pill fw-medium" data-bs-toggle="modal" data-bs-target="#deleteImage_<?php echo urlencode($child_image['id']); ?>">
                  Delete
                </button>
              </div>
            </div>
          </div>
          <div class="modal fade" id="originalImage_<?php echo urlencode($child_image['id']); ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
              <div class="modal-content bg-transparent border-0 rounded-0">
                <div class="modal-body position-relative">
                  <img class="object-fit-contain h-100 w-100 rounded lazy-load" data-src="../images/<?php echo htmlspecialchars($child_image['filename']); ?>">
                  <button type="button" class="btn border-0 position-absolute end-0 top-0 m-2" data-bs-dismiss="modal"><i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i></button>
                </div>
              </div>
            </div>
          </div>
          <div class="modal fade" id="deleteImage_<?php echo urlencode($child_image['id']); ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-body p-4 text-center">
                  <h5 class="mb-0">Delete this image "<?php echo htmlspecialchars($child_image['filename']); ?>"?</h5>
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
        $startPage = max($page - 2, 1);
        $endPage = min($page + 2, $totalPages);

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
    <style>
      .text-shadow {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);
      }
    </style>
    <script>
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      let imageContainer = document.getElementById("image-container");

      const defaultPlaceholder = "/icon/bg.png";

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
          image.src = defaultPlaceholder;
          imageObserver.observe(image);
          image.style.filter = "blur(5px)";
          image.addEventListener("load", function() {
            image.style.filter = "none";
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

      let loading = false;

      function loadMoreImages() {
        if (loading) return;
        loading = true;

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

      loadMoreImages();
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>