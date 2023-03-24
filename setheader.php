<!DOCTYPE html>
<html>
  <head>
    <title>Database Counts</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>
  <body>
    <nav class="navbar sticky-top navbar-light bg-white flex-md-nowrap p-0 shadow-sm" style="height: 45px;">
      <div class="container-fluid">
        <a class="navbar-brand text-secondary fw-bold" href="index.php">
          ArtCODE
        </a>
        <button class="navbar-toggler1 d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
          <img src="icon/toggle1.svg" width="22" height="22">
        </button>
      </div>
    </nav>

    <div class="container-fluid">
      <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
          <div class="position-sticky pt-3 sidebar-sticky">
            <ul class="nav flex-column">
              <li class="nav-item">
                <h3 class="text-secondary text-center fw-bold">Settings</h3>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'setting.php') echo 'fs-4' ?>" href="setting.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-gear-fill"></i> Settings
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'yourname.php') echo 'fs-4' ?>" href="yourname.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-person-circle"></i> Username
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'propic.php') echo 'fs-4' ?>" href="propic.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-person-square"></i> Picture
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'bg.php') echo 'fs-4' ?>" href="bg.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-images"></i> Background
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'desc.php') echo 'fs-4' ?>" href="desc.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-person-vcard"></i> Bio
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'setpass.php') echo 'fs-4' ?>" href="setpass.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-key-fill"></i> Password
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'analytic.php') echo 'fs-4' ?>" href="analytic.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-pie-chart-fill"></i> User's Analytic
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'setsupport.php') echo 'fs-4' ?>" href="setsupport.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-headset"></i> Support
                </a>
              </li>
            </ul>
          </div>
        </nav>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        