<?php
require_once('../auth.php');
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <title>All Settings</title>
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
    <div class="container">
      <?php include('../header.php'); ?>
        <ul class="nav flex-column gap-2 my-3">
          <?php
          $pages = [
            'yourname.php' => [
              'Username',
              'Edit your username that appears on your profile and interactions with others.',
              'bi-person-circle'
            ],
            'profile_picture.php' => [
              'Picture',
              'Update your profile picture to personalize your profile and make it recognizable.',
              'bi-person-square'
            ],
            'background.php' => [
              'Background',
              'Change your profile background to customize the visual style of your profile page.',
              'bi-image'
            ],
            'appearance.php' => [
              'Appearance',
              'Change the theme appearance of your profile page to suit your visual preferences.',
              'bi-palette-fill'
            ],
            'bio.php' => [
              'Bio',
              'Edit your personal bio to share information about yourself with others.',
              'bi-person-vcard'
            ],
            'page.php' => [
              'Page',
              'Adjust how many images to display on each page.',
              'bi-file-earmark-fill'
            ],
            'display.php' => [
              'Display',
              'Adjust display settings to optimize how your content is presented on the platform.',
              'bi-display-fill'
            ],
            'date.php' => [
              'Date',
              'Set important dates or update your birthdate for personalized milestones and notifications.',
              'bi-calendar-fill'
            ],
            'region.php' => [
              'Region',
              'Edit your region settings to specify where you are located or where you want your content to be visible.',
              'bi-globe'
            ],
            'sns.php' => [
              'SNS',
              'Link your social network accounts to your profile to connect with others across platforms.',
              'bi-share'
            ],
            'contact.php' => [
              'Contact',
              'Manage your contact information, making it easier for others to reach out to you.',
              'bi-envelope-fill'
            ],
            'password.php' => [
              'Password',
              'Change your password to ensure the security and protection of your account.',
              'bi-key'
            ],
            'analytic.php' => [
              'Analytic',
              'View analytics data to gain insights into your data.',
              'bi-bar-chart-fill'
            ],
            'setsupport.php' => [
              'Support',
              'Contact support for assistance with any issues or questions you have regarding the platform.',
              'bi-headset'
            ]
          ];
          
          foreach ($pages as $file => $info) {
            $name = $info[0];
            $description = $info[1];
            $icon = $info[2];
          ?>
            <li class="nav-item mb-3">
              <a class="d-flex align-items-center bg-body-tertiary rounded-4 text-decoration-none shadow" href="<?php echo $file; ?>">
                <div class="me-auto">
                  <div class="card p-4 border-0 bg-body-tertiary rounded-4 d-flex justify-content-between">
                    <div class="d-flex align-items-center">
                      <div class="feature-icon-small d-inline-flex align-items-center justify-content-center me-3 p-2">
                        <i class="bi <?php echo $icon; ?> fs-4"></i>
                      </div>
                      <div>
                        <div class="fw-bold mb-1 link-body-emphasis"><?php echo $name; ?></div>
                        <small class="text-muted link-body-emphasis"><?php echo $description; ?></small>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="ms-auto me-3">
                  <i class="bi bi-chevron-right text-muted link-body-emphasis" style="-webkit-text-stroke: 2px;"></i>
                </div>
              </a>
            </li>
          <?php
          }
          ?>
          <li class="nav-item mb-3">
            <a class="d-flex align-items-center bg-body-tertiary rounded-4 text-decoration-none shadow" href="profile.php">
              <div class="me-auto">
                <div class="card p-4 border-0 bg-body-tertiary rounded-4 d-flex justify-content-between">
                  <div class="d-flex align-items-center">
                    <div class="feature-icon-small d-inline-flex align-items-center justify-content-center me-3 p-2">
                      <i class="bi bi-arrow-left-circle-fill fs-4"></i>
                    </div>
                    <div>
                      <div class="fw-bold mb-1 link-body-emphasis">Profile</div>
                      <small class="text-muted fw-medium link-body-emphasis">Back to your main profile</small>
                    </div>
                  </div>
                </div>
              </div>
            </a>
          </li>
        </ul>
        <div class="mt-5"></div>
      <?php include('end.php'); ?>
    </div>
  </body>
</html>