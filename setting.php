<?php
  session_start();
  if (!isset($_SESSION['username'])) {
    header("Location: session.php");
    exit;
  }
?>

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
      <a href="propic.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
        <i class="bi bi-person-square" style="font-size: 35px; color: gray;"></i>
        <div class="d-flex gap-2 w-100 justify-content-between">
          <div>
            <h6 class="mb-0 fw-bold">Change Your Profile Picture</h6>
            <p class="mb-0 opacity-75">Change how people see your profile picture.</p>
          </div>
        </div>
      </a>
      <a href="bg.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
        <i class="bi bi-images" style="font-size: 35px; color: gray;"></i>
        <div class="d-flex gap-2 w-100 justify-content-between">
          <div>
            <h6 class="mb-0 fw-bold">Change Your Background Picture</h6>
            <p class="mb-0 opacity-75">Change how people see your background picture.</p>
          </div>
        </div>
      </a>
      <a href="desc.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
        <i class="bi bi-person-vcard" style="font-size: 35px; color: gray;"></i>
        <div class="d-flex gap-2 w-100 justify-content-between">
          <div>
            <h6 class="mb-0 fw-bold">Change Your Bio</h6>
            <p class="mb-0 opacity-75">Change how people see information about you.</p>
          </div>
        </div>
      </a>
      <a href="setpass.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
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
    <div class="mt-4"></div>
    <?php include('end.php'); ?>