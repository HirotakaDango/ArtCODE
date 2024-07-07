<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <title>Settings</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="/icon/favicon.png">
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
    <div class="container-fluid">
      <div class="row">
        <nav id="sidebarMenu" class="col-md-4 col-lg-3 d-md-block sidebar collapse">
          <div class="position-sticky sidebar-sticky vh-100 overflow-auto hide-scrollbar">
            <ul class="nav flex-column shadow gap-2 my-3">
              <?php
              $pages = [
                'all.php' => ['General', 'See all options.', 'bi-gear-fill'],
                'yourname.php' => ['Username', 'Edit your username.', 'bi-person-circle'],
                'profile_picture.php' => ['Picture', 'Update your profile picture.', 'bi-person-square'],
                'background.php' => ['Background', 'Change your profile background.', 'bi-image'],
                'appearance.php' => ['Appearance', 'Change theme appearance.', 'bi-palette-fill'],
                'bio.php' => ['Bio', 'Edit your personal bio.', 'bi-person-vcard'],
                'page.php' => ['Page', 'Manage your pages.', 'bi-file-earmark-fill'],
                'display.php' => ['Display', 'Adjust display settings.', 'bi-display-fill'],
                'date.php' => ['Date', 'Set important dates.', 'bi-calendar-fill'],
                'region.php' => ['Region', 'Edit your region settings.', 'bi-globe'],
                'sns.php' => ['SNS', 'Link your social network accounts.', 'bi-share'],
                'contact.php' => ['Contact', 'Manage your contact information.', 'bi-envelope-fill'],
                'password.php' => ['Password', 'Change your password.', 'bi-key'],
                'analytic.php' => ['Analytic', 'View analytics data.', 'bi-bar-chart-fill'],
                'setsupport.php' => ['Support', 'Contact support.', 'bi-headset']
              ];
            
              foreach ($pages as $file => $info) {
                $name = $info[0];
                $description = $info[1];
                $icon = $info[2];
                $activeClass = (basename($_SERVER['PHP_SELF']) == $file) ? 'opacity-50' : '';
              ?>
                <li class="nav-item">
                  <a class="card p-3 border-0 bg-body-tertiary rounded-4 text-decoration-none fs-5 <?php echo $activeClass; ?>" href="<?php echo $file; ?>">
                    <div class="d-flex align-items-center">
                      <div class="feature-icon-small d-inline-flex align-items-center justify-content-center text-bg-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> bg-gradient fs-4 rounded-3 me-3 p-3">
                        <i class="bi <?php echo $icon; ?>"></i>
                      </div>
                      <div>
                        <div class="fw-bold"><?php echo $name; ?></div>
                        <small class="text-muted fw-medium"><?php echo $description; ?></small>
                      </div>
                    </div>
                  </a>
                </li>
              <?php
              }
              ?>
              <li class="nav-item">
                <a class="card p-3 border-0 bg-body-tertiary rounded-4 text-decoration-none fs-5" href="profile.php">
                  <div class="d-flex align-items-center">
                    <div class="feature-icon-small d-inline-flex align-items-center justify-content-center text-bg-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> bg-gradient fs-4 rounded-3 me-3 p-3">
                      <i class="bi bi-arrow-left-circle-fill"></i>
                    </div>
                    <div>
                      <div class="fw-bold">Profile</div>
                      <small class="text-muted fw-medium">Back to your main profile</small>
                    </div>
                  </div>
                </a>
              </li>
            </ul>
          </div>
        </nav>
        <main class="col-md-8 ms-sm-auto col-lg-9 px-md-4 vh-100 overflow-auto">
        