<?php
// Check if the user is logged in
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: session.php');
  exit();
}

// Get the user's current background picture from the database
$db = new SQLite3('database.sqlite');
$username = $_SESSION['username'];
$stmt = $db->prepare('SELECT bgpic FROM users WHERE username = :username');
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);
$current_pic = $row['bgpic'];
$db->close();

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Get the user's ID from the session
  $username = $_SESSION['username'];

  // Check if a file was uploaded
  if (!empty($_FILES['bgpic']['name'])) {

    // Define the upload directory and file path
    $upload_dir = 'background_pictures/';
    $file_name = basename($_FILES['bgpic']['name']);
    $file_path = $upload_dir . $file_name;

    // Resize and move the uploaded file to the upload directory
    $temp_file = $_FILES['bgpic']['tmp_name'];
    list($width, $height) = getimagesize($temp_file);
    $new_width = 400;
    $new_height = 400;
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

      // Delete the user's previous background picture
      if (!empty($current_pic) && file_exists($current_pic)) {
        unlink($current_pic);
      }

      // Update the user's background picture in the database
      $db = new SQLite3('database.sqlite');
      $stmt = $db->prepare('UPDATE users SET bgpic = :bgpic WHERE username = :username');
      $stmt->bindValue(':bgpic', $file_path, SQLITE3_TEXT);
      $stmt->bindValue(':username', $username, SQLITE3_TEXT);
      $stmt->execute();
      $db->close();

      // Redirect to the profile page
      header('Location: bg.php');
      exit();

    } else {
      $error_msg = 'Failed to upload the file.';
    }

  } else {
    $error_msg = 'Please select a file to upload.';
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Change Profile Picture</title>
  <meta charset="UTF-8"> 
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
</head>
<body>

  <?php if (isset($error_msg)): ?>
    <div class="alert alert-danger" role="alert">
      Error: <?php echo $error_msg; ?>
    </div>
  <?php endif; ?>

  <div class="container my-3">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title text-center text-secondary fw-bold">Change Background Picture</h5>
            <img src="<?php echo $current_pic; ?>" alt="Current Profile Picture" class="img-thumbnail" style="width: 100%; height: 300px; object-fit: cover;">
            <br><br>

            <form method="post" enctype="multipart/form-data">
              <div class="form-group mb-3">
                <label for="bgpic" class="form-label text-secondary fw-bold">Select a new background picture:</label>
                <input type="file" id="bgpic" name="bgpic" class="form-control">
              </div>

              <div class="d-grid">
                <button type="submit" class="btn btn-primary fw-bold mb-2">Update</button>
                <a href="setting.php" type="button" class="btn btn-danger fw-bold">Back</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

</body>
</html>

