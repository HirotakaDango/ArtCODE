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
    <?php include('header.php'); ?>
    <div class="container-fluid mt-3">
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
        xhr.open('POST', 'upload_code.php', true); // Specify the correct path to the PHP file

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
