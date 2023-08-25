<!DOCTYPE html>
<html>
  <head>
    <title>Settings</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>
  <body>
    <nav class="navbar sticky-top navbar-light bg-white flex-md-nowrap p-0 shadow-sm" style="height: 45px;">
      <div class="container-fluid">
        <a class="navbar-brand text-secondary fw-bold" href="../index.php">
          ArtCODE
        </a>
        <button class="navbar-toggler1 d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
          <img src="../icon/toggle1.svg" width="22" height="22">
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
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'setting.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="setting.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-gear-fill"></i> Settings
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'yourname.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="yourname.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-person-circle"></i> Username
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'profile_picture.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="profile_picture.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-person-square"></i> Picture
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'background.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="background.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-images"></i> Background
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'bio.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="bio.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-person-vcard"></i> Bio
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'date.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="date.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-calendar-fill"></i> Date
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'region.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="region.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-globe-asia-australia"></i> Region
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'sns.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="sns.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-phone-fill"></i> SNS
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'password.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="password.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-key-fill"></i> Password
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'analytic.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="analytic.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-pie-chart-fill"></i> User's Analytic
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'setsupport.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="setsupport.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-headset"></i> Support
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav text-danger fw-bold fs-5" href="profile.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-arrow-left-circle-fill"></i> Back to Profile
                </a>
              </li>
            </ul>
          </div>
        </nav>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        