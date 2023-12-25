<?php
require_once('../../auth.php');

// Connect to SQLite database
$db = new SQLite3('../../database.sqlite');

$email = $_SESSION['email'];

// Create video table if not exists
$query = "CREATE TABLE IF NOT EXISTS videos (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  video TEXT,
  email TEXT,
  thumb TEXT,
  title TEXT,
  description TEXT,
  date DATETIME,
  view_count INT DEFAULT 0
)";
$db->exec($query);

// Function to resize image to default size and maintain 16:9 aspect ratio
function resizeImage($sourceFile, $targetFile, $width, $height) {
  $targetAspectRatio = 16 / 9; // Set the desired aspect ratio

  list($sourceWidth, $sourceHeight, $sourceType) = getimagesize($sourceFile);

  $sourceAspectRatio = $sourceWidth / $sourceHeight;

  if ($sourceAspectRatio > $targetAspectRatio) {
    $targetHeight = $height;
    $targetWidth = $height * $targetAspectRatio;
  } else {
    $targetWidth = $width;
    $targetHeight = $width / $targetAspectRatio;
  }

  $sourceImage = imagecreatefromjpeg($sourceFile); // Assumes JPG, change accordingly
  $targetImage = imagecreatetruecolor($width, $height);

  $offsetX = 0;
  $offsetY = 0;

  if ($sourceAspectRatio > $targetAspectRatio) {
    $offsetX = ($targetWidth - $width) / 2;
  } elseif ($sourceAspectRatio < $targetAspectRatio) {
    $offsetY = ($targetHeight - $height) / 2;
  }

  imagecopyresampled($targetImage, $sourceImage, -$offsetX, -$offsetY, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

  imagejpeg($targetImage, $targetFile); // Assumes JPG, change accordingly

  imagedestroy($sourceImage);
  imagedestroy($targetImage);
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Check if the form was submitted
  if (
    isset($_FILES['image']) && !empty($_FILES['image']['name']) &&
    isset($_FILES['videoFile']) && !empty($_FILES['videoFile']['name']) &&
    isset($_POST['title']) && !empty($_POST['title'])
  ) {
    $uploadDir = 'videos/';
    $coverDir = 'thumbnails/';

    if (!file_exists($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }
    if (!file_exists($coverDir)) {
      mkdir($coverDir, 0777, true);
    }

    $originalImageName = basename($_FILES['image']['name']);
    $imageExtension = pathinfo($originalImageName, PATHINFO_EXTENSION);

    if (in_array(strtolower($imageExtension), ['jpg', 'jpeg', 'png'])) {
      $uniqueImageName = uniqid() . '.' . $imageExtension;
      $imageFile = $uploadDir . $uniqueImageName;
      $coverFile = 'cover_' . $uniqueImageName;

      if (move_uploaded_file($_FILES['image']['tmp_name'], $imageFile)) {
        resizeImage($imageFile, $coverDir . $coverFile, 1262, 1262);
      } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to upload the image.']);
        exit;
      }
    } else {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, and PNG are allowed.']);
      exit;
    }

    $originalVideoName = basename($_FILES['videoFile']['name']);
    $videoExtension = pathinfo($originalVideoName, PATHINFO_EXTENSION);

    if (strtolower($videoExtension) === 'mp4') {
      $uniqueVideoName = uniqid() . '.' . $videoExtension;
      $videoFile = $uploadDir . $uniqueVideoName;

      if (!move_uploaded_file($_FILES['videoFile']['tmp_name'], $videoFile)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to upload the video file.']);
        exit;
      }
    } else {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Invalid video file type. Only MP4 is allowed.']);
      exit;
    }

    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = nl2br(filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    $stmt = $db->prepare("INSERT INTO videos (video, email, thumb, title, description, date) VALUES (:video, :email, :thumb, :title, :description, :date)");
    $stmt->bindValue(':video', $videoFile, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':thumb', $coverFile, SQLITE3_TEXT);
    $stmt->bindValue(':title', $title, SQLITE3_TEXT);
    $stmt->bindValue(':description', $description, SQLITE3_TEXT);
    $stmt->bindValue(':date', date('Y-m-d H:i:s'), SQLITE3_TEXT);

    $stmt->execute();

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;

  } else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required input data.']);
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload</title>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container-fluid mt-3">
      <nav aria-label="breadcrumb">
        <div class="d-none d-md-block d-lg-block">
          <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-3" style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);">
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">
                ArtCODE
              </a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/minutes/">Home</a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none fw-bold" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/minutes/upload.php">Upload</a>
            </li>
          </ol>
        </div>
        <div class="d-md-none d-lg-none">
          <a class="btn bg-body-tertiary p-3 fw-bold w-100 text-start mb-2" data-bs-toggle="collapse" href="#collapseModal" role="button" aria-expanded="false" aria-controls="collapseExample">
            <i class="bi bi-list" style="-webkit-text-stroke: 1px;"></i> Menu
          </a>
          <div class="collapse bg-body-tertiary mb-2 rounded" id="collapseModal">
            <div class="btn-group-vertical w-100">
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">ArtCODE</a>
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/minutes/">Home</a>
              <a class="btn py-2 rounded text-start fw-bold" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/minutes/upload.php"><i class="bi bi-chevron-right small" style="-webkit-text-stroke: 2px;"></i> Upload</a>
            </div>
          </div>
        </div>
      </nav>
      <form id="uploadForm" enctype="multipart/form-data" action="upload.php" method="POST">
        <div class="row">
          <div class="col-md-4 mb-2 pe-md-1">
            <div class="ratio ratio-1x1">
              <div id="file-preview-container" class="d-flex align-items-center justify-content-center h-100 border border-3 rounded-4">
                <div class="text-center">
                  <h6><i class="bi bi-image fs-1"></i></h6>
                  <h6>Your image cover here!</h6>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-8 ps-md-1">
            <div class="row">
              <div class="col-md-6 pe-md-1">
                <div class="mb-2">
                  <label for="file-ip-1" class="form-label">Select Cover Image</label>
                  <input class="form-control border border-3 rounded-4 mb-2" type="file" name="image" type="file" id="file-ip-1" accept="image/*" onchange="showPreview(event);" required>
                </div>
              </div>
              <div class="col-md-6 ps-md-1">
                <div class="mb-2">
                  <label for="file-ip-1" class="form-label">Select File</label>
                  <input type="file" class="form-control border border-3 rounded-4" id="videoFile" name="videoFile" accept=".mp4" required>
                </div>
              </div>
            </div>
            <div class="form-floating mb-2">
              <input class="form-control border border-3 rounded-4" type="text" id="title" placeholder="title" name="title" required>
              <label class="fw-medium" for="title">title</label>
            </div>
            <div class="form-floating mb-2">
              <textarea class="form-control border border-3 rounded-4 vh-100" id="description" placeholder="description" name="description"></textarea>
              <label class="fw-medium" for="album">description</label>
            </div>
            <div class="mb-2">
              <div class="progress fw-bold rounded-4" style="display: none; height: 45px;">
                <div id="progressBar" class="progress-bar progress-bar-animated bg-primary text-white" role="progressbar" style="height: 45px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold border-primary-subtle border border-3 rounded-4" onclick="uploadFile()">upload</button>
            </div>
          </div>
      </form>
    </div>
    <div class="mt-5"></div>
    <script>
      function uploadFile() {
        event.preventDefault(); // Prevent default form submission

        var form = document.getElementById('uploadForm');
        var formData = new FormData(form);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'upload.php', true); // Specify the correct path to the PHP file

        xhr.upload.onprogress = function (event) {
          if (event.lengthComputable) {
            var percentComplete = Math.round((event.loaded / event.total) * 100);
            document.getElementById('progressBar').style.width = percentComplete + '%';
            document.getElementById('progressBar').innerText = percentComplete + '%';
          }
        };

        xhr.onloadend = function () {
          // Hide progress bar when the upload is complete or failed
          document.querySelector('.progress').style.display = 'none';
        };

        xhr.onreadystatechange = function () {
          if (xhr.readyState == 4) {
            if (xhr.status == 200) {
              var response = JSON.parse(xhr.responseText);
              if (response.success) {
                alert('Upload successful!');
                // You can redirect or perform other actions as needed
              } else {
                alert('Upload failed!');
              }
            } else {
              alert('Error during upload. Please try again.');
            }
          }
        };

        // Show progress bar before sending the request
        document.querySelector('.progress').style.display = 'block';
    
        xhr.send(formData);
      }

      function showPreview(event) {
        var fileInput = event.target;
        var previewContainer = document.getElementById("file-preview-container");

        if (fileInput.files.length > 0) {
          // Create an image element
          var img = document.createElement("img");
          img.classList.add("d-block", "object-fit-cover");
          img.style.borderRadius = "0.85em";
          img.style.width = "100%";
          img.style.height = "100%";

          // Set the image source
          var src = URL.createObjectURL(fileInput.files[0]);
          img.src = src;

          // Remove any existing content in the preview container
          previewContainer.innerHTML = "";

          // Append the image to the preview container
          previewContainer.appendChild(img);
        } else {
          // If no file is selected, show the Bootstrap icon
          previewContainer.innerHTML = '<div class="text-center"><h6><i class="bi bi-image fs-1"></i></h6><h6>Your image cover here!</h6></div>';
        }
      }
    </script>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
