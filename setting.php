<?php
  session_start();
  if (!isset($_SESSION['username'])) {
    header("Location: session.php");
    exit;
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Settings</title>

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">

  <!-- Bootstrap Icons CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
</head>
<body>

  <!-- Page Content -->
  <h4 class="text-secondary fw-bold text-center mt-4 mb-4"><i class="bi bi-gear-fill"></i> Settings</h1>
  <div class="list-group w-auto ms-2 me-2">
    <a href="yourname.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
      <i class="bi bi-person-circle" style="font-size: 35px; color: gray;"></i>
      <div class="d-flex gap-2 w-100 justify-content-between">
        <div>
          <h6 class="mb-0">Change Your Name</h6>
          <p class="mb-0 opacity-75">Change how people see your name.</p>
        </div>
      </div>
    </a>
    <a href="propic.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
      <i class="bi bi-person-square" style="font-size: 35px; color: gray;"></i>
      <div class="d-flex gap-2 w-100 justify-content-between">
        <div>
          <h6 class="mb-0">Change Your Profile Picture</h6>
          <p class="mb-0 opacity-75">Change how people see your profile picture.</p>
        </div>
      </div>
    </a>
    <a href="bg.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
      <i class="bi bi-images" style="font-size: 35px; color: gray;"></i>
      <div class="d-flex gap-2 w-100 justify-content-between">
        <div>
          <h6 class="mb-0">Change Your Background Picture</h6>
          <p class="mb-0 opacity-75">Change how people see your background picture.</p>
        </div>
      </div>
    </a>
    <a href="desc.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
      <i class="bi bi-person-vcard" style="font-size: 35px; color: gray;"></i>
      <div class="d-flex gap-2 w-100 justify-content-between">
        <div>
          <h6 class="mb-0">Change Your Description</h6>
          <p class="mb-0 opacity-75">Change how people see description about you.</p>
        </div>
      </div>
    </a>
    <a href="setpass.php" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
      <i class="bi bi-key-fill" style="font-size: 35px; color: gray;"></i>
      <div class="d-flex gap-2 w-100 justify-content-between">
        <div>
          <h6 class="mb-0">Change Your Password</h6>
          <p class="mb-0 opacity-75">Change your password for security.</p>
        </div>
      </div>
    </a>
  </div> 
  
  <a href="profile.php" class="btn btn-danger ms-2 mt-2 fw-bold" role="button">back to profile</a>

  <!-- Bootstrap JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-3gmFj3l7bX9yQNQqIzH0WT65R+V7jsKwvCn3f3xSiHcFKV7JXkFQ2V7zFdIbRy4g" crossorigin="anonymous"></script>
</body>
</html>
