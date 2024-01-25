<?php
require_once('auth.php');

// Connect to SQLite database
$db = new SQLite3('../../database.sqlite');

$email = $_SESSION['email'];

// Create music table if not exists
$query = "CREATE TABLE IF NOT EXISTS music (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  file TEXT,
  email TEXT,
  cover TEXT,
  album TEXT,
  title TEXT,
  lyrics TEXT,
  description TEXT
)";
$db->exec($query);

// Function to resize image to default size and maintain 1x1 aspect ratio
function resizeImage($sourceFile, $targetFile, $width, $height) {
  list($sourceWidth, $sourceHeight, $sourceType) = getimagesize($sourceFile);

  $sourceAspectRatio = $sourceWidth / $sourceHeight;
  $targetAspectRatio = $width / $height;

  if ($sourceAspectRatio > $targetAspectRatio) {
    $targetHeight = $height;
    $targetWidth = $height * $sourceAspectRatio;
  } else {
    $targetWidth = $width;
    $targetHeight = $width / $sourceAspectRatio;
  }

  $sourceImage = imagecreatefromjpeg($sourceFile); // Assumes JPG, change accordingly
  $targetImage = imagecreatetruecolor($width, $height);

  // Resize image to fit the square canvas using object fit cover
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
    isset($_FILES['musicFile']) && !empty($_FILES['musicFile']['name']) &&
    isset($_POST['album']) && !empty($_POST['album']) &&
    isset($_POST['title']) && !empty($_POST['title'])
  ) {
    $uploadDir = 'uploads/';
    $coverDir = 'covers/';

    // Create directories if not exist
    if (!file_exists($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }
    if (!file_exists($coverDir)) {
      mkdir($coverDir, 0777, true);
    }

    // Process uploaded image
    $originalImageName = basename($_FILES['image']['name']);
    $imageExtension = pathinfo($originalImageName, PATHINFO_EXTENSION);

    // Check if the file type is JPG, JPEG, or PNG
    if (in_array(strtolower($imageExtension), ['jpg', 'jpeg', 'png'])) {
      $uniqueImageName = uniqid() . '.' . $imageExtension;
      $imageFile = $uploadDir . $uniqueImageName;
      $coverFile = 'cover_' . $uniqueImageName; // Adjusted to use only the file name

      // Move uploaded image to destination
      if (move_uploaded_file($_FILES['image']['tmp_name'], $imageFile)) {
        // Resize image to default size and maintain 1x1 aspect ratio
        resizeImage($imageFile, $coverDir . $coverFile, 1262, 1262);
      } else {
        // Output a JSON response indicating upload failure
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to upload the image.']);
        exit;
      }
    } else {
      // Output a JSON response indicating invalid file type
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, and PNG are allowed.']);
      exit;
    }

    // Process uploaded music file
    $originalMusicName = basename($_FILES['musicFile']['name']);
    $musicExtension = pathinfo($originalMusicName, PATHINFO_EXTENSION);

    if (strtolower($musicExtension) === 'mp3') {
      $uniqueMusicName = uniqid() . '.' . $musicExtension;
      $musicFile = $uploadDir . $uniqueMusicName;

      // Move uploaded music file to destination
      if (!move_uploaded_file($_FILES['musicFile']['tmp_name'], $musicFile)) {
        // Handle the case where moving the music file fails
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to upload the music file.']);
        exit;
      }
    } else {
      // Output a JSON response indicating invalid music file type
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Invalid music file type. Only MP3 is allowed.']);
      exit;
    }

    // Sanitize input data before using in SQL query
    $sanitizedAlbum = filter_var($_POST['album'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
    $sanitizedTitle = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
    $sanitizedLyrics = nl2br(filter_var($_POST['lyrics'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $sanitizedDescription = nl2br(filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    // Insert record into the database
    $stmt = $db->prepare("INSERT INTO music (file, email, cover, album, title, lyrics, description) VALUES (:file, :email, :cover, :album, :title, :lyrics, :description)");
    $stmt->bindValue(':file', $musicFile, SQLITE3_TEXT); // Use the uploaded music file
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':cover', $coverFile, SQLITE3_TEXT);
    $stmt->bindValue(':album', $sanitizedAlbum, SQLITE3_TEXT);
    $stmt->bindValue(':title', $sanitizedTitle, SQLITE3_TEXT);
    $stmt->bindValue(':lyrics', $sanitizedLyrics, SQLITE3_TEXT);
    $stmt->bindValue(':description', $sanitizedDescription, SQLITE3_TEXT);
    $stmt->execute();

    // Output a JSON response
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;

  } else {
    // If any required input data is missing, do not proceed with upload and insertion
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
              <a class="link-body-emphasis text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/music/?mode=grid&by=newest">Home</a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none fw-bold" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/music/upload.php">Upload</a>
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
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/music/?mode=grid&by=newest">Home</a>
              <a class="btn py-2 rounded text-start fw-bold" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/music/upload.php"><i class="bi bi-chevron-right small" style="-webkit-text-stroke: 2px;"></i> Upload</a>
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
                  <input type="file" class="form-control border border-3 rounded-4" id="musicFile" name="musicFile" accept=".mp3" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 pe-md-1">
                <div class="form-floating mb-2">
                  <input class="form-control border border-3 rounded-4" type="text" id="title" placeholder="title" name="title" required>
                  <label class="fw-medium" for="title">title</label>
                </div>
              </div>
              <div class="col-md-6 ps-md-1">
                <div class="form-floating mb-2">
                  <input class="form-control border border-3 rounded-4" type="text" id="album" placeholder="album" name="album" required>
                  <label class="fw-medium" for="album">album</label>
                </div>
              </div>
            </div>
            <button class="btn btn-secondary border border-secondary-subtle border-3 rounded-4 w-100 fw-bold mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDescription" aria-expanded="false" aria-controls="collapseExample">
              add description (optional)
            </button>
            <div class="collapse" id="collapseDescription">
              <div class="form-floating mb-2">
                <textarea class="form-control border border-3 rounded-4 vh-100" id="description" placeholder="description" name="description"></textarea>
                <label class="fw-medium" for="album">description</label>
              </div>
            </div>
            <button class="btn btn-secondary border border-secondary-subtle border-3 rounded-4 w-100 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLyrics" aria-expanded="false" aria-controls="collapseExample">
              add lyrics (optional)
            </button>
            <div class="collapse mt-2" id="collapseLyrics">
              <div class="form-floating mb-2">
                <textarea class="form-control border border-3 rounded-4 vh-100" id="lyrics" placeholder="lyrics" name="lyrics"></textarea>
                <label class="fw-medium" for="album">lyrics</label>
              </div>
            </div>
            <div class="my-2">
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
