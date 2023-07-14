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
        <form action="upload.php" method="post" enctype="multipart/form-data">
          <div id="drop-zone" class="drop-zone fw-bold text-secondary mb-2 rounded-3 border-4 text-center">
            <div class="d-flex flex-column align-items-center">
              <div class="mb-4 mt-2">
                <i class="bi bi-filetype-png me-4 display-4"></i>
                <i class="bi bi-filetype-jpg me-4 display-4"></i>
                <i class="bi bi-filetype-gif display-4"></i>
              </div>
              <label for="file-ip-1">
                <input class="form-control mb-2 border rounded-3 text-secondary fw-bold border-4" type="file" name="image[]" id="file-ip-1" accept="image/*" multiple required>
                <p class="badge bg-secondary opacity-50 text-wrap" style="font-size: 15px;">Drag and drop files here</p>
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
                  <div class="form-floating mb-2">
                    <input class="form-control border rounded-3 text-secondary fw-bold border-4" type="text" name="title" placeholder="Enter title for your image" maxlength="50" required>  
                    <label for="floatingInput" class="text-secondary fw-bold">Enter title for your image</label>
                  </div>
                  <div class="form-floating mb-2">
                    <textarea class="form-control border rounded-3 text-secondary fw-bold border-4" type="text" name="imgdesc" placeholder="Enter description for your image" maxlength="400" style="height: 200px;" required></textarea>
                    <label for="floatingInput" class="text-secondary fw-bold">Enter description for your image</label>
                  </div>
                  <div class="form-floating mb-2">
                    <input class="form-control border rounded-3 text-secondary fw-bold border-4" type="text" name="tags" placeholder="Enter tag for your image" maxlength="180" required>  
                    <label for="floatingInput" class="text-secondary fw-bold">Enter tag for your image</label>
                  </div>
                  <div class="form-floating mb-2">
                    <input class="form-control border rounded-3 text-secondary fw-bold border-4" type="text" name="link" placeholder="Enter link for your image" maxlength="140">  
                    <label for="floatingInput" class="text-secondary fw-bold">Enter link for your image</label>
                  </div>
                  <button class="btn btn-lg btn-primary fw-bold w-100" type="submit"><i class="bi bi-cloud-arrow-up-fill"></i></button>
                </div>
              </div>
            </div>
          </div>
          <button type="button" class="btn btn-primary w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#uploadModal">
            UPLOAD
          </button>
        </form> 
      </div>
    </div>
    <div class="mt-5"></div>
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
  
      // Handle file drop event
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
        fileInput.files = files; // Assign dropped files to the file input element
        handleFiles(files);
      });
  
      // Handle file selection event
      fileInput.addEventListener('change', function(e) {
        var files = e.target.files;
        handleFiles(files);
      });
  
      // Handle the files
      function handleFiles(files) {
        var fileCount = files.length;
        var message = fileCount > 1 ? fileCount + ' images selected' : files[0].name;
        var messageElement = dropZone.querySelector('p');
        messageElement.textContent = message;
  
        // Remove existing total size element if it exists
        var existingTotalSizeElement = dropZone.querySelector('.total-size');
        if (existingTotalSizeElement) {
          existingTotalSizeElement.remove();
        }
  
        // Calculate total size of selected images
        var totalSize = 0; // Initialize total size variable
  
        for (var i = 0; i < files.length; i++) {
          var fileSize = files[i].size;
          totalSize += fileSize;
        }
  
        // Convert total size to megabytes (MB)
        var totalSizeInMB = totalSize / (1024 * 1024);
        var totalSizeText = Math.round(totalSizeInMB * 100) / 100 + ' MB'; // Round to 2 decimal places
  
        // Create a separate HTML element for the total size
        var totalSizeContainer = document.createElement('div');
        totalSizeContainer.classList.add('total-size');
  
        var totalSizeLabel = document.createElement('p');
        totalSizeLabel.classList.add('fw-bold');
        totalSizeLabel.textContent = 'Total Images Size: ' + totalSizeText;
  
        totalSizeContainer.appendChild(totalSizeLabel);
  
        // Append the total size element to the drop zone
        dropZone.appendChild(totalSizeContainer);
  
        // Process the files here
        showPreview(files);
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

        // Image Name
        var fileNameElement = document.createElement("p");
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
        img.onload = function() {
          var imageResolutionElement = document.createElement("p");
          imageResolutionElement.classList.add("fw-bold");
          imageResolutionElement.innerHTML = 'Image Resolution: ' + this.naturalWidth + "x" + this.naturalHeight;
          metadataContainer.appendChild(imageResolutionElement);
        };
 
        // JFIF Version
        var jfifVersionElement = document.createElement("p");
        jfifVersionElement.classList.add("fw-bold");
        getJfifVersion(file, function(jfifVersion) {
          jfifVersionElement.innerHTML = 'JFIF Version: ' + jfifVersion;
        });
        metadataContainer.appendChild(jfifVersionElement);

        // Open the metadata modal
        var modal = new bootstrap.Modal(document.getElementById("metadataModal"));
        modal.show();
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
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
