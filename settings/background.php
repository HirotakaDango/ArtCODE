<?php
// Check if the user is logged in
require_once('../auth.php');

// Get the user's current background picture from the database
$db = new PDO('sqlite:../database.sqlite');
$email = $_SESSION['email'];
$stmt = $db->prepare('SELECT bgpic FROM users WHERE email = :email');
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$current_pic = $row['bgpic'];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Get the user's ID from the session
  $email = $_SESSION['email'];

  // Check if a file was uploaded
  if (!empty($_FILES['bgpic']['name'])) {

    // Define the upload directory and file path
    $upload_dir = '../background_pictures/';
    $file_extension = pathinfo($_FILES['bgpic']['name'], PATHINFO_EXTENSION);
    $file_name = uniqid('bgpic_') . '.' . $file_extension;
    $file_path = $upload_dir . $file_name;

    // Resize and move the uploaded file to the upload directory
    $temp_file = $_FILES['bgpic']['tmp_name'];
    list($width, $height) = getimagesize($temp_file);

    // Set the desired dimensions with original aspect ratio
    $new_width = 2500;
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

      // Delete the user's previous background picture
      if (!empty($current_pic) && file_exists($current_pic)) {
        unlink($current_pic);
      }

      // Update the user's background picture in the database
      $stmt = $db->prepare('UPDATE users SET bgpic = :bgpic WHERE email = :email');
      $stmt->bindParam(':bgpic', $file_path, PDO::PARAM_STR);
      $stmt->bindParam(':email', $email, PDO::PARAM_STR);
      $stmt->execute();

      // Redirect to the profile page
      header('Location: background.php');
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

    <main id="swup" class="transition-main">
      <?php include('setheader.php'); ?>
        <?php if (isset($error_msg)): ?>
          <div class="alert alert-danger" role="alert">
            Error: <?php echo $error_msg; ?>
          </div>
        <?php endif; ?>
        <div class="container mb-5 mt-4">
          <div class="d-md-none mb-4">
            <div class="d-flex">
              <a class="text-decoration-none text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" href="/settings/">
                <i class="bi bi-chevron-left" style="-webkit-text-stroke: 2px;"></i>
              </a>
            </div>
          </div>
          <h3 class="fw-bold mb-3">
            Change Profile Background
          </h3>
          <p class="fw-semibold">Current profile background:</p>
          <div class="ratio ratio-16x9 my-4">
            <a data-bs-toggle="modal" data-bs-target="#originalImage"><img id="previewImage" src="<?php echo !empty($current_pic) ? $current_pic : "../icon/bg.png"; ?>" alt="Current Background Picture" class="img-thumbnail h-100 w-100 rounded-4 object-fit-cover"></a>
          </div>
          <div class="card border-0 bg-body-tertiary rounded-4 shadow-sm p-4 mb-4">
            <h5 class="fw-bold">
              <i class="bi bi-image-fill me-2"></i> Update Profile Background
            </h5>
            <p class="text-muted mb-4">Upload a new image file to change your profile background.</p>
            <form method="post" enctype="multipart/form-data">
              <div class="form-group mb-2">
                <label for="bgpic" class="form-label fw-bold">Select a file:</label>
                <input type="file" id="bgpic" name="bgpic" class="form-control rounded-3 border fw-bold border-4" onchange="previewFile()">
              </div>
              <button type="submit" class="btn btn-primary w-100 fw-bold" name="submit">Apply</button>
            </form>
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
      <?php include('end.php'); ?>
    </main>

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