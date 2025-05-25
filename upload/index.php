<?php
require_once('../auth.php');
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload Your Artwork</title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
    <style>
      /* For Webkit-based browsers */
      ::-webkit-scrollbar {
        width: 0;
        height: 0;
        border-radius: 10px;
      }

      ::-webkit-scrollbar-track {
        border-radius: 0;
      }

      ::-webkit-scrollbar-thumb {
        border-radius: 0;
      }
    </style>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <?php include('sections.php'); ?>
    <div class="mt-2">
      <div id="preview-container" class="mb-2"></div>
      <div class="caard container">
        <div id="drop-zone" class="drop-zone border-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium mb-2 rounded-3 border-4 text-center">
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
          <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
            <div class="modal-content rounded-4 border-0">
              <div class="modal-header border-0">
                <h1 class="modal-title fw-medium fs-5" id="exampleModalLabel">Upload</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form id="upload-form" enctype="multipart/form-data">
                  <div class="form-floating">
                    <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>" type="text" name="title" id="title" placeholder="Enter title for your image" maxlength="500" required>  
                    <label for="title" class="fw-medium">Enter title for your artwork</label>
                  </div>
                  <h6 class="fw-medium mt-2 mb-4 small">(Note: Enter the title for your artwork)</h6>

                  <div class="form-floating">
                    <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>" type="text" name="tags" id="tags" placeholder="Enter tags for your image" maxlength="500" required>  
                    <label for="tags" class="fw-medium">Enter tags for your artwork</label>
                  </div>
                  <h6 class="fw-medium mt-2 mb-4 small">(Note: To separate tags, use comma ",")</h6>

                  <div class="form-floating">
                    <textarea class="form-control rounded-3 fw-medium border-0 bg-body-tertiary focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>" type="text" name="imgdesc" id="imgdesc" placeholder="Enter description for your image" maxlength="5000" style="height: 200px;" required></textarea>
                    <label for="imgdesc" class="fw-medium">Enter description</label>
                  </div>
                  <h6 class="fw-medium mt-2 mb-4 small">(Note: Make sure use "http" or "https" for url link)</h6>

                  <div class="form-floating">
                    <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>" type="text" name="group" id="group" placeholder="Enter group for your image" maxlength="4500">  
                    <label for="group" class="fw-medium">Enter group for your artwork</label>
                  </div>
                  <h6 class="fw-medium mt-2 mb-4 small">(Note: Group is optional, to displaying group names for <a class="text-decoration-none fw-medium" target="_blank" href="/feeds/manga/?group=">manga section only!</a>)</h6>

                  <div class="form-floating">
                    <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>" type="text" name="characters" id="characters" placeholder="Enter characters for your image" maxlength="4500">  
                    <label for="characters" class="fw-medium">Enter characters for your artwork</label>
                  </div>
                  <h6 class="fw-medium mt-2 mb-4 small">(Note: Characters is optional, to displaying character names for <a class="text-decoration-none fw-medium" target="_blank" href="/feeds/manga/?character=">manga section only!</a>)</h6>

                  <div class="form-floating">
                    <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>" type="text" name="parodies" id="parodies" placeholder="Enter parodies for your image" maxlength="4500">  
                    <label for="parodies" class="fw-medium">Enter parodies for your artwork</label>
                  </div>
                  <h6 class="fw-medium mt-2 mb-4 small">(Note: Parodies is optional, to displaying fiction names for <a class="text-decoration-none fw-medium" target="_blank" href="/feeds/manga/?parody=">manga section only!</a>)</h6>

                  <div class="form-floating">
                    <select class="form-select border-0 bg-body-tertiary focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?> rounded-3 fw-medium py-0 text-start" name="episode_name">
                      <option class="form-control" value="">Add episode:</option>
                      <?php
                        // Connect to the SQLite database
                        $db = new SQLite3('../database.sqlite');

                        // Get the email of the current user
                        $email = $_SESSION['email'];

                        // Retrieve the list of albums created by the current user
                        $stmt = $db->prepare('SELECT * FROM episode WHERE email = :email ORDER BY id DESC');
                        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
                        $results = $stmt->execute();

                        // Loop through each album and create an option in the dropdown list
                        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                          $episode_name = $row['episode_name'];
                          $id = $row['id'];
                          echo '<option value="' . htmlspecialchars($episode_name). '">' . htmlspecialchars($episode_name). '</option>';
                        }

                        $db->close();
                      ?>
                    </select>
                  </div>
                  <h6 class="fw-medium mt-2 mb-4 small">(Note: Episode name is optional, to displaying manga title names for <a class="text-decoration-none fw-medium" target="_blank" href="/feeds/manga/">manga section only!</a>)</h6>

                  <div class="row g-2 mb-2">
                    <div class="col-md-6">
                      <select class="form-select border-0 bg-body-tertiary focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?> rounded-3 fw-medium" style="height: 58px;" name="artwork_type" aria-label="Large select example" required>
                        <option value="illustration" selected>Illustration</option>
                        <option value="manga">Manga</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <select class="form-select border-0 bg-body-tertiary focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?> rounded-3 fw-medium" style="height: 58px;" name="type" aria-label="Large select example" required>
                        <option value="safe" selected>Safe For Works</option>
                        <option value="nsfw">NSFW/R-18</option>
                      </select>
                    </div>
                  </div>

                  <div class="row g-2 mb-2">
                    <div class="col-md-6">
                      <select class="form-select border-0 bg-body-tertiary focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?> rounded-3 fw-medium" style="height: 58px;" name="categories" aria-label="Large select example" required>
                        <option value="artworks/illustrations" selected>artworks/illustrations</option>
                        <option value="3DCG">3DCG</option>
                        <option value="real">real</option>
                        <option value="MMD">MMD</option>
                        <option value="multi-work series">multi-work series</option>
                        <option value="manga series">manga series</option>
                        <option value="doujinshi series">doujinshi series</option>
                        <option value="oneshot manga">oneshot manga</option>
                        <option value="oneshot doujinshi">oneshot doujinshi</option>
                        <option value="doujinshi">doujinshi</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <select class="form-select border-0 bg-body-tertiary focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?> rounded-3 fw-medium" style="height: 58px;" name="language" aria-label="Large select example" required>
                        <option>Choose Language</option>
                        <option value="English">English</option>
                        <option value="Japanese">Japanese</option>
                        <option value="Chinese">Chinese</option>
                        <option value="Korean">Korean</option>
                        <option value="Russian">Russian</option>
                        <option value="Indonesian">Indonesian</option>
                        <option value="Spanish">Spanish</option>
                        <option value="Other">Other</option>
                        <option value="None">None</option>
                      </select>
                    </div>
                  </div>

                  <button class="btn btn-lg btn-primary fw-medium w-100" id="upload-button" type="submit">UPLOAD</button>
                </form>
                <div id="progress-bar-container" class="progress fw-medium" style="height: 45px; display: none;">
                  <div id="progress-bar" class="progress-bar progress-bar-animated" style="height: 45px;" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div id="progress-info" class="mt-2 fw-medium"></div>
              </div>
            </div>
          </div>
        </div>
        <button type="button" class="btn btn-primary w-100 fw-medium mb-2" data-bs-toggle="modal" data-bs-target="#uploadModal">
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
        <div class="modal-content rounded-4 border-0">
          <div class="modal-header border-0">
            <h5 class="modal-title fw-medium" id="metadataModalLabel">Image Metadata</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
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
        xhr.open('POST', 'upload.php');
      
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
        goToHomeButton.textContent = 'Go to Myworks';
        goToHomeButton.href = '/myworks.php';
      
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