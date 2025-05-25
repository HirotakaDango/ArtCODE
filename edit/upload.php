<?php
require_once('../auth.php');

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  header("Location: ../index.php");
  exit;
}

$email = $_SESSION['email'];
$image_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$image_id) {
  die("Invalid image ID.");
}

// Connect to SQLite database with error handling
try {
  $db = new SQLite3('../database.sqlite', SQLITE3_OPEN_READWRITE);
} catch (Exception $e) {
  die("Database connection failed: " . $e->getMessage());
}

// Get user ID from email
$stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);

if (!$user || !isset($user['id'])) {
  die("Error: Unable to find user ID.");
}

$user_id = $user['id'];

// Get the existing image information
$stmt = $db->prepare("SELECT filename FROM images WHERE id = :image_id AND email = :email");
$stmt->bindValue(':image_id', $image_id, SQLITE3_INTEGER);
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();
$image = $result->fetchArray(SQLITE3_ASSOC);

if (!$image) {
  die("Error: Image not found or access denied.");
}

// Extract the unique ID from filename using regex
if (!preg_match('/imageassets_([^\/]+)/', $image['filename'], $matches)) {
  die("Error: Could not determine unique ID from existing image.");
}

$uniqueId = $matches[1];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
  $images = $_FILES['image'];

  // Base directories
  $uploadDir = '../images/';
  $thumbnailDir = '../thumbnails/';

  // Ensure directories exist
  foreach ([$uploadDir, $thumbnailDir] as $dir) {
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
      die("Error: Failed to create directory $dir.");
    }
  }

  // Get count of existing child images
  $stmt = $db->prepare("SELECT COUNT(*) as count FROM image_child WHERE image_id = :image_id");
  $stmt->bindValue(':image_id', $image_id, SQLITE3_INTEGER);
  $result = $stmt->execute();
  $count = $result->fetchArray(SQLITE3_ASSOC);
  $startIndex = (int) $count['count'] + 1;

  for ($i = 0, $len = count($images['name']); $i < $len; $i++) {
    $currentIndex = $startIndex + $i;
    $ext = strtolower(pathinfo($images['name'][$i], PATHINFO_EXTENSION));

    // Validate file extension
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp', 'wbmp'];
    if (!in_array($ext, $allowedExts, true)) {
      echo "Error: Unsupported image format.";
      continue;
    }

    // Generate file paths
    $filename = "uid_{$user_id}/data/imageid-{$image_id}/imageassets_{$uniqueId}/{$uniqueId}_i{$currentIndex}.{$ext}";
    $uploadPath = $uploadDir . $filename;
    $thumbnailPath = $thumbnailDir . $filename;
    $originalFilename = basename($images['name'][$i]);

    // Create necessary directories
    foreach ([dirname($uploadPath), dirname($thumbnailPath)] as $dir) {
      if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        echo "Error: Failed to create directory $dir.";
        continue 2;
      }
    }

    // Save the uploaded file
    if (!move_uploaded_file($images['tmp_name'][$i], $uploadPath)) {
      echo "Error: Failed to save uploaded file.";
      continue;
    }

    // Create a thumbnail
    $imageInfo = getimagesize($uploadPath);
    $mime = $imageInfo['mime'];
    
    // Create source image based on mime type (Restored switch)
    switch ($mime) {
      case 'image/jpeg':
        $source = imagecreatefromjpeg($uploadPath);
        break;
      case 'image/png':
        $source = imagecreatefrompng($uploadPath);
        break;
      case 'image/gif':
        $source = imagecreatefromgif($uploadPath);
        break;
      case 'image/webp':
        $source = imagecreatefromwebp($uploadPath);
        break;
      case 'image/avif':
        $source = function_exists('imagecreatefromavif') ? imagecreatefromavif($uploadPath) : null;
        break;
      case 'image/bmp':
        $source = imagecreatefrombmp($uploadPath);
        break;
      case 'image/wbmp':
        $source = imagecreatefromwbmp($uploadPath);
        break;
      default:
        $source = null;
    }

    if (!$source) {
      echo "Error: Failed to create image source.";
      continue;
    }

    // Resize image for thumbnail
    $origWidth = imagesx($source);
    $origHeight = imagesy($source);
    $thumbWidth = 300;
    $thumbHeight = (int) (300 / ($origWidth / $origHeight));

    $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
    imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $origWidth, $origHeight);

    // Save thumbnail based on extension (Restored switch)
    switch ($ext) {
      case 'jpg':
      case 'jpeg':
        imagejpeg($thumbnail, $thumbnailPath);
        break;
      case 'png':
        imagepng($thumbnail, $thumbnailPath);
        break;
      case 'gif':
        imagegif($thumbnail, $thumbnailPath);
        break;
      case 'webp':
        imagewebp($thumbnail, $thumbnailPath);
        break;
      case 'avif':
        function_exists('imageavif') ? imageavif($thumbnail, $thumbnailPath) : null;
        break;
      case 'bmp':
        imagebmp($thumbnail, $thumbnailPath);
        break;
      case 'wbmp':
        imagewbmp($thumbnail, $thumbnailPath);
        break;
    }

    // Insert into database
    $stmt = $db->prepare("INSERT INTO image_child (filename, original_filename, image_id, email) VALUES (:filename, :original_filename, :image_id, :email)");
    $stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
    $stmt->bindValue(':original_filename', $originalFilename, SQLITE3_TEXT);
    $stmt->bindValue(':image_id', $image_id, SQLITE3_INTEGER);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->execute();
  }

  header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/upload.php'));
  exit;
}

// Fetch image details
$stmt = $db->prepare("SELECT * FROM images WHERE id = :image_id AND email = :email");
$stmt->bindValue(':image_id', $image_id, SQLITE3_INTEGER);
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();
$image = $result->fetchArray(SQLITE3_ASSOC);

if (!$image) {
  echo '<meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <img src="../icon/403-Error-Forbidden.svg" style="height: 100%; width: 100%;">';
  exit;
}

$db->close();
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload New Images to <?php echo $image['title']; ?></title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <div>
      <div class="container">
        <?php include('nav.php'); ?>
      </div>
      <div id="preview-container" class="mb-2"></div>
      <div class="caard container">
        <div id="drop-zone" class="drop-zone <?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium mb-2 rounded-3 border-4 text-center">
          <div class="d-flex flex-column align-items-center">
            <div class="mb-4 mt-2">
              <i class="bi bi-filetype-png me-4 display-4"></i>
              <i class="bi bi-filetype-jpg me-4 display-4"></i>
              <i class="bi bi-filetype-gif display-4"></i>
            </div>
            <label for="file-ip-1">
              <input class="form-control mb-2 border rounded-3 fw-medium border-4" type="file" name="image[]" id="file-ip-1" accept="image/*" multiple required>
              <p style="word-break: break-word;" class="badge bg-dark text-wrap" style="font-size: 15px;">Drag and drop files here</p>
              <p><small><i class="bi bi-info-circle-fill"></i> Supported formats: JPG, PNG, GIF, WEBP, AVIF, BMP.</small></p>
              <div class="total-size"></div>
            </label>
          </div>
        </div>
        <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content rounded-4 shadow border-0">
              <div class="modal-header border-0">
                <h1 class="modal-title fw-medium fs-5" id="exampleModalLabel">Upload</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body scrollable-div">
                <form id="upload-form" enctype="multipart/form-data">
                  <button class="btn btn-lg btn-primary fw-medium w-100" id="upload-button" type="submit">UPLOAD</button>
                </form>
                <div id="progress-bar-container" class="progress fw-medium" style="height: 45px; display: none;">
                  <div id="progress-bar" class="progress-bar progress-bar progress-bar-animated" style="height: 45px;" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div id="progress-info" class="mt-2 fw-medium"></div>
              </div>
            </div>
          </div>
        </div>
        <button type="button" class="mb-2 btn btn-primary w-100 fw-medium" data-bs-toggle="modal" data-bs-target="#uploadModal">
          UPLOAD
        </button>
        <div class="d-flex align-items-center justify-content-center">
          <a class="text-decoration-none fw-medium link-dark link-body-emphasis" href="#" data-bs-toggle="modal" data-bs-target="#limitationsModal">Note: read our limitations in case you forgot</a>
        </div>
      </div>
    </div>
    <div class="mt-5"></div>
    <!-- Metadata Modal -->
    <div class="modal fade" id="metadataModal" tabindex="-1" aria-labelledby="metadataModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content rounded-4 shadow border-0">
          <div class="modal-header border-0">
            <h5 class="modal-title fw-medium" id="metadataModalLabel">Image Metadata</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body scrollable-div">
            <div id="metadata-container"></div>
          </div>
        </div>
      </div>
    </div>
    <!-- Limitations Modal -->
    <?php include('limitations.php'); ?>
    <style>
      .text-stroke {
        -webkit-text-stroke: 1px;
      }
      
      .drop-zone {
        position: relative;
        display: inline-block;
        border: 2px dashed;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        width: 100%;
      }
  
      .drop-zone input[type="file"] {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
      }

      .drag-over {
        border-color: #000;
      }
    </style>
    <script>
      var dropZone = document.getElementById('drop-zone');
      var fileInput = document.getElementById('file-ip-1');
      var uploadForm = document.getElementById('upload-form');
      var progressBarContainer = document.getElementById('progress-bar-container');
      var progressBar = document.getElementById('progress-bar');
      var progressInfo = document.getElementById('progress-info');
      var uploadButton = document.getElementById('upload-button');
    
      var currentFiles = [];
    
      dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('drag-over');
      });
    
      dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
      });
    
      dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        var files = e.dataTransfer.files;
        handleFiles(files);
      });
    
      fileInput.addEventListener('change', function(e) {
        var files = e.target.files;
        handleFiles(files);
      });
    
      function handleFiles(newlySelectedFiles) {
        var newFilesArray = Array.from(newlySelectedFiles);
    
        var potentialTotalCount = currentFiles.length + newFilesArray.length;
        if (potentialTotalCount > 20) {
          alert('Cannot add new images. You can select up to 20 images in total. Please de-select some existing images if you want to add more.');
          fileInput.value = '';
          return;
        }
    
        var currentTotalSize = currentFiles.reduce(function(sum, file) {
          return sum + file.size;
        }, 0);
    
        var newFilesSize = newFilesArray.reduce(function(sum, file) {
          return sum + file.size;
        }, 0);
    
        var potentialTotalSize = currentTotalSize + newFilesSize;
        if (potentialTotalSize > 20 * 1024 * 1024) {
          alert('Cannot add new images. Total file size would exceed 20MB. Please de-select some existing images if you want to add more.');
          fileInput.value = '';
          return;
        }
    
        newFilesArray.forEach(function(newFile) {
          var isDuplicate = currentFiles.some(function(existingFile) {
            return existingFile.name === newFile.name &&
                   existingFile.size === newFile.size &&
                   existingFile.lastModified === newFile.lastModified;
          });
          if (!isDuplicate) {
            currentFiles.push(newFile);
          }
        });
    
        currentFiles.sort(function(a, b) {
          return a.name.localeCompare(b.name, undefined, { numeric: true, sensitivity: 'base' });
        });
    
        updateFileInput(currentFiles);
        updateDisplayWithFiles(currentFiles);
    
        fileInput.value = '';
      }
    
      function updateDisplayWithFiles(filesToDisplay) {
        var fileCount = filesToDisplay.length;
        var message = fileCount + '/20 images selected';
        var messageElement = dropZone.querySelector('p');
        if (messageElement) {
          messageElement.textContent = message;
        }
    
        var totalSize = filesToDisplay.reduce(function(sum, file) {
          return sum + file.size;
        }, 0);
    
        var existingTotalSizeElement = dropZone.querySelector('.total-size');
        if (existingTotalSizeElement) {
          existingTotalSizeElement.remove();
        }
    
        if (fileCount > 0) {
          var totalSizeInMB = totalSize / (1024 * 1024);
          var totalSizeText = Math.round(totalSizeInMB * 100) / 100 + ' MB';
    
          var totalSizeContainer = document.createElement('div');
          totalSizeContainer.classList.add('total-size');
    
          var totalSizeLabel = document.createElement('small');
          totalSizeLabel.classList.add('fw-medium');
          totalSizeLabel.textContent = 'Total Size: ' + totalSizeText;
    
          totalSizeContainer.appendChild(totalSizeLabel);
          dropZone.appendChild(totalSizeContainer);
        }
        
        showPreview(filesToDisplay);
      }
    
      function updateFileInput(sortedFiles) {
        var dataTransfer = new DataTransfer();
        sortedFiles.forEach(function(file) {
          dataTransfer.items.add(file);
        });
        fileInput.files = dataTransfer.files;
      }
    
      uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (currentFiles.length === 0) {
          alert('Please select at least one image to upload.');
          return;
        }
        if (currentFiles.length > 20) {
          alert('You have selected more than 20 images. Please reduce the selection to 20 images or fewer.');
          return;
        }
        var totalSizeForUpload = currentFiles.reduce(function(sum, file) { return sum + file.size; }, 0);
        if (totalSizeForUpload > 20 * 1024 * 1024) {
          alert('Total file size exceeds 20MB. Please reduce the selection.');
          return;
        }
        uploadFiles(currentFiles);
      });
    
      function uploadFiles(filesToUpload) {
        var formData = new FormData(uploadForm);
        
        for (var i = 0; i < filesToUpload.length; i++) {
          formData.append('image[]', filesToUpload[i]);
        }
      
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '');
      
        var startTime;
        var totalBytes = 0;
        var uploadedBytes = 0;
    
        xhr.upload.addEventListener('progress', function(e) {
          if (e.lengthComputable) {
            if (!startTime) {
              startTime = Date.now();
              totalBytes = e.total;
            }
            uploadedBytes = e.loaded;
      
            var percent = Math.round((uploadedBytes / totalBytes) * 100);
            progressBar.style.width = percent + '%';
            progressBar.textContent = percent + '%';
      
            var elapsedTime = (Date.now() - startTime) / 1000;
            var uploadSpeed = uploadedBytes / elapsedTime / 1024;
            var remainingBytes = totalBytes - uploadedBytes;
            var estimatedTimeLeft = (remainingBytes / (uploadSpeed * 1024)).toFixed(1);
      
            progressInfo.innerHTML = `
              Upload Speed: ${uploadSpeed.toFixed(1)} kb/s<br>
              Time Left: ${Math.max(0, Math.ceil(estimatedTimeLeft))} s
            `;
          }
        });
      
        xhr.onreadystatechange = function() {
          if (xhr.readyState === XMLHttpRequest.DONE) {
            progressBarContainer.style.display = 'none';
            uploadButton.style.display = 'block';
            uploadButton.disabled = false;
      
            progressInfo.style.display = 'none';
      
            if (xhr.status === 200) {
              showSuccessMessage();
              currentFiles = [];
              updateFileInput(currentFiles);
              updateDisplayWithFiles(currentFiles);
            } else {
              showErrorMessage();
            }
          }
        };
      
        xhr.send(formData);
        progressBarContainer.style.display = 'block';
        uploadButton.style.display = 'none';
        progressInfo.style.display = 'block';
      }
    
      function showSuccessMessage() {
        var uploadModalElement = document.getElementById('uploadModal');
        if (uploadModalElement) {
          var modalInstance = bootstrap.Modal.getInstance(uploadModalElement);
          if (modalInstance) {
            modalInstance.hide();
          }
        }
      
        var toastContainer = document.createElement('div');
        toastContainer.classList.add('toast-container', 'position-fixed', 'bottom-0', 'end-0', 'p-3');
      
        var toast = document.createElement('div');
        toast.classList.add('toast');
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
      
        var toastHeader = document.createElement('div');
        toastHeader.classList.add('toast-header', 'border-0');
      
        var toastTitle = document.createElement('strong');
        toastTitle.classList.add('me-auto');
        toastTitle.textContent = 'ArtCODE';
      
        var toastTime = document.createElement('small');
        toastTime.textContent = 'Just now';
      
        var closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.classList.add('btn-close');
        closeButton.setAttribute('data-bs-dismiss', 'toast');
        closeButton.setAttribute('aria-label', 'Close');
      
        var toastBody = document.createElement('div');
        toastBody.classList.add('toast-body', 'fw-medium');
        toastBody.textContent = 'File uploaded successfully.';
      
        var actionButtons = document.createElement('div');
        actionButtons.classList.add('mt-2', 'pt-2', 'border-top');
      
        var buttonGroup = document.createElement('div');
        buttonGroup.classList.add('btn-group', 'w-100', 'gap-2');
      
        var goToHomeButton = document.createElement('a');
        goToHomeButton.classList.add('btn', 'btn-primary', 'btn-sm', 'fw-medium', 'rounded');
        goToHomeButton.textContent = 'Go to Artwork';
        goToHomeButton.href = '/image.php?artworkid=<?php echo isset($_GET['id']) ? htmlspecialchars($_GET['id']) : ''; ?>';
      
        var goToProfileButton = document.createElement('a');
        goToProfileButton.classList.add('btn', 'btn-primary', 'btn-sm', 'fw-medium', 'rounded');
        goToProfileButton.textContent = 'Go to Profile';
        goToProfileButton.href = '/profile.php';
      
        buttonGroup.appendChild(goToHomeButton);
        buttonGroup.appendChild(goToProfileButton);
      
        actionButtons.appendChild(buttonGroup);
      
        var closeButtonInToast = document.createElement('button');
        closeButtonInToast.type = 'button';
        closeButtonInToast.classList.add('btn', 'btn-secondary', 'btn-sm', 'mt-2', 'fw-medium', 'w-100');
        closeButtonInToast.setAttribute('data-bs-dismiss', 'toast');
        closeButtonInToast.textContent = 'Close';
      
        toastHeader.appendChild(toastTitle);
        toastHeader.appendChild(toastTime);
        toastHeader.appendChild(closeButton);
      
        toastBody.appendChild(actionButtons);
        toastBody.appendChild(closeButtonInToast);
      
        toast.appendChild(toastHeader);
        toast.appendChild(toastBody);
      
        toastContainer.appendChild(toast);
      
        document.body.appendChild(toastContainer);
      
        var toastElement = new bootstrap.Toast(toast);
        toastElement.show();
      
        setTimeout(function () {
          toastElement.hide();
        }, 60000);
      }
      
      function showErrorMessage() {
        var toastContainer = document.createElement('div');
        toastContainer.classList.add('toast-container', 'position-fixed', 'bottom-0', 'end-0', 'p-3');
      
        var toast = document.createElement('div');
        toast.classList.add('toast', 'bg-danger');
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
      
        var toastHeader = document.createElement('div');
        toastHeader.classList.add('toast-header', 'border-0');
      
        var toastTitle = document.createElement('strong');
        toastTitle.classList.add('me-auto');
        toastTitle.textContent = 'ArtCODE';
      
        var toastTime = document.createElement('small');
        toastTime.textContent = 'Just now';
      
        var closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.classList.add('btn-close');
        closeButton.setAttribute('data-bs-dismiss', 'toast');
        closeButton.setAttribute('aria-label', 'Close');
      
        var toastBody = document.createElement('div');
        toastBody.classList.add('toast-body', 'fw-medium', 'text-light');
        toastBody.textContent = 'Image upload failed. Please try again.';
      
        var closeButtonInToast = document.createElement('button');
        closeButtonInToast.type = 'button';
        closeButtonInToast.classList.add('btn', 'btn-secondary', 'btn-sm', 'mt-2', 'fw-medium', 'w-100');
        closeButtonInToast.setAttribute('data-bs-dismiss', 'toast');
        closeButtonInToast.textContent = 'Close';
      
        toastHeader.appendChild(toastTitle);
        toastHeader.appendChild(toastTime);
        toastHeader.appendChild(closeButton);
      
        toastBody.appendChild(closeButtonInToast);
      
        toast.appendChild(toastHeader);
        toast.appendChild(toastBody);
      
        toastContainer.appendChild(toast);
      
        document.body.appendChild(toastContainer);
      
        var toastElement = new bootstrap.Toast(toast);
        toastElement.show();
      }
    
      function showPreview(files) {
        var container = document.getElementById("preview-container");
        container.innerHTML = "";
        container.className = "row g-1 container-fluid mx-auto mb-3";
    
        if (window.innerWidth >= 768) {
          container.classList.add("row-cols-6");
        } else {
          container.classList.add(files.length > 1 ? "row-cols-2" : "row-cols-1");
        }
    
        var html = '';
        for (var i = 0; i < files.length; i++) {
          var imgSrc = URL.createObjectURL(files[i]);
          var fileSize = files[i].size / (1024 * 1024);
          var fileSizeRounded = Math.round(fileSize * 100) / 100;
          var fileSizeText = fileSizeRounded + " MB";
          var fileName = files[i].name;
          var truncatedFileName = fileName.length > 20 ? fileName.substring(0, 17) + '...' : fileName;
        
          html += `
            <div class="col position-relative">
              <div class="ratio ratio-1x1">
                <img src="${imgSrc}" class="w-100 rounded object-fit-cover rounded-bottom-0">
              </div>
              <button class="btn btn-sm border-0 position-absolute top-0 end-0 m-1" id="remove-image-${i}" data-index="${i}">
                <i class="bi bi-x-lg text-danger" style="-webkit-text-stroke: 1.5px;"></i>
              </button>
              <div class="rounded-1 d-flex align-items-center bg-body-secondary rounded-top-0 w-100 p-3 pb-2 text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>">
                <div class="my-auto">
                  <h6 class="fw-medium small">${truncatedFileName}</h6>
                  <h6 class="fw-medium small pb-0 mb-0">${fileSizeText}</h6>
                  <button class="btn btn-sm fw-medium border-0 p-0 m-0" id="view-info-${i}" data-index="${i}">
                    view more info
                  </button>
                </div>
              </div>
            </div>`;
        }
        container.innerHTML = html;
    
        var infoButtons = container.querySelectorAll('button[id^="view-info-"]');
        infoButtons.forEach(function(button) {
          button.addEventListener("click", function () {
            var index = parseInt(this.getAttribute('data-index'), 10);
            displayMetadata(files[index]);
          });
        });
    
        var removeButtons = container.querySelectorAll('button[id^="remove-image-"]');
        removeButtons.forEach(function(button) {
          button.addEventListener('click', function() {
            var index = parseInt(this.getAttribute('data-index'), 10);
            removeFile(index);
          });
        });
      }
    
      function removeFile(index) {
        currentFiles.splice(index, 1);
        updateFileInput(currentFiles);
        updateDisplayWithFiles(currentFiles);
      }
    
      function displayMetadata(file) {
        var metadataContainer = document.getElementById("metadata-container");
        metadataContainer.innerHTML = "";
    
        var fileName = file.name;
        var fileSize = file.size / (1024 * 1024);
        var fileSizeRounded = Math.round(fileSize * 100) / 100;
        var fileSizeText = fileSizeRounded + " MB";
        var fileType = file.type;
    
        var imgRow = document.createElement('div');
        imgRow.classList.add('row', 'g-4');
    
        var imgCol = document.createElement('div');
        imgCol.classList.add('col-md-6');
    
        var img = new Image();
        img.src = URL.createObjectURL(file);
        img.classList.add('rounded', 'mb-3', 'mb-md-0', 'w-100', 'shadow');
        imgCol.appendChild(img);
        imgRow.appendChild(imgCol);
    
        var metadataCol = document.createElement('div');
        metadataCol.classList.add('col-md-6');
    
        var fileNameElement = createMetadataElement('Image Name', fileName);
        metadataCol.appendChild(fileNameElement);
    
        var fileSizeElement = createMetadataElement('Image Size', fileSizeText);
        metadataCol.appendChild(fileSizeElement);
    
        var fileTypeElement = createMetadataElement('Image Type', fileType);
        metadataCol.appendChild(fileTypeElement);
    
        var imageDateElement = createMetadataElement('Image Date', formatDate(file.lastModifiedDate));
        metadataCol.appendChild(imageDateElement);
    
        var imgResolutionElement = document.createElement('div');
        imgResolutionElement.classList.add('mb-3', 'row', 'g-2');
    
        var resolutionLabel = document.createElement('label');
        resolutionLabel.classList.add('col-sm-4', 'col-form-label', 'text-nowrap', 'fw-medium');
        resolutionLabel.innerText = 'Image Resolution';
        imgResolutionElement.appendChild(resolutionLabel);
    
        var resolutionValue = document.createElement('div');
        resolutionValue.classList.add('col-sm-8');
        var resolutionInput = document.createElement('input');
        resolutionInput.classList.add('form-control-plaintext', 'fw-medium');
        resolutionInput.type = 'text';
        resolutionInput.readOnly = true;
        resolutionValue.appendChild(resolutionInput);
        imgResolutionElement.appendChild(resolutionValue);
    
        metadataCol.appendChild(imgResolutionElement);
    
        var imgForResolution = new Image();
        imgForResolution.src = URL.createObjectURL(file);
        imgForResolution.onload = function () {
          resolutionInput.value = this.naturalWidth + 'x' + this.naturalHeight;
        };
    
        imgRow.appendChild(metadataCol);
        metadataContainer.appendChild(imgRow);
    
        var metadataModalElement = document.getElementById("metadataModal");
        if (metadataModalElement) {
          var modal = new bootstrap.Modal(metadataModalElement);
          modal.show();
        }
    
        function createMetadataElement(label, value) {
          var div = document.createElement('div');
          div.classList.add('mb-3', 'row', 'g-2');
    
          var labelElement = document.createElement('label');
          labelElement.classList.add('col-sm-4', 'col-form-label', 'text-nowrap', 'fw-medium');
          labelElement.innerText = label;
          div.appendChild(labelElement);
    
          var valueElement = document.createElement('div');
          valueElement.classList.add('col-sm-8');
          var inputElement = document.createElement('input');
          inputElement.classList.add('form-control-plaintext', 'fw-medium');
          inputElement.type = 'text';
          inputElement.value = value;
          inputElement.readOnly = true;
          valueElement.appendChild(inputElement);
          div.appendChild(valueElement);
    
          return div;
        }
      }
    
      function formatDate(date) {
        var options = { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' };
        if (date instanceof Date) {
          return date.toLocaleDateString(undefined, options);
        } else if (typeof date === 'number') {
          return new Date(date).toLocaleDateString(undefined, options);
        }
        return 'N/A';
      }
    </script>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>