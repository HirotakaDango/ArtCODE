<!DOCTYPE html>
<html>
  <head>
    <title>Settings</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../swup/transitions.css" />
    <script type="module" src="../swup/swup.js"></script>
    <style>
      .hide-scrollbar::-webkit-scrollbar {
        display: none;
      }

      .hide-scrollbar {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
      }
    </style>
  </head>
  <body>
    <nav class="navbar sticky-top navbar-light bg-white flex-md-nowrap p-0 shadow-sm" style="height: 45px;">
      <div class="container-fluid">
        <a class="navbar-brand text-secondary fw-bold" href="../index.php">
          ArtCODE
        </a>
        <button class="navbar-toggler1 d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
          <img src="../icon/toggle1.svg" width="22" height="22">
        </button>
        <div class="offcanvas offcanvas-start d-md-none d-lg-none" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
          <div class="offcanvas-header">
            <h3 class="text-secondary text-center fw-bold">Settings</h3>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body">
            <ul class="nav flex-column">
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
                  <i class="bi bi-image"></i> Background
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'bio.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="bio.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-person-vcard"></i> Bio
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'page.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="page.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-images"></i> Page
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'display.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="display.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-display"></i> Display
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
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'contact.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="contact.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-chat-fill"></i> Contact
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
                  <i class="bi bi-pie-chart-fill"></i> Analytic
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
        </div>
      </div>
    </nav>
    <div class="container-fluid">
      <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse d-none d-md-block d-lg-block">
          <div class="position-sticky pt-3 sidebar-sticky vh-100 overflow-auto hide-scrollbar">
            <ul class="nav flex-column">
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
                  <i class="bi bi-image"></i> Background
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'bio.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="bio.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-person-vcard"></i> Bio
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'page.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="page.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-images"></i> Page
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'display.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="display.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-display"></i> Display
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
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'contact.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="contact.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-chat-fill"></i> Contact
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
                  <i class="bi bi-pie-chart-fill"></i> Analytic
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
              <br><br><br><br><br><br>
            </ul>
          </div>
        </nav>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        