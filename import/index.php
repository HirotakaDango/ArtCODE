<?php
require_once('../auth.php');
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Import Your Artwork</title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
    <style>
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

      .progress-info {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
      }
    </style>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <?php include('../upload/sections.php'); ?>
    <div class="container mt-2">
      <div id="drop-zone" class="drop-zone fw-medium mb-2 rounded-3 border-4 text-center">
        <div class="d-flex flex-column align-items-center">
          <div class="my-2">
            <i class="bi bi-filetype-zip display-4"></i>
          </div>
          <label for="zipfile">
            <input type="file" id="zipfile" name="zipfile" accept=".zip" required>
            <p class="badge bg-dark text-wrap" style="font-size: 15px;">Drag and drop ZIP files here or click to select</p>
          </label>
        </div>
      </div>
      <div id="cardPreview" class="card mb-2" style="display:none;">
        <div class="card-header">
          File Information
        </div>
        <div class="card-body">
          <div class="mb-2 row">
            <label class="col-3 col-form-label text-nowrap fw-medium">File Name</label>
            <div class="col-9">
              <p class="form-control-plaintext fw-bold text-white" id="fileName"></p>
            </div>
          </div>
          <div class="mb-2 row">
            <label class="col-3 col-form-label text-nowrap fw-medium">File Size</label>
            <div class="col-9">
              <p class="form-control-plaintext fw-bold text-white" id="fileSizePreview"></p>
            </div>
          </div>
        </div>
      </div>
      <button id="uploadButton" type="submit" class="btn btn-primary w-100 fw-medium mb-2">Upload and Import</button>
      <div id="progressWrapper" style="display:none;">
        <div class="progress fw-medium" style="height: 45px;">
          <div id="progressBar" class="progress-bar fw-medium" role="progressbar" style="width: 0%; height: 45px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
        </div>
        <div id="progressInfo" class="progress-info">
          <span id="fileSize">File Size: </span>
          <span id="timeLeft">Time Left: </span>
          <span id="speed">Speed: </span>
        </div>
      </div>
    </div>
    <div class="mt-5"></div>

    <!-- Toast Notification -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
      <div id="uploadToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto">Upload Status</strong>
          <small>Just now</small>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div id="toastBody" class="toast-body">
          Upload completed successfully.
        </div>
      </div>
    </div>

    <script>
      document.getElementById('zipfile').addEventListener('change', function() {
        var file = this.files[0];
        if (file) {
          var cardPreview = document.getElementById('cardPreview');
          var fileNameElem = document.getElementById('fileName');
          var fileSizeElem = document.getElementById('fileSizePreview');

          // Show file info
          fileNameElem.textContent = file.name;
          fileSizeElem.textContent = (file.size / 1024).toFixed(2) + ' KB';

          cardPreview.style.display = 'block';
        } else {
          document.getElementById('cardPreview').style.display = 'none';
        }
      });

      document.getElementById('uploadButton').addEventListener('click', function(event) {
        event.preventDefault();
        var formData = new FormData();
        var fileInput = document.getElementById('zipfile');
        if (fileInput.files.length > 0) {
          formData.append('zipfile', fileInput.files[0]);
        }
        var xhr = new XMLHttpRequest();
        var startTime = Date.now();

        xhr.open('POST', 'import_code.php', true);

        // Hide upload button and show progress bar
        document.getElementById('uploadButton').style.display = 'none';
        document.getElementById('progressWrapper').style.display = 'block';

        xhr.upload.onprogress = function(e) {
          if (e.lengthComputable) {
            var percentComplete = Math.round((e.loaded / e.total) * 100);
            var progressBar = document.getElementById('progressBar');
            var elapsedTime = Date.now() - startTime;
            var timeLeft = (e.total - e.loaded) / (e.loaded / elapsedTime);
            var speed = e.loaded / (elapsedTime / 1000); // Speed in bytes per second

            progressBar.style.width = percentComplete + '%';
            progressBar.textContent = percentComplete + '%';

            document.getElementById('fileSize').textContent = 'File Size: ' + (e.total / 1024).toFixed(2) + ' KB';
            document.getElementById('timeLeft').textContent = 'Time Left: ' + (timeLeft / 1000).toFixed(2) + ' s';
            document.getElementById('speed').textContent = 'Speed: ' + (speed / 1024).toFixed(2) + ' KB/s';
          }
        };

        xhr.onload = function() {
          var response;
          try {
            response = JSON.parse(xhr.responseText);
          } catch (e) {
            response = { status: 'error', message: 'Invalid response from server' };
          }

          var progressWrapper = document.getElementById('progressWrapper');
          var uploadButton = document.getElementById('uploadButton');
          var toast = new bootstrap.Toast(document.getElementById('uploadToast'));
          var toastBody = document.getElementById('toastBody');

          // Hide progress bar
          progressWrapper.style.display = 'none';

          if (xhr.status === 200 && response.status === 'success') {
            // Show success toast
            toastBody.textContent = response.message;
            toast.show();
          } else {
            // Show error toast
            toastBody.textContent = response.message || 'An error occurred.';
            toast.show();
          }

          // Show button again after 5 seconds
          setTimeout(function() {
            uploadButton.style.display = 'block';
          }, 5000);
        };

        xhr.onerror = function() {
          var progressWrapper = document.getElementById('progressWrapper');
          var uploadButton = document.getElementById('uploadButton');
          var toast = new bootstrap.Toast(document.getElementById('uploadToast'));
          var toastBody = document.getElementById('toastBody');

          // Hide progress bar
          progressWrapper.style.display = 'none';

          // Show error toast
          toastBody.textContent = 'Network error occurred.';
          toast.show();

          // Show button again after 5 seconds
          setTimeout(function() {
            uploadButton.style.display = 'block';
          }, 5000);
        };

        xhr.send(formData);
      });
    </script>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>