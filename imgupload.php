<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="mt-2">
      <div id="preview-container" class="mb-2"></div>
      <div class="caard container">
        <div id="drop-zone" class="drop-zone fw-bold text-secondary mb-2 rounded-3 border-4 text-center">
          <div class="d-flex flex-column align-items-center">
            <div class="mb-4 mt-2">
              <i class="bi bi-filetype-png me-4 display-4"></i>
              <i class="bi bi-filetype-jpg me-4 display-4"></i>
              <i class="bi bi-filetype-gif display-4"></i>
            </div>
            <label for="file-ip-1">
              <input class="form-control mb-2 border rounded-3 text-secondary fw-bold border-4" type="file" name="image[]" id="file-ip-1" accept="image/*" multiple required>
              <p style="word-break: break-word;" class="badge bg-secondary opacity-50 text-wrap" style="font-size: 15px;">Drag and drop files here</p>
              <p><small><i class="bi bi-info-circle-fill"></i> type of extension you can upload: jpg, jpeg, png, gif</small></p>
              <div class="total-size"></div>
            </label>
          </div>
        </div>
        <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fw-bold fs-5" id="exampleModalLabel">Upload</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form id="upload-form" enctype="multipart/form-data">
                  <div class="form-floating mb-2">
                    <input class="form-control border rounded-3 text-secondary fw-bold border-4" type="text" name="title" id="title" placeholder="Enter title for your image" maxlength="50" required>  
                    <label for="title" class="text-secondary fw-bold">Enter title for your image</label>
                  </div>
                  <div class="form-floating mb-2">
                    <textarea class="form-control border rounded-3 text-secondary fw-bold border-4" type="text" name="imgdesc" id="imgdesc" placeholder="Enter description for your image" maxlength="400" style="height: 200px;" required></textarea>
                    <label for="imgdesc" class="text-secondary fw-bold">Enter description for your image</label>
                  </div>
                  <div class="form-floating mb-2">
                    <input class="form-control border rounded-3 text-secondary fw-bold border-4" type="text" name="tags" id="tags" placeholder="Enter tag for your image" maxlength="180" required>  
                    <label for="tags" class="text-secondary fw-bold">Enter tag for your image</label>
                  </div>
                  <div class="form-floating mb-2">
                    <input class="form-control border rounded-3 text-secondary fw-bold border-4" type="text" name="link" id="link" placeholder="Enter link for your image" maxlength="140">  
                    <label for="link" class="text-secondary fw-bold">Enter link for your image</label>
                  </div>
                  <select class="form-select text-secondary fw-bold mb-2" name="type" aria-label="Large select example" required>
                    <option value="safe" selected>Safe For Works</option>
                    <option value="nsfw">NSFW/R-18</option>
                  </select> 
                  <button class="btn btn-lg btn-primary fw-bold w-100" id="upload-button" type="submit"><i class="bi bi-cloud-arrow-up-fill"></i></button>
                </form>
                <div id="progress-bar-container" class="progress fw-bold mt-2" style="height: 30px; display: none;">
                  <div id="progress-bar" class="progress-bar progress-bar progress-bar-animated" style="height: 30px;" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <button type="button" class="btn btn-primary w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#uploadModal">
          UPLOAD
        </button>
      </div>
    </div>
    <div class="mt-4"></div>
    <!-- Metadata Modal -->
    <div class="modal fade" id="metadataModal" tabindex="-1" aria-labelledby="metadataModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-bold" id="metadataModalLabel">Image Metadata</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="metadata-container"></div>
          </div>
        </div>
      </div>
    </div>
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
        fileInput.files = files;
        handleFiles(files);
      });
  
      fileInput.addEventListener('change', function(e) {
        var files = e.target.files;
        handleFiles(files);
      });
  
      function handleFiles(files) {
        var fileCount = files.length;
        var message = fileCount > 1 ? fileCount + ' images selected' : files[0].name;
        var messageElement = dropZone.querySelector('p');
        messageElement.textContent = message;
  
        var existingTotalSizeElement = dropZone.querySelector('.total-size');
        if (existingTotalSizeElement) {
          existingTotalSizeElement.remove();
        }
  
        var totalSize = 0;
  
        for (var i = 0; i < files.length; i++) {
          var fileSize = files[i].size;
          totalSize += fileSize;
        }
  
        var totalSizeInMB = totalSize / (1024 * 1024);
        var totalSizeText = Math.round(totalSizeInMB * 100) / 100 + ' MB';
  
        var totalSizeContainer = document.createElement('div');
        totalSizeContainer.classList.add('total-size');
  
        var totalSizeLabel = document.createElement('small');
        totalSizeLabel.classList.add('fw-bold');
        totalSizeLabel.textContent = 'Total Images Size: ' + totalSizeText;
  
        totalSizeContainer.appendChild(totalSizeLabel);
        dropZone.appendChild(totalSizeContainer);
  
        showPreview(files);
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
        xhr.open('POST', 'upload.php');
        xhr.upload.addEventListener('progress', function(e) {
          var percent = Math.round((e.loaded / e.total) * 100);
          progressBar.style.width = percent + '%';
          progressBar.textContent = percent + '%';
        });
  
        xhr.onreadystatechange = function() {
          if (xhr.readyState === XMLHttpRequest.DONE) {
            progressBarContainer.style.display = 'none';
            uploadButton.disabled = false;
  
            if (xhr.status === 200) {
              showSuccessMessage();
            } else {
              showErrorMessage();
            }
          }
        };
  
        xhr.send(formData);
        progressBarContainer.style.display = 'block';
        uploadButton.disabled = true;
      }
  
      function showSuccessMessage() {
        var successContainer = document.createElement('div');
        successContainer.classList.add('container', 'mt-3');
  
        var alertContainer = document.createElement('div');
        alertContainer.classList.add('alert', 'alert-success', 'p-3');
        alertContainer.textContent = 'Images uploaded successfully.';
  
        successContainer.appendChild(alertContainer);
  
        var homeLink = document.createElement('a');
        homeLink.classList.add('btn', 'btn-primary', 'mt-2', 'text-center', 'fw-bold', 'w-100', 'mb-5');
        homeLink.href = 'index.php';
        homeLink.textContent = 'Go back to homepage';
  
        successContainer.appendChild(homeLink);
  
        document.body.appendChild(successContainer);
  
        var uploadModal = document.getElementById('uploadModal');
        var modal = bootstrap.Modal.getInstance(uploadModal);
  
        modal.hide();
  
        modal.addEventListener('hidden.bs.modal', function () {
          uploadForm.reset();
          progressBar.style.width = '0%';
          progressBar.textContent = '';
        });
      }
  
      function showErrorMessage() {
        var errorContainer = document.createElement('div');
        errorContainer.classList.add('container', 'mt-3');

        var errorAlert = document.createElement('div');
        errorAlert.classList.add('alert', 'alert-danger', 'p-3');
        errorAlert.textContent = 'An error occurred during the upload. Please try again.';

        errorContainer.appendChild(errorAlert);
        document.body.appendChild(errorContainer);
      }
  
      function showPreview(files) {
        var container = document.getElementById("preview-container");
        container.innerHTML = "";
  
        if (window.innerWidth >= 768) {
          container.style.display = "flex";
          container.style.flexWrap = "wrap";  // Added flexWrap property
          container.style.justifyContent = "center";
          container.style.gridGap = "2px";
          container.style.marginRight = "3px";
          container.style.marginLeft = "3px";
        } else {
          container.style.display = "grid";
          container.style.gridTemplateColumns = "repeat(auto-fit, minmax(150px, 1fr))";
          container.style.gridGap = "2px";
          container.style.justifyContent = "center";
          container.style.marginRight = "3px";
          container.style.marginLeft = "3px";
        }
  
        for (var i = 0; i < files.length; i++) {
          var imgContainer = document.createElement("div");
          imgContainer.classList.add("position-relative", "d-inline-block");
  
          var img = document.createElement("img");
          img.style.width = window.innerWidth >= 768 ? "150px" : "100%";
          img.style.height = window.innerWidth >= 768 ? "150px" : (files.length > 1 ? "200px" : "400px");
          img.classList.add("rounded", "object-fit-cover", "shadow");
          img.src = URL.createObjectURL(files[i]);
  
          var fileSize = files[i].size / (1024 * 1024); // Convert to MB
          var fileSizeRounded = Math.round(fileSize * 100) / 100; // Round to 2 decimal places
          var fileSizeText = fileSizeRounded + " MB";
  
          var fileSizeBadge = document.createElement("span");
          fileSizeBadge.classList.add("badge", "rounded-1", "opacity-75", "bg-dark", "position-absolute", "bottom-0", "start-0", "mb-1", "ms-1", "fw-bold");
          fileSizeBadge.textContent = fileSizeText;
  
          var infoButton = document.createElement("button");
          infoButton.classList.add("btn", "btn-sm", "opacity-75", "btn-dark", "position-absolute", "top-0", "end-0", "mt-1", "me-1");
          infoButton.innerHTML = '<i class="bi bi-info-circle-fill"></i>';
  
          // Store the file object as a custom property on the info button
          infoButton.file = files[i];
  
          imgContainer.appendChild(img);
          imgContainer.appendChild(fileSizeBadge);
          imgContainer.appendChild(infoButton);
          container.appendChild(imgContainer);
  
          // Add click event listener to each image
          img.addEventListener("click", function () {
            // Get the metadata and display it in the modal
            displayMetadata(this.file);
          });
  
          // Add click event listener to info button
          infoButton.addEventListener("click", function () {
            // Get the metadata from the custom file property and display it in the modal
            displayMetadata(this.file);
          });
        }
      }
  
      function displayMetadata(file) {
        var metadataContainer = document.getElementById("metadata-container");
        metadataContainer.innerHTML = "";
  
        var fileName = file.name;
        var fileSize = file.size / (1024 * 1024); // Convert to MB
        var fileSizeRounded = Math.round(fileSize * 100) / 100; // Round to 2 decimal places
        var fileSizeText = fileSizeRounded + " MB";
        var fileType = file.type;

        // Image
        var img = new Image();
        img.src = URL.createObjectURL(file);
        img.classList.add('rounded', 'mb-3');
        img.style.width = '100%'; // Set width to 100%
        img.style.height = 'auto'; // Maintain aspect ratio
        metadataContainer.appendChild(img);

        // Image Name
        var fileNameElement = document.createElement("p");
        fileNameElement.style.wordBreak = "break-word";
        fileNameElement.classList.add("fw-bold");
        fileNameElement.innerHTML = 'Image Name: ' + fileName;
        metadataContainer.appendChild(fileNameElement);
  
        // Image Size
        var fileSizeElement = document.createElement("p");
        fileSizeElement.classList.add("fw-bold");
        fileSizeElement.innerHTML = 'Image Size: ' + fileSizeText;
        metadataContainer.appendChild(fileSizeElement);
  
        // Image Type
        var fileTypeElement = document.createElement("p");
        fileTypeElement.classList.add("fw-bold");
        fileTypeElement.innerHTML = 'Image Type: ' + fileType;
        metadataContainer.appendChild(fileTypeElement);
  
        // Image Date
        var imageDateElement = document.createElement("p");
        imageDateElement.classList.add("fw-bold");
        imageDateElement.innerHTML = 'Image Date: ' + formatDate(file.lastModifiedDate);
        metadataContainer.appendChild(imageDateElement);
  
        // Image Resolution
        var img = new Image();
        img.src = URL.createObjectURL(file);
        img.onload = function () {
          var imageResolutionElement = document.createElement("p");
          imageResolutionElement.classList.add("fw-bold");
          imageResolutionElement.innerHTML =
            'Image Resolution: ' + this.naturalWidth + 'x' + this.naturalHeight;
          metadataContainer.appendChild(imageResolutionElement);

          // PPI (Pixels Per Inch)
          var ppiElement = document.createElement("p");
          ppiElement.classList.add("fw-bold");
          var ppi = calculatePPI(this.naturalWidth, this.naturalHeight, fileSizeRounded);
          ppiElement.innerHTML = 'Pixels Per Inch (PPI): ' + ppi;
          metadataContainer.appendChild(ppiElement);

          // Bit Color
          var bitColorElement = document.createElement("p");
          bitColorElement.classList.add("fw-bold");
          var bitColor = calculateBitColor(fileType);
          bitColorElement.innerHTML = 'Bit Color: ' + bitColor;
          metadataContainer.appendChild(bitColorElement);

          // Color Profiles
          var colorProfileElement = document.createElement("p");
          colorProfileElement.classList.add("fw-bold");
          var colorProfiles = getColorProfiles(fileType);
          colorProfileElement.innerHTML = 'Color Profiles: ' + colorProfiles.join(", ");
          metadataContainer.appendChild(colorProfileElement);
        };

        // JFIF Version
        var jfifVersionElement = document.createElement("p");
        jfifVersionElement.classList.add("fw-bold");
        getJfifVersion(file, function(jfifVersion) {
          jfifVersionElement.innerHTML = 'JFIF Version: ' + jfifVersion;
        });
        metadataContainer.appendChild(jfifVersionElement);
  
        var modal = new bootstrap.Modal(document.getElementById("metadataModal"));
        modal.show();

        // PNG Version
        var pngVersionElement = document.createElement("p");
        pngVersionElement.classList.add("fw-bold");
        getPngVersion(file, function(pngVersion) {
          pngVersionElement.innerHTML = 'PNG Version: ' + pngVersion;
        });
        metadataContainer.appendChild(pngVersionElement);

        // GIF Version
        var gifVersionElement = document.createElement("p");
        gifVersionElement.classList.add("fw-bold");
        getGifVersion(file, function(gifVersion) {
          gifVersionElement.innerHTML = 'GIF Version: ' + gifVersion;
        });
        metadataContainer.appendChild(gifVersionElement);
      }
  
      function formatDate(date) {
        var options = { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' };
        return date.toLocaleDateString(undefined, options);
      }
  
      function getJfifVersion(file, callback) {
        var reader = new FileReader();
        reader.onload = function(e) {
          var view = new DataView(e.target.result);
          if (view.getUint16(0, false) !== 0xFFD8) {
            callback("Not a valid JPEG file.");
            return;
          }
  
          var offset = 2;
          var marker;
          while (offset < view.byteLength) {
            marker = view.getUint16(offset, false);
            if (marker === 0xFFE0) {
              if (view.getUint32(offset + 4, false) === 0x4A464946) {
                var majorVersion = view.getUint8(offset + 9);
                var minorVersion = view.getUint8(offset + 10);
                callback(majorVersion + "." + minorVersion);
                return;
              }
            }
            offset += 2 + view.getUint16(offset + 2, false);
          }
  
          callback("Unknown");
        };
        reader.readAsArrayBuffer(file);
      }

      function getPngVersion(file, callback) {
        var reader = new FileReader();
        reader.onload = function(e) {
          var view = new DataView(e.target.result);
          var signature = view.getUint32(0, false);
          if (signature !== 0x89504E47) {
            callback("Not a valid PNG file.");
            return;
          }
    
          var version = view.getUint8(4) + "." + view.getUint8(5) + "." + view.getUint8(6);
          callback(version);
        };
        reader.readAsArrayBuffer(file);
      }

      function getGifVersion(file, callback) {
        var reader = new FileReader();
        reader.onload = function(e) {
          var view = new DataView(e.target.result);
          var signature = String.fromCharCode.apply(null, new Uint8Array(view.buffer, 0, 6));
          if (signature !== "GIF87a" && signature !== "GIF89a") {
            callback("Not a valid GIF file.");
            return;
          }

          var version = signature.substring(3);
          callback(version);
        };
        reader.readAsArrayBuffer(file);
      }

      function calculatePPI(width, height, fileSize) {
        // Calculate PPI using width, height, and file size information
        // Example calculation:
        var ppi = Math.round(Math.sqrt((width * width + height * height) / fileSize));
        return ppi;
      }

      function calculateBitColor(fileType) {
        // Determine bit color based on the file type
        // Example implementation:
        if (fileType === 'image/jpeg') {
          return '8-bit'; // JPEG images are typically 8-bit
        } else if (fileType === 'image/png') {
          return '8-bit or 16-bit'; // PNG images can be either 8-bit or 16-bit
        } else {
          return 'Unknown';
        }
      }

      function getColorProfiles(fileType) {
        // Determine color profiles based on the file type
        // Example implementation:
        if (fileType === 'image/jpeg' || fileType === 'image/png') {
          return ['sRGB', 'AdobeRGB']; // Assuming sRGB and AdobeRGB color profiles for JPEG and PNG
        } else if (fileType === 'image/tiff') {
          return ['sRGB', 'AdobeRGB', 'DCI-P3']; // Assuming sRGB, AdobeRGB, and DCI-P3 color profiles for TIFF
        } else {
          return ['Unknown'];
        }
      } 
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
