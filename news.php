<?php
  session_start();
  if (!isset($_SESSION['username'])) {
    header("Location: session.php");
    exit;
  }

  // Connect to the SQLite database
  $db = new SQLite3('database.sqlite');

  // Retrieve all news items from the database
  $results = $db->query('SELECT * FROM news ORDER BY id DESC');
  $data = [];

  // Loop through each row in the result set
  while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $data[] = $row;
  }

  // Close the database connection
  $db->close();
?>

<!DOCTYPE html>
<html>
  <head>
    <title>My Table</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
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
                <a class="nav-link nav-center" href="index.php">
                  <i class="bi bi-house-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Home</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="forum-chat/index.php">
                  <i class="bi bi-chat-left-dots-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Forum</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="imgupload.php">
                  <i class="bi bi-cloud-arrow-up-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Uploads</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="popular.php">
                  <i class="bi bi-star-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Popular</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="tags.php">
                  <i class="bi bi-tags-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Tags</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="users.php">
                  <i class="bi bi-people-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Users</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center" href="global.php">
                  <i class="bi bi-clock-history fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Recents</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center active" href="news.php">
                  <i class="bi bi-newspaper fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Update & News</span>
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
    <h5 class="text-secondary mt-2 fw-bold ms-2"><i class="bi bi-newspaper"></i> Update & News</h5>
    <div class="container">
      <div class="row">
        <div class="container">
          <?php foreach ($data as $row): ?>  
            <div class="card mb-3">
              <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-newspaper"></i>
                News <p class="btn-sm text-white float-end"></p>
              </div>
              <div class="card-body text-secondary fw-bold">
                <p class="text-start ms-3">Title: <?= $row['title'] ?></p>
                <p class="text-start ms-3">Description: <?= $row['description'] ?></p>
                <a class="text-start text-primary text-decoration-none ms-3" href="<?= $row['verlink'] ?>" target="_blank"><?= $row['ver'] ?></a>
                <p class="text-start ms-3">Created At: <?= $row['created_at'] ?></p>
              </div>
            </div>
          <?php endforeach; ?> 
        </div>
      </div>
    </div>
    <div class="mt-5"></div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>
