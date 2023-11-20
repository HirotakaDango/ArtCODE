<?php
require_once('../auth.php');
?>

    <main id="swup" class="transition-main">
    <?php include('setheader.php'); ?>
    <div class="list-group w-auto ms-2 me-2 mt-4 fw-semibold">
      <a href="yourname.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
        <i class="bi bi-person-circle" style="font-size: 35px; color: gray;"></i>
        <div class="d-flex gap-2 w-100 justify-content-between">
          <div>
            <h6 class="mb-0 fw-bold">Change Your Name</h6>
            <p class="mb-0 opacity-75">Change how people see your name.</p>
          </div>
        </div>
      </a>
      <a href="profile_picture.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
        <i class="bi bi-person-square" style="font-size: 35px; color: gray;"></i>
        <div class="d-flex gap-2 w-100 justify-content-between">
          <div>
            <h6 class="mb-0 fw-bold">Change Your Profile Picture</h6>
            <p class="mb-0 opacity-75">Change how people see your profile picture.</p>
          </div>
        </div>
      </a>
      <a href="background.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
        <i class="bi bi-image" style="font-size: 35px; color: gray;"></i>
        <div class="d-flex gap-2 w-100 justify-content-between">
          <div>
            <h6 class="mb-0 fw-bold">Change Your Background Picture</h6>
            <p class="mb-0 opacity-75">Change how people see your background picture.</p>
          </div>
        </div>
      </a>
      <a href="bio.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
        <i class="bi bi-person-vcard" style="font-size: 35px; color: gray;"></i>
        <div class="d-flex gap-2 w-100 justify-content-between">
          <div>
            <h6 class="mb-0 fw-bold">Change Your Bio</h6>
            <p class="mb-0 opacity-75">Change how people see information about you.</p>
          </div>
        </div>
      </a>
      <a href="page.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
        <i class="bi bi-images" style="font-size: 35px; color: gray;"></i>
        <div class="d-flex gap-2 w-100 justify-content-between">
          <div>
            <h6 class="mb-0 fw-bold">Change Number Per Page</h6>
            <p class="mb-0 opacity-75">Change how many images set up for each page.</p>
          </div>
        </div>
      </a>
      <a href="display.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
        <i class="bi bi-display" style="font-size: 35px; color: gray;"></i>
        <div class="d-flex gap-2 w-100 justify-content-between">
          <div>
            <h6 class="mb-0 fw-bold">Change Display Mode</h6>
            <p class="mb-0 opacity-75">Choose which mode to display.</p>
          </div>
        </div>
      </a>
      <a href="date.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
        <i class="bi bi-calendar-fill" style="font-size: 35px; color: gray;"></i>
        <div class="d-flex gap-2 w-100 justify-content-between">
          <div>
            <h6 class="mb-0 fw-bold">Change Your Date</h6>
            <p class="mb-0 opacity-75">Change how people know your age.</p>
          </div>
        </div>
      </a>
      <a href="region.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
        <i class="bi bi-globe-asia-australia" style="font-size: 35px; color: gray;"></i>
        <div class="d-flex gap-2 w-100 justify-content-between">
          <div>
            <h6 class="mb-0 fw-bold">Change Your Region</h6>
            <p class="mb-0 opacity-75">Change how people see where you come from.</p>
          </div>
        </div>
      </a>
      <a href="sns.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
        <i class="bi bi-phone-fill" style="font-size: 35px; color: gray;"></i>
        <div class="d-flex gap-2 w-100 justify-content-between">
          <div>
            <h6 class="mb-0 fw-bold">Change Your Linked SNS</h6>
            <p class="mb-0 opacity-75">Change how people see your another SNS.</p>
          </div>
        </div>
      </a>
      <a href="password.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
        <i class="bi bi-key-fill" style="font-size: 35px; color: gray;"></i>
        <div class="d-flex gap-2 w-100 justify-content-between">
          <div>
            <h6 class="mb-0 fw-bold">Change Your Password</h6>
            <p class="mb-0 opacity-75">Change your password for security.</p>
          </div>
        </div>
      </a>
      <a href="analytic.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
        <i class="bi bi-pie-chart-fill" style="font-size: 35px; color: gray;"></i>
        <div class="d-flex gap-2 w-100 justify-content-between">
          <div>
            <h6 class="mb-0 fw-bold">User's Analytical Data</h6>
            <p class="mb-0 opacity-75">See your data.</p>
          </div>
        </div>
      </a>
      <a href="setsupport.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
        <i class="bi bi-headset" style="font-size: 35px; color: gray;"></i>
        <div class="d-flex gap-2 w-100 justify-content-between">
          <div>
            <h6 class="mb-0 fw-bold">Support</h6>
            <p class="mb-0 opacity-75">If you have problem, contact us.</p>
          </div>
        </div>
      </a>
    </div> 
    <a class="btn me-2 mt-3 mb-5 btn-danger fw-bold d-md-none d-lg-none float-end" href="profile.php"><i class="bi bi-arrow-left-circle-fill"></i> Back to Profile</a> 
    <div class="mt-5"></div>
    <?php include('end.php'); ?>
    </main>