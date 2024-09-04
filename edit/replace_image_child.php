<?php
require_once('../auth.php');

// Connect to SQLite database
$db = new SQLite3('../database.sqlite');

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  header("Location: ../index.php");
  exit;
}

// Function to delete previous images
function deletePreviousImages($filename) {
  $dateFolder = date('Y/m/d');
  $previousImage = '../images/' . $dateFolder . '/' . $filename;
  $previousThumbnail = '../thumbnails/' . $dateFolder . '/' . $filename;

  if (file_exists($previousImage)) {
    unlink($previousImage);
  }

  if (file_exists($previousThumbnail)) {
    unlink($previousThumbnail);
  }
}

// Retrieve image details with title from images table
if (isset($_GET['id']) && isset($_GET['child_id'])) {
  $id = $_GET['id'];
  $child_id = $_GET['child_id'];

  $email = $_SESSION['email'];
  $stmt = $db->prepare('
    SELECT ic.*, i.title 
    FROM image_child ic
    JOIN images i ON ic.image_id = i.id
    WHERE ic.id = :child_id AND ic.email = :email
  ');
  $stmt->bindValue(':child_id', $child_id);
  $stmt->bindValue(':email', $email);
  $result = $stmt->execute();
  $image = $result->fetchArray(SQLITE3_ASSOC);

  if (!$image) {
    echo '<meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1">
          <img src="../icon/403-Error-Forbidden.svg" style="height: 100%; width: 100%;">';
    exit();
  }
} else {
  // Redirect if id or child_id is missing
  header('Location: all.php?id=' . urlencode($id) . '&child_id=' . urlencode($child_id) . '&page=' . urlencode($_GET['page']));
  exit();
}

// Handle image update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    if (!empty($image['filename'])) {
      deletePreviousImages($image['filename']);
    }

    $dateFolder = date('Y/m/d');
    $uploadDir = '../images/' . $dateFolder . '/';
    $thumbnailDir = '../thumbnails/' . $dateFolder . '/';

    // Create directories if they don't exist
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0755, true);
    }
    if (!is_dir($thumbnailDir)) {
      mkdir($thumbnailDir, 0755, true);
    }

    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;
    $originalFilename = basename($_FILES['image']['name']);
    $uploadFile = $uploadDir . $filename;

    // Move the uploaded file to the destination directory
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
      // Generate thumbnail
      $image_info = getimagesize($uploadFile);
      $mime_type = $image_info['mime'];
      switch ($mime_type) {
        case 'image/jpeg':
          $source = imagecreatefromjpeg($uploadFile);
          break;
        case 'image/png':
          $source = imagecreatefrompng($uploadFile);
          break;
        case 'image/gif':
          $source = imagecreatefromgif($uploadFile);
          break;
        case 'image/webp':
          $source = imagecreatefromwebp($uploadFile);
          break;
        case 'image/avif':
          $source = imagecreatefromavif($uploadFile);
          break;
        case 'image/bmp':
          $source = imagecreatefrombmp($uploadFile);
          break;
        case 'image/wbmp':
          $source = imagecreatefromwbmp($uploadFile);
          break;
        default:
          echo "Error: Unsupported image format.";
          exit;
      }

      if ($source === false) {
        echo "Error: Failed to create image source.";
        exit;
      }

      $original_width = imagesx($source);
      $original_height = imagesy($source);
      $ratio = $original_width / $original_height;
      $thumbnail_width = 300;
      $thumbnail_height = intval(300 / $ratio);

      $thumbnail = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
      if ($thumbnail === false) {
        echo "Error: Failed to create thumbnail.";
        exit;
      }

      imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $original_width, $original_height);

      // Save the thumbnail
      switch ($ext) {
        case 'jpg':
        case 'jpeg':
          imagejpeg($thumbnail, $thumbnailDir . $filename);
          break;
        case 'png':
          imagepng($thumbnail, $thumbnailDir . $filename);
          break;
        case 'gif':
          imagegif($thumbnail, $thumbnailDir . $filename);
          break;
        case 'webp':
          imagewebp($thumbnail, $thumbnailDir . $filename);
          break;
        case 'avif':
          imageavif($thumbnail, $thumbnailDir . $filename);
          break;
        case 'bmp':
          imagebmp($thumbnail, $thumbnailDir . $filename);
          break;
        case 'wbmp':
          imagewbmp($thumbnail, $thumbnailDir . $filename);
          break;
        default:
          echo "Error: Unsupported image format.";
          exit;
      }

      // Update the image details in the database
      $stmt = $db->prepare('UPDATE image_child SET filename = :filename, original_filename = :original_filename WHERE id = :child_id');
      $stmt->bindValue(':filename', $dateFolder . '/' . $filename, SQLITE3_TEXT);
      $stmt->bindValue(':original_filename', $originalFilename, SQLITE3_TEXT);
      $stmt->bindValue(':child_id', $child_id, SQLITE3_INTEGER);
      $stmt->execute();

      header('Location: all.php?id=' . urlencode($id) . '&child_id=' . urlencode($child_id) . '&page=' . urlencode($_GET['page']));
      exit();
    } else {
      echo 'Error uploading file.';
    }
  } else {
    echo 'No file uploaded.';
  }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Replace Main Image of <?php echo $image['title']; ?></title>
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <div class="container">
      <?php include('nav.php'); ?>
      <div class="row">
        <div class="col-md-6 pe-md-1 mb-2">
          <a data-bs-toggle="modal" data-bs-target="#originalImage">
            <div id="file-preview-container" class="d-flex align-items-center justify-content-center h-100 border border-3 rounded-4">
              <?php if (!empty($image['filename'])): ?>
                <img src="../thumbnails/<?php echo $image['filename']; ?>" style="border-radius: 0.85em; height: 100%; width: 100%;" class="d-block object-fit-cover" id="coverImage">
              <?php else: ?>
                <div class="text-center">
                  <h6><i class="bi bi-image fs-1"></i></h6>
                  <h6>Your image cover here!</h6>
                </div>
              <?php endif; ?>
            </div>
          </a>
        </div>
        <div class="col-md-6 ps-md-1">
          <form action="" method="post" enctype="multipart/form-data" oninput="showPreview(event)">
            <input class="form-control border border-dark-subtle border-3 rounded-4 mb-2" type="file" id="image" name="image" accept="image/*" required>
            <button class="btn btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold text-nowrap border border-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>-subtle border-3 rounded-4 w-100" type="submit">save changes</button>
          </form>
        </div>
      </div>
    </div>
    <div class="mt-5"></div>
    <div class="modal fade" id="originalImage" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="modal-body position-relative">
            <img class="object-fit-contain h-100 w-100 rounded" src="../images/<?php echo $image['filename']; ?>">
            <button type="button" class="btn border-0 position-absolute end-0 top-0 m-2" data-bs-dismiss="modal"><i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i></button>
          </div>
        </div>
      </div>
    </div>
    <script>
      function showPreview(event) {
        var fileInput = event.target;
        var previewContainer = document.getElementById("file-preview-container");

        if (fileInput.files.length > 0) {
          var img = document.createElement("img");
          img.src = URL.createObjectURL(fileInput.files[0]);
          img.classList.add("d-block", "object-fit-cover");
          img.style.borderRadius = "0.85em";
          img.style.width = "100%";
          img.style.height = "100%";
          previewContainer.innerHTML = "";
          previewContainer.appendChild(img);
        } else {
          // Show the existing cover image or default content if no file is selected
          var currentImage = "<?php echo !empty($image['filename']) ? '../thumbnails/' . htmlspecialchars($image['filename']) : ''; ?>";
          if (currentImage) {
            previewContainer.innerHTML = '<img src="' + currentImage + '" style="border-radius: 0.85em; height: 100%; width: 100%;" class="d-block object-fit-cover">';
          } else {
            previewContainer.innerHTML = '<div class="text-center"><h6><i class="bi bi-image fs-1"></i></h6><h6>Your image cover here!</h6></div>';
          }
        }
      }
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>