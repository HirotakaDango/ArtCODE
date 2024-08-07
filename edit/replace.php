<?php
require_once('../auth.php');

// Connect to SQLite database
$db = new SQLite3('../database.sqlite');

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  // Redirect to index.php if not logged in
  header("Location: ../index.php");
  exit;
}

// Function to delete previous images
function deletePreviousImages($filename) {
  $previousImage = '../images/' . $filename;
  $previousThumbnail = '../thumbnails/' . $filename;

  if (file_exists($previousImage)) {
    unlink($previousImage);
  }

  if (file_exists($previousThumbnail)) {
    unlink($previousThumbnail);
  }
}

// Retrieve image details
if (isset($_GET['id'])) {
  $id = $_GET['id'];

  // Retrieve the email of the logged-in user
  $email = $_SESSION['email'];

  // Select the image details using the image ID and the email of the logged-in user
  $stmt = $db->prepare('SELECT * FROM images WHERE id = :id AND email = :email');
  $stmt->bindParam(':id', $id);
  $stmt->bindParam(':email', $email);
  $result = $stmt->execute();
  $image = $result->fetchArray(SQLITE3_ASSOC); // Retrieve result as an associative array

  // Check if the image exists and belongs to the logged-in user
  if (!$image) {
    echo '<meta charset="UTF-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <img src="../icon/403-Error-Forbidden.svg" style="height: 100%; width: 100%;">';
    exit();
  }
} else {
  // Redirect to the error page if the image ID is not specified
  header('Location: ?id=' . $id);
  exit();
}

// Handle image update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Check if a file was uploaded
  if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    // Delete previous images
    if (!empty($image['filename'])) {
      deletePreviousImages($image['filename']);
    }

    $uploadDir = '../images/';
    $uploadFile = $uploadDir . basename($_FILES['image']['name']);

    // Move the uploaded file to the destination directory
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
      // Generate a unique file name for the random image
      $ext = pathinfo($uploadFile, PATHINFO_EXTENSION);
      $filename = uniqid() . '.' . $ext;

      // Copy the original image to the "images" folder
      copy($uploadFile, $uploadDir . $filename);

      // Determine the image type and generate the thumbnail
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
          imagejpeg($thumbnail, '../thumbnails/' . $filename);
          break;
        case 'png':
          imagepng($thumbnail, '../thumbnails/' . $filename);
          break;
        case 'gif':
          imagegif($thumbnail, '../thumbnails/' . $filename);
          break;
        case 'webp':
          imagewebp($thumbnail, '../thumbnails/' . $filename);
          break;
        case 'avif':
          imageavif($thumbnail, '../thumbnails/' . $filename);
          break;
        case 'bmp':
          imagebmp($thumbnail, '../thumbnails/' . $filename);
          break;
        case 'wbmp':
          imagewbmp($thumbnail, '../thumbnails/' . $filename);
          break;
        default:
          echo "Error: Unsupported image format.";
          exit;
      }

      // Update the image details in the database
      $stmt = $db->prepare('UPDATE images SET filename = :filename WHERE id = :id');
      $stmt->bindParam(':filename', $filename);
      $stmt->bindParam(':id', $id);
      $stmt->execute();

      // Redirect to the image details page after the update
      header('Location: ?id=' . $id);
      exit();
    } else {
      echo 'Error uploading file.';
    }
  } else {
    echo 'No file uploaded.';
  }
}

// Function to create a thumbnail
function createThumbnail($filePath, $width, $height) {
  $source = imagecreatefromstring(file_get_contents($filePath));
  $thumbnail = imagecreatetruecolor($width, $height);
  imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));

  return $thumbnail;
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
        var coverImage = document.getElementById("coverImage");

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
          // Show the existing cover image
          <?php if (!empty($image['filename'])): ?>
            previewContainer.innerHTML = '<img src="../thumbnails/<?php echo $image['filename']; ?>" style="border-radius: 0.85em; height: 100%; width: 100%;" class="d-block object-fit-cover">';
          <?php else: ?>
            // If no file is selected, show the default content
            previewContainer.innerHTML = '<div class="text-center"><h6><i class="bi bi-image fs-1"></i></h6><h6>Your image cover here!</h6></div>';
          <?php endif; ?>
        }
      }
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>