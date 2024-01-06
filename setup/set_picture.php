<?php
// Check if the user is logged in
require_once('../auth.php');

// Get the user's current profile picture from the database
$db = new PDO('sqlite:../database.sqlite');
$email = $_SESSION['email'];
$stmt = $db->prepare('SELECT pic FROM users WHERE email = :email');
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$current_pic = $row['pic'];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Get the user's ID from the session
  $email = $_SESSION['email'];

  // Check if a file was uploaded
  if (!empty($_FILES['pic']['name'])) {

    // Define the upload directory and file path
    $upload_dir = '../profile_pictures/';
    $file_extension = pathinfo($_FILES['pic']['name'], PATHINFO_EXTENSION);
    $file_name = uniqid('pic_') . '.' . $file_extension;
    $file_path = $upload_dir . $file_name;

    // Resize and move the uploaded file to the upload directory
    $temp_file = $_FILES['pic']['tmp_name'];
    list($width, $height) = getimagesize($temp_file);

    // Set the desired dimensions with original aspect ratio
    $new_width = 400;
    $new_height = round($new_width * $height / $width);

    $temp_image = imagecreatetruecolor($new_width, $new_height);
    switch (mime_content_type($temp_file)) {
      case 'image/jpeg':
        $source_image = imagecreatefromjpeg($temp_file);
        break;
      case 'image/png':
        $source_image = imagecreatefrompng($temp_file);
        break;
      case 'image/gif':
        $source_image = imagecreatefromgif($temp_file);
        break;
    }

    imagecopyresampled($temp_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    switch (mime_content_type($temp_file)) {
      case 'image/jpeg':
        imagejpeg($temp_image, $temp_file);
        break;
      case 'image/png':
        imagepng($temp_image, $temp_file);
        break;
      case 'image/gif':
        imagegif($temp_image, $temp_file);
        break;
    }

    if (move_uploaded_file($temp_file, $file_path)) {

      // Delete the user's previous profile picture
      if (!empty($current_pic) && file_exists($current_pic)) {
        unlink($current_pic);
      }

      // Update the user's profile picture in the database
      $stmt = $db->prepare('UPDATE users SET pic = :pic WHERE email = :email');
      $stmt->bindParam(':pic', $file_path, PDO::PARAM_STR);
      $stmt->bindParam(':email', $email, PDO::PARAM_STR);
      $stmt->execute();

      // Redirect to the profile page
      header('Location: set_picture.php');
      exit();

    } else {
      $error_msg = 'Failed to upload the file.';
    }

  } else {
    $error_msg = 'Please select a file to upload.';
  }
}

$db = null; // Close the PDO connection
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container-fluid">
      <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger" role="alert">
          Error: <?php echo $error_msg; ?>
        </div>
      <?php endif; ?>
      <div class="mt-3">
        <h5 class="text-center text-dark fw-bold mb-3">Add Your Profile Picture</h5>
        <div class="row">
          <div class="col-md-5 mb-2">
            <div class="ratio ratio-1x1">
              <a data-bs-toggle="modal" data-bs-target="#originalImage"><img id="previewImage" src="<?php echo !empty($current_pic) ? $current_pic : "../icon/bg.png"; ?>" alt="Current Background Picture" class="img-thumbnail w-100 h-100 object-fit-cover"></a>
            </div>
          </div>
          <div class="col-md-7">
            <div class="container-fluid">
              <form method="post" enctype="multipart/form-data">
                <div class="form-group mb-2">
                  <label for="pic" class="form-label text-dark fw-bold">Select a file:</label>
                  <input type="file" id="pic" name="pic" class="form-control rounded-3 border text-dark fw-bold border-4" onchange="previewFile()">
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-bold" name="submit">Apply</button>
              </form>
              <div class="btn-group w-100 gap-2 mt-2">
                <a href="../index.php" class="btn btn-danger w-50 rounded fw-bold">Skip</a>
                <a href="set_background.php" class="btn btn-primary w-50 rounded fw-bold">Next</a>
              </div>
            </div> 
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="originalImage" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="modal-body position-relative">
            <img class="object-fit-contain h-100 w-100 rounded" src="<?php echo !empty($current_pic) ? $current_pic : "../icon/bg.png"; ?>">
            <button type="button" class="btn border-0 position-absolute end-0 top-0 me-2 mt-3" data-bs-dismiss="modal"><i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i></button>
            <a class="btn btn-primary fw-bold w-100 mt-2" href="<?php echo !empty($current_pic) ? $current_pic : "../icon/bg.png"; ?>" download>Download Cover Image</a>
          </div>
        </div>
      </div>
    </div>
    
    <script>
      function previewFile() {
        const preview = document.getElementById('previewImage');
        const fileInput = document.getElementById('bgpic');
        const file = fileInput.files[0];
        const reader = new FileReader();

        reader.onloadend = function () {
          preview.src = reader.result;
        }

        if (file) {
          reader.readAsDataURL(file);
        } else {
          preview.src = "../icon/bg.png";
        }
      }
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>