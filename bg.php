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
    $aspect_ratio = $width / $height;
    $new_width = 400;
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
    <nav class="navbar fixed-top navbar-expand-md navbar-light bg-white shadow-sm">
      <div class="container-fluid">
        <button class="navbar-toggler1 d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
          <img src="icon/toggle1.svg" width="22" height="22">
        </button> 
        <a class="navbar-brand text-secondary fw-bold" href="index.php">
          ArtCODE
        </a>
          <div class="dropdown nav-right">
            <a class="nav-link px-2 text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle fs-5"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-start">
              <li><a class="dropdown-item text-secondary fw-bold" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a></li>
              <li><a class="dropdown-item text-secondary fw-bold" href="setting.php"><i class="bi bi-gear-fill"></i> Settings</a></li>
              <li><a class="dropdown-item text-secondary fw-bold" href="favorite.php"><i class="bi bi-heart-fill"></i> Favorites</a></li>
              <li><a class="dropdown-item text-secondary fw-bold" href="logout.php"><i class="bi bi-door-open-fill"></i> Logout</a></li>
            </ul>
          </div> 
        <div class="offcanvas offcanvas-start w-50" tabindex="-1" id="navbar" aria-labelledby="navbarLabel">
          <div class="offcanvas-header">
            <h5 class="offcanvas-title text-secondary" id="navbarLabel">Menu</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 fw-bold">
              <li class="nav-item">
                <a class="nav-link nav-center" href="setting.php">
                  <i class="bi bi-gear-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Back</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="yourname.php">
                  <i class="bi bi-person-circle fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Profile's Name</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="propic.php">
                  <i class="bi bi-person-square fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Profile's Photo</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center active" href="bg.php">
                  <i class="bi bi-images fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Background</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="desc.php">
                  <i class="bi bi-person-vcard fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Description</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="setpass.php">
                  <i class="bi bi-key-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Password</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="analytic.php">
                  <i class="bi bi-pie-chart-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Analytics</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="support.php">
                  <i class="bi bi-headset fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Support</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>
    <br><br>
    <style>
      @media (min-width: 768px) {
        .navbar-nav {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
        }
      
        .nav-center {
          margin-left: 15px;
          margin-right: 15px;
        }
      
        .nav-right {
          position: absolute;
          right: 10px;
          top: 10;
          align-items: center;
        }
      }
      
      @media (max-width: 767px) {
        .navbar-brand {
          position: static;
          display: block;
          text-align: center;
          margin: auto;
          transform: none;
        }

        .navbar-brand {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          font-size: 18px;
        }
      }
    
      .navbar {
        height: 45px;
      }
      
      .navbar-brand {
        font-size: 18px;
      }

      @media (min-width: 992px) {
        .navbar-toggler1 {
          display: none;
        }
      }
    
      .navbar-toggler1 {
        background-color: #ededed;
        border: none;
        font-size: 8px;
        margin-top: -2px;
        margin-left: 8px;
        border-radius: 5px;
        padding: 6px;
        transition: background-color 0.3s ease; 
      }

      .navbar-toggler1:hover {
        background-color: rgba(0,0,0,0.2);
      }

    </style>
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
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>

