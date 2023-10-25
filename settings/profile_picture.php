<?php
require_once('../auth.php');

// Get the user's current profile picture from the database
$db = new SQLite3('../database.sqlite');
$email = $_SESSION['email'];
$stmt = $db->prepare('SELECT pic FROM users WHERE email = :email');
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);
$current_pic = $row['pic'];
$db->close();

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Get the user's ID from the session
  $email = $_SESSION['email'];

  // Check if a file was uploaded
  if (!empty($_FILES['pic']['name'])) {

    // Define the upload directory and file path
    $upload_dir = '../profile_pictures/';
    $file_name = basename($_FILES['pic']['name']);
    $file_path = $upload_dir . $file_name;

    // Resize and move the uploaded file to the upload directory
    $temp_file = $_FILES['pic']['tmp_name'];
    list($width, $height) = getimagesize($temp_file);
    $aspect_ratio = $width / $height;
    $new_width = 200;
    $new_height = round($new_width / $aspect_ratio);
    if ($new_height > 200) {
      $new_height = 200;
      $new_width = round($new_height * $aspect_ratio);
    }
    $temp_image = imagecreatetruecolor($new_width, $new_height);
    switch(mime_content_type($temp_file)) {
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
    switch(mime_content_type($temp_file)) {
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
      $db = new SQLite3('../database.sqlite');
      $stmt = $db->prepare('UPDATE users SET pic = :pic WHERE email = :email');
      $stmt->bindValue(':pic', $file_path, SQLITE3_TEXT);
      $stmt->bindValue(':email', $email, SQLITE3_TEXT);
      $stmt->execute();
      $db->close();

      // Redirect to the profile page
      header('Location: profile_picture.php');
      exit();

    } else {
      $error_msg = 'Failed to upload the file.';
    }

  } else {
    $error_msg = 'Please select a file to upload.';
  }
}
?>

    <?php include('setheader.php'); ?>
    <?php if (isset($error_msg)): ?>
      <div class="alert alert-danger" role="alert">
        Error: <?php echo $error_msg; ?>
      </div>
    <?php endif; ?>
    <div class="mt-3">
    <h5 class="card-title text-center text-dark fw-bold mb-3">Add Profile Picture</h5>
      <div class="roow">
        <div class="cool-6">
          <div class="caard">
            <img src="<?php echo !empty($current_pic) ? $current_pic : "../icon/propic.png"; ?>" alt="Current Profile Picture" class="img-thumbnail" style="width: 100%; height: 300px; object-fit: cover;">
          </div>
        </div>
        <div class="cool-6">
          <div class="caard container">
            <form method="post" enctype="multipart/form-data">
              <div class="form-group mb-2">
                <label for="pic" class="form-label text-dark fw-bold">Select a profile picture:</label>
                <input type="file" id="pic" name="pic" class="form-control rounded-3 border text-dark fw-bold border-4">
              </div>
              <button type="submit" class="btn btn-primary w-100 fw-bold" name="submit">Apply</button>
            </form>
          </div> 
        </div>
      </div>
    </div>
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
        height: 350px;
        width: 100%;
        object-fit: cover;
      }

      @media (min-width: 768px) {
        .btn-w {
          width: 291px;
        }
      } 
      
      @media (max-width: 767px) {
        .cool-6 {
          width: 100%;
          padding: 0;
        }
  
        .btn-w {
          width: 153px;
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
    <?php include('end.php'); ?>