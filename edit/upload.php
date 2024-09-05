<?php
require_once('../auth.php');

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  // Redirect to index.php if not logged in
  header("Location: ../index.php");
  exit;
}

$email = $_SESSION['email'];
$image_id = $_GET['id'];

// Connect to the SQLite database
$db = new SQLite3('../database.sqlite');

// Check for database connection errors
if (!$db) {
  die("Connection failed: " . $db->lastErrorMsg());
}

// Check if the 'upload' button is clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Check if any images were uploaded
  if (isset($_FILES['image'])) {
    ob_start(); // Start output buffering to prevent header errors

    $image_files = $_FILES['image'];

    // Determine today's date for folder structure
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

    // Loop through each uploaded file
    for ($i = 0; $i < count($image_files['name']); $i++) {
      // Generate a unique file name for the child image
      $ext = pathinfo($image_files['name'][$i], PATHINFO_EXTENSION);
      $filename = $dateFolder . '/' . uniqid() . '.' . $ext;

      // Save the child image
      move_uploaded_file($image_files['tmp_name'][$i], $uploadDir . basename($filename));

      // Determine the image type and generate the thumbnail
      $image_info = getimagesize($uploadDir . basename($filename));
      $mime_type = $image_info['mime'];

      switch ($mime_type) {
        case 'image/jpeg':
          $source = imagecreatefromjpeg($uploadDir . basename($filename));
          break;
        case 'image/png':
          $source = imagecreatefrompng($uploadDir . basename($filename));
          break;
        case 'image/gif':
          $source = imagecreatefromgif($uploadDir . basename($filename));
          break;
        case 'image/webp':
          $source = imagecreatefromwebp($uploadDir . basename($filename));
          break;
        case 'image/avif':
          $source = imagecreatefromavif($uploadDir . basename($filename));
          break;
        case 'image/bmp':
          $source = imagecreatefrombmp($uploadDir . basename($filename));
          break;
        case 'image/wbmp':
          $source = imagecreatefromwbmp($uploadDir . basename($filename));
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
      $thumbnail_height = intval(300 / $ratio); // Convert float to integer

      $thumbnail = imagecreatetruecolor($thumbnail_width, $thumbnail_height);

      if ($thumbnail === false) {
        echo "Error: Failed to create thumbnail.";
        exit;
      }

      imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $original_width, $original_height);

      switch ($ext) {
        case 'jpg':
        case 'jpeg':
          imagejpeg($thumbnail, $thumbnailDir . basename($filename));
          break;
        case 'png':
          imagepng($thumbnail, $thumbnailDir . basename($filename));
          break;
        case 'gif':
          imagegif($thumbnail, $thumbnailDir . basename($filename));
          break;
        case 'webp':
          imagewebp($thumbnail, $thumbnailDir . basename($filename));
          break;
        case 'avif':
          imageavif($thumbnail, $thumbnailDir . basename($filename));
          break;
        case 'bmp':
          imagebmp($thumbnail, $thumbnailDir . basename($filename));
          break;
        case 'wbmp': 
          imagewbmp($thumbnail, $thumbnailDir . basename($filename));
          break;
        default:
          echo "Error: Unsupported image format.";
          exit;
      }

      // Insert the new image into the "image_child" table with the associated image_id
      $stmt = $db->prepare("INSERT INTO image_child (image_id, filename, original_filename, email) VALUES (:image_id, :filename, :original_filename, :email)");
      $stmt->bindValue(':image_id', $image_id);
      $stmt->bindValue(':filename', $filename);
      $stmt->bindValue(':original_filename', basename($image_files['name'][$i]));
      $stmt->bindValue(':email', $email);

      // Check for database insertion errors
      if (!$stmt->execute()) {
        echo "Error: " . $db->lastErrorMsg();
        exit;
      }
    }

    // Redirect back to the page after upload
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/upload.php';
    header("Location: " . $redirect_url);
    exit;
  }
}

// Get the current image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id = :image_id AND email = :email");
$stmt->bindParam(':image_id', $image_id);
$stmt->bindParam(':email', $email);
$result = $stmt->execute();

// Check for database query errors
if (!$result) {
  echo "Error: " . $db->lastErrorMsg();
  exit;
}

// Fetch the result as an associative array
$image = $result->fetchArray(SQLITE3_ASSOC);

// Check if the image exists and belongs to the logged-in user
if (!$image) {
  echo '<meta charset="UTF-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <img src="../icon/403-Error-Forbidden.svg" style="height: 100%; width: 100%;">
       ';
  exit();
}

// Close the database connection
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
        <div id="drop-zone" class="drop-zone fw-medium mb-2 rounded-3 border-4 text-center">
          <div class="d-flex flex-column align-items-center">
            <div class="mb-4 mt-2">
              <i class="bi bi-filetype-png me-4 display-4"></i>
              <i class="bi bi-filetype-jpg me-4 display-4"></i>
              <i class="bi bi-filetype-gif display-4"></i>
            </div>
            <label for="file-ip-1">
              <input class="form-control mb-2 border rounded-3 fw-medium border-4" type="file" name="image[]" id="file-ip-1" accept="image/*" multiple required>
              <p style="word-break: break-word;" class="badge bg-dark text-wrap" style="font-size: 15px;">Drag and drop files here</p>
              <p><small><i class="bi bi-info-circle-fill"></i> type of extension you can upload: jpg, jpeg, png, gif</small></p>
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
        border: 2px dashed #ccc;
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
    
      function handleFiles(files) {
        var filesArray = Array.from(files).sort(function(a, b) {
          return a.name.localeCompare(b.name, undefined, { numeric: true, sensitivity: 'base' });
        });
    
        // Limit to 20 files and 20MB
        if (filesArray.length > 20) {
          alert('You can only upload up to 20 images.');
          return;
        }
    
        var totalSize = filesArray.reduce(function(sum, file) {
          return sum + file.size;
        }, 0);
    
        if (totalSize > 20 * 1024 * 1024) {
          alert('Total file size cannot exceed 20MB.');
          return;
        }
    
        updateFileInput(filesArray);
    
        var fileCount = filesArray.length;
        var message = fileCount + '/20 images selected';
        var messageElement = dropZone.querySelector('p');
        messageElement.textContent = message;
    
        var existingTotalSizeElement = dropZone.querySelector('.total-size');
        if (existingTotalSizeElement) {
          existingTotalSizeElement.remove();
        }
    
        var totalSizeInMB = totalSize / (1024 * 1024);
        var totalSizeText = Math.round(totalSizeInMB * 100) / 100 + ' MB';
    
        var totalSizeContainer = document.createElement('div');
        totalSizeContainer.classList.add('total-size');
    
        var totalSizeLabel = document.createElement('small');
        totalSizeLabel.classList.add('fw-medium');
        totalSizeLabel.textContent = 'Total Size: ' + totalSizeText;
    
        totalSizeContainer.appendChild(totalSizeLabel);
        dropZone.appendChild(totalSizeContainer);
    
        showPreview(filesArray);
      }
    
      function updateFileInput(sortedFiles) {
        var dataTransfer = new DataTransfer();
        sortedFiles.forEach(file => {
          dataTransfer.items.add(file);
        });
        fileInput.files = dataTransfer.files;
      }
    
      uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        var files = fileInput.files;
        uploadFiles(files);
      });
    
      function uploadFiles(files) {
        var formData = new FormData(uploadForm);
        for (var i = 0; i < files.length; i++) {
          formData.append('image[]', files[i]);
        }
      
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '');
      
        var startTime;
        var totalBytes = 0;
        var uploadedBytes = 0;
        var progressInfo = document.getElementById('progress-info');
      
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
      
            var elapsedTime = (Date.now() - startTime) / 1000; // in seconds
            var uploadSpeed = uploadedBytes / elapsedTime / 1024; // kb/s
            var remainingBytes = totalBytes - uploadedBytes;
            var estimatedTimeLeft = (remainingBytes / (uploadSpeed * 1024)).toFixed(1); // in seconds
      
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
      
            // Hide progress info
            progressInfo.style.display = 'none';
      
            if (xhr.status === 200) {
              showSuccessMessage();
            } else {
              showErrorMessage();
            }
          }
        };
      
        xhr.send(formData);
        progressBarContainer.style.display = 'block';
        uploadButton.style.display = 'none';
        progressInfo.style.display = 'block'; // Show progress info during upload
      }
    
      function showSuccessMessage() {
        // Hide the modal
        var uploadModal = document.getElementById('uploadModal');
        var modal = bootstrap.Modal.getInstance(uploadModal);
        modal.hide();
      
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
        goToHomeButton.href = '/image.php?artworkid=<?php echo $_GET['id']; ?>';
      
        var goToProfileButton = document.createElement('a');
        goToProfileButton.classList.add('btn', 'btn-primary', 'btn-sm', 'fw-medium', 'rounded');
        goToProfileButton.textContent = 'Go to Profile';
        goToProfileButton.href = '../profile.php';
      
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
      
        // Show the toast
        var toastElement = new bootstrap.Toast(toast);
        toastElement.show();
      
        // Automatically hide the toast after 1 minute (60000 milliseconds)
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
      
        // Show the toast
        var toastElement = new bootstrap.Toast(toast);
        toastElement.show();
      }
    
      function showPreview(files) {
        var container = document.getElementById("preview-container");
        container.innerHTML = "";
    
        // Add Bootstrap row classes to the container
        container.className = "row g-1 container-fluid mx-auto mb-3";
    
        if (window.innerWidth >= 768) {
          container.classList.add("row-cols-6");
        } else {
          container.classList.add(files.length > 1 ? "row-cols-2" : "row-col-1");
        }
    
        var html = '';
    
        for (var i = 0; i < files.length; i++) {
          var imgSrc = URL.createObjectURL(files[i]);
          var fileSize = files[i].size / (1024 * 1024); // Convert to MB
          var fileSizeRounded = Math.round(fileSize * 100) / 100; // Round to 2 decimal places
          var fileSizeText = fileSizeRounded + " MB";
          var fileName = files[i].name;
        
          // Truncate filename to 20 characters with ellipsis if necessary
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
                  <h6 class="fw-medium small">${fileSizeText}</h6>
                  <button class="btn btn-sm fw-medium border-0 p-0 m-0" id="view-info-${i}" data-index="${i}">
                    view more info
                  </button>
                </div>
              </div>
            </div>`;
        }
    
        container.innerHTML = html;
    
        // Add click event listeners after the HTML has been inserted
        var infoButtons = container.querySelectorAll('button[id^="view-info-"]');
        infoButtons.forEach((button) => {
          button.addEventListener("click", function () {
            var index = parseInt(this.getAttribute('data-index'), 10);
            displayMetadata(files[index]);
          });
        });
    
        // Add event listener for remove buttons
        var removeButtons = container.querySelectorAll('button[id^="remove-image-"]');
        removeButtons.forEach(button => {
          button.addEventListener('click', function() {
            var index = parseInt(this.getAttribute('data-index'), 10);
            removeFile(index);
          });
        });
      }
    
      function removeFile(index) {
        var files = Array.from(fileInput.files);
        files.splice(index, 1); // Remove file at index
        updateFileInput(files); // Update the file input with the new file list
        showPreview(files); // Update the preview
    
        // Update file count message
        var fileCount = files.length;
        var message = fileCount + '/20 images selected';
        var messageElement = dropZone.querySelector('p');
        messageElement.textContent = message;
    
        // Recalculate and update total size
        var totalSize = files.reduce(function(sum, file) {
          return sum + file.size;
        }, 0);
    
        var existingTotalSizeElement = dropZone.querySelector('.total-size');
        if (existingTotalSizeElement) {
          existingTotalSizeElement.remove();
        }
    
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
    
      function displayMetadata(file) {
        var metadataContainer = document.getElementById("metadata-container");
        metadataContainer.innerHTML = "";
    
        var fileName = file.name;
        var fileSize = file.size / (1024 * 1024); // Convert to MB
        var fileSizeRounded = Math.round(fileSize * 100) / 100; // Round to 2 decimal places
        var fileSizeText = fileSizeRounded + " MB";
        var fileType = file.type;
    
        // Create row for image
        var imgRow = document.createElement('div');
        imgRow.classList.add('row', 'g-4');
    
        // Image column
        var imgCol = document.createElement('div');
        imgCol.classList.add('col-md-6');
    
        // Image
        var img = new Image();
        img.src = URL.createObjectURL(file);
        img.classList.add('rounded', 'mb-3', 'mb-md-0', 'w-100', 'shadow');
        imgCol.appendChild(img);
        imgRow.appendChild(imgCol);
    
        // Metadata column
        var metadataCol = document.createElement('div');
        metadataCol.classList.add('col-md-6');
    
        // Image Name
        var fileNameElement = createMetadataElement('Image Name', fileName);
        metadataCol.appendChild(fileNameElement);
    
        // Image Size
        var fileSizeElement = createMetadataElement('Image Size', fileSizeText);
        metadataCol.appendChild(fileSizeElement);
    
        // Image Type
        var fileTypeElement = createMetadataElement('Image Type', fileType);
        metadataCol.appendChild(fileTypeElement);
    
        // Image Date
        var imageDateElement = createMetadataElement('Image Date', formatDate(file.lastModifiedDate));
        metadataCol.appendChild(imageDateElement);
    
        // Image Resolution
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
    
        // Load image resolution
        var imgForResolution = new Image();
        imgForResolution.src = URL.createObjectURL(file);
        imgForResolution.onload = function () {
          resolutionInput.value = this.naturalWidth + 'x' + this.naturalHeight;
        };
    
        // Append metadata column to row
        imgRow.appendChild(metadataCol);
    
        // Append row to container
        metadataContainer.appendChild(imgRow);
    
        // Show Modal
        var modal = new bootstrap.Modal(document.getElementById("metadataModal"));
        modal.show();
    
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
        return date.toLocaleDateString(undefined, options);
      }
    </script>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>