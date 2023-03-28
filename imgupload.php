<?php
  session_start();
  if (!isset($_SESSION['username'])) {
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  </head>
  <body>
    <?php include('header.php'); ?>
    <section class="mt-2">
      <h2 class="mt-3 mb-3 text-center" style="font-family: sans-serif; color: gray; font-weight: 800;">UPLOAD IMAGE</h2>
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
                <textarea class="form-control border rounded-3 text-secondary fw-bold border-4" type="text" name="imgdesc" placeholder="Enter description for your image" maxlength="400" style="height: 100px;" required></textarea>
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
    <script>
      function showPreview(event) {
        // Get the container for the preview images
        var container = document.getElementById("preview-container");

        // Clear any existing preview images
        container.innerHTML = "";

        // Set the height of the image based on the viewport width and number of images
        var imgHeight = window.innerWidth < 768 ? (event.target.files.length > 1 ? 200 : 400) : (event.target.files.length > 1 ? 100 : 424);

        // Loop through all selected files
        for (var i = 0; i < event.target.files.length; i++) {
          // Create a new image element for each file
          var img = document.createElement("img");
          img.style.width = "100%";
          img.style.height = imgHeight + "px";
          img.style.objectFit = "cover";
          img.style.marginRight = "2px";
          img.style.borderRadius = "5px";
          img.style.border = "2px solid lightgray";
 
          // Set the source of the image to the URL of the file
          img.src = URL.createObjectURL(event.target.files[i]);

          // Add the image element to the container
          container.appendChild(img);
        }
  
        // Set the grid display properties
        container.style.display = "grid";
        container.style.gridTemplateColumns = "repeat(auto-fit, minmax(100px, 1fr))";
        container.style.gridGap = "2px";
        container.style.justifyContent = "center";
        container.style.marginRight = "3px";
        container.style.marginLeft = "3px";
      }

    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>
