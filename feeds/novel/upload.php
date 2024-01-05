<?php
require_once('auth.php');

$db = new PDO('sqlite:../../database.sqlite');

$email = $_SESSION['email'];

// Check if an image was uploaded
if (isset($_FILES['image'])) {
  $image = $_FILES['image'];

  // Check if the image is valid
  if ($image['error'] == 0) {
    // Generate a unique file name
    $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;

    // Save the original image
    move_uploaded_file($image['tmp_name'], 'images/' . $filename);

    // Determine the image type and generate the thumbnail
    switch ($ext) {
      case 'jpg':
      case 'jpeg':
        $source = imagecreatefromjpeg('images/' . $filename);
        break;
      case 'png':
        $source = imagecreatefrompng('images/' . $filename);
        break;
      case 'gif':
        $source = imagecreatefromgif('images/' . $filename);
        break;
      case 'webp':
        $source = imagecreatefromwebp('images/' . $filename);
        break;
      case 'avif':
        $source = imagecreatefromavif('images/' . $filename);
        break;
      case 'bmp':
        $source = imagecreatefrombmp('images/' . $filename);
        break;
      case 'wbmp':
        $source = imagecreatefromwbmp('images/' . $filename);
        break; 
      default:
        echo "Error: Unsupported image format.";
        exit;
    }

    // Calculate the new dimensions based on the aspect ratio of the original image
    $source_width = imagesx($source);
    $source_height = imagesy($source);
    $source_ratio = $source_width / $source_height;
    $thumbnail_width = 300;
    $thumbnail_height = round($thumbnail_width / $source_ratio);

    // Calculate the target dimensions while maintaining the original aspect ratio
    if ($source_ratio > 1) {
      $thumbnail_height = round($thumbnail_width / $source_ratio);
    } else {
      $thumbnail_width = round($thumbnail_height * $source_ratio);
    }

    $thumbnail = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
    imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $source_width, $source_height);

    switch ($ext) {
      case 'jpg':
      case 'jpeg':
        imagejpeg($thumbnail, 'thumbnails/' . $filename);
        break;
      case 'png':
        imagepng($thumbnail, 'thumbnails/' . $filename);
        break;
      case 'gif':
        imagegif($thumbnail, 'thumbnails/' . $filename);
        break;
      case 'webp':
        imagewebp($thumbnail, 'thumbnails/' . $filename);
        break;
      case 'avif':
        imageavif($thumbnail, 'thumbnails/' . $filename);
        break;
      case 'bmp':
        imagebmp($thumbnail, 'thumbnails/' . $filename);
        break;
      case 'wbmp':
        imagewbmp($thumbnail, 'thumbnails/' . $filename);
        break;
      default:
        echo "Error: Unsupported image format.";
        exit;
    }

    // Retrieve the title and description values from the form submission
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
    $tags = filter_input(INPUT_POST, 'tags', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

    // Add the image to the database
    $stmt = $db->prepare("INSERT INTO novel (email, filename, title, description, content, tags, date) VALUES (:email, :filename, :title, :description, :content, :tags, :date)");
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':filename', $filename);
    $stmt->bindValue(':title', $title);
    $stmt->bindValue(':description', nl2br($description));
    $stmt->bindValue(':content', nl2br($content));
    $stmt->bindValue(':tags', $tags);

    // Bind the current date with the desired format
    $currentDate = date("Y/m/d");
    $stmt->bindValue(':date', $currentDate);

    $stmt->execute();

    header("Location: index.php");
    exit;
  } else {
    echo "Error uploading image.";
  }
}
?>

<!DOCTYPE html>
<html data-bs-theme="dark">
  <head>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload</title>
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include ('header.php'); ?>
    <div class="container-fluid">
      <form method="post" enctype="multipart/form-data">
        <?php if (isset($_GET['error'])): ?>
          <p><?php echo $_GET['error']; ?></p>
        <?php endif ?>
        <div class="row featurette">
          <div class="col-md-3 order-md-1 mb-2 pe-md-0" style="height: 500px;">
            <div id="file-preview-container" class="d-flex align-items-center justify-content-center h-100 border border-3 rounded-4">
              <div class="text-center">
                <h6><i class="bi bi-image fs-1"></i></h6>
                <h6>Your image cover here!</h6>
              </div>
            </div>
          </div>
          <div class="col-md-9 order-md-2 ps-md-2">
            <input class="form-control border border-3 rounded-4 mb-2" type="file" name="image" type="file" id="file-ip-1" accept="image/*" onchange="showPreview(event);" required>
            <div class="input-group mb-2 gap-2">
              <div class="form-floating">
                <input class="form-control border border-3 rounded-4" type="text" id="floatingInput" placeholder="title" id="title" name="title" required>
                <label class="fw-medium" for="floatingInput">title</label>
              </div>
              <div class="form-floating">
                <input class="form-control border border-3 rounded-4" type="text" id="floatingInput" placeholder="tags" id="tags" name="tags" required>
                <label class="fw-medium" for="floatingInput">tags</label>
              </div>
            </div>
            <div class="form-floating mb-2">
              <textarea class="form-control border border-3 rounded-4" type="text" id="floatingTextarea" placeholder="description" id="description" style="height: 384px; max-height: 384px;" name="description" required></textarea>
              <label class="fw-medium" for="floatingTextarea">description</label>
            </div>
          </div>
        </div>
        <div class="form-floating mb-2">
          <textarea class="form-control vh-100 border border-3 rounded-4" type="text" id="floatingTextarea" placeholder="content" id="content" name="content" required></textarea>
          <label class="fw-medium" for="floatingTextarea">content</label>
        </div>
        <button class="btn btn-dark fw-bold w-100 border border-3 rounded-4" type="submit">upload</button>
      </form>
    </div>
    <div class="mt-5"></div>
    <script>
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
