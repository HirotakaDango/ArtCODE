<?php
session_start();

class Settings {
  private $db;

  function __construct() {
    // Connect to the SQLite database
    $this->db = new SQLite3('database.sqlite');
  }

  function checkLoggedIn() {
    // Check if the user is logged in
    if (!isset($_SESSION['username'])) {
      header("Location: session.php");
      exit;
    }
  }

  function updateArtist() {
    // Check if the form was submitted
    if (isset($_POST['submit'])) {
      $username = $_SESSION['username'];
      $artist = htmlspecialchars($_POST['artist']);

      // Update the user's artist name in the database
      $stmt = $this->db->prepare("UPDATE users SET artist = :artist WHERE username = :username");
      $stmt->bindValue(':username', $username);
      $stmt->bindValue(':artist', $artist);
      $stmt->execute();

      // Store the new artist name in the session for future use
      $_SESSION['artist'] = $artist;

      // Redirect the user to the homepage
      header("Location: yourname.php");
      exit;
    }
  }

  function getCurrentArtist() {
    // Get the user's current artist name from the database
    $username = $_SESSION['username'];
    $stmt = $this->db->prepare("SELECT artist FROM users WHERE username = :username");
    $stmt->bindValue(':username', $username);
    $result = $stmt->execute();
    $user = $result->fetchArray();
    $artist = htmlspecialchars($user['artist']);
    return $artist;
  }
}

$settings = new Settings();
$settings->checkLoggedIn();
$settings->updateArtist();
$artist = $settings->getCurrentArtist();

?>

<!DOCTYPE html>
<html>
  <head>
    <title>Settings</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
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
                <a class="nav-link nav-center active" href="yourname.php">
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
                <a class="nav-link nav-center" href="bg.php">
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
    <div class="container">
      <h3 class="text-secondary text-center mt-4 fw-bold"><i class="bi bi-gear"></i> Change Your Name</h3>

      <form method="post" action="yourname.php">
        <div class="mb-3">
          <label for="artist" class="form-label text-secondary fw-bold">Your Name: <?php echo $artist; ?></label>
          <input type="text" class="form-control" id="artist" name="artist" value="<?php echo $artist; ?>" maxlength="40">
        </div>
        <div class="container">
          <header class="d-flex justify-content-center py-3">
            <ul class="nav nav-pills">
              <li class="nav-item"><button type="submit" class="btn btn-primary me-1 fw-bold" name="submit">Save</button></li>
              <li class="nav-item"><a href="setting.php" class="btn btn-danger ms-1 fw-bold">Back</a></li>
            </ul>
          </header>
        </div>
      </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>
