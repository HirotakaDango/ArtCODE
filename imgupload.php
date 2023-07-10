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
    <section class="mt-2">
      <h2 class="mt-3 mb-3 text-center text-secondary fw-bold"><i class="bi bi-cloud-arrow-up-fill"></i> UPLOAD IMAGE</h2>
      <div class="roow">
        <div class="cool-6">
          <div class="caard">
            <div id="preview-container"></div>
          </div>
        </div>
        <div class="cool-6">
          <div class="caard container">
            <form action="upload.php" method="post" enctype="multipart/form-data">
              <input class="form-control mb-2 border rounded-3 text-secondary fw-bold border-4" type="file" name="image[]" id="file-ip-1" accept="image/*" onchange="showPreview(event);" multiple required>
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
              <input class="btn btn-lg btn-primary fw-bold w-100" type="submit" name="submit" value="upload">
            </form> 
          </div> 
        </div>
      </div>
    </section>
    <div class="mt-5"></div>
    <style>
      .roow {
        display: flex;
        flex-wrap: wrap;
      }

      .cool-6 {
        width: 50%;
        padding: 0 15px;
        box-sizing: border-box;
      }

      .caard {
        background-color: #fff;
        margin-bottom: 15px;
      }

      .art {
        border: 2px solid lightgray;
        border-radius: 10px;
        object-fit: cover;
      }
      
      .text-stroke {
        -webkit-text-stroke: 1px;
      }

      @media (max-width: 768px) {
        .cool-6 {
          width: 100%;
          padding: 0;
        }
  
        .art {
          border-top: 2px solid lightgray;
          border-bottom: 2x solid lightgray;
          border-left: none;
          border-right: none;
          border-radius: 0;
          object-fit: cover;
        }
      }
    </style>
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
    <script>
      function showPreview(event) {
        var container = document.getElementById("preview-container");
        container.innerHTML = "";
        var imgHeight = window.innerWidth < 768 ? (event.target.files.length > 1 ? 200 : 400) : (event.target.files.length > 2 ? 150 : 424);

        for (var i = 0; i < event.target.files.length; i++) {
          var imgContainer = document.createElement("div");
          imgContainer.classList.add("position-relative", "d-inline-block");

          var img = document.createElement("img");
          img.style.width = "100%";
          img.style.height = imgHeight + "px";
          img.classList.add("rounded", "object-fit-cover", "shadow");
          img.src = URL.createObjectURL(event.target.files[i]);

          var fileSize = event.target.files[i].size / (1024 * 1024); // Convert to MB
          var fileSizeRounded = Math.round(fileSize * 100) / 100; // Round to 2 decimal places
          var fileSizeText = fileSizeRounded + " MB";

          var fileSizeBadge = document.createElement("span");
          fileSizeBadge.classList.add("badge", "rounded-1", "opacity-75", "bg-dark", "position-absolute", "bottom-0", "start-0", "mb-1", "ms-1", "fw-bold");
          fileSizeBadge.textContent = fileSizeText;

          var infoButton = document.createElement("button");
          infoButton.classList.add("btn", "btn-sm", "opacity-75", "btn-dark", "position-absolute", "top-0", "end-0", "mt-1", "me-1");
          infoButton.innerHTML = '<i class="bi bi-info-circle-fill"></i>';

          // Store the file object as a custom property on the info button
          infoButton.file = event.target.files[i];

          imgContainer.appendChild(img);
          imgContainer.appendChild(fileSizeBadge);
          imgContainer.appendChild(infoButton);
          container.appendChild(imgContainer);

          // Add click event listener to each image
          img.addEventListener("click", function() {
            // Get the metadata and display it in the modal
            displayMetadata(event.target.files[i]);
          });

          // Add click event listener to info button
          infoButton.addEventListener("click", function() {
            // Get the metadata from the custom file property and display it in the modal
            displayMetadata(this.file);
          });
        }

        container.style.display = "grid";
        container.style.gridTemplateColumns = "repeat(auto-fit, minmax(150px, 1fr))";
        container.style.gridGap = "2px";
        container.style.justifyContent = "center";
        container.style.marginRight = "3px";
        container.style.marginLeft = "3px";
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

        // Open the metadata modal
        var modal = new bootstrap.Modal(document.getElementById("metadataModal"));
        modal.show();
      }

      function formatDate(date) {
       var options = { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' };
       return date.toLocaleDateString(undefined, options);
      }
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
