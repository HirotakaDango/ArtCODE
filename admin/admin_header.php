<!DOCTYPE html>
<html>
  <head>
    <title>Admin Section</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
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
                <h3 class="text-secondary text-nav fw-bold">ADMIN SECTION</h3>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'index.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="../admin/index.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-diagram-3-fill"></i> Dashboard
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'edit_users.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="../admin/edit_users.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-people-fill"></i> All Users
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'remove_images.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="../admin/remove_images.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-images"></i> All Images
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'remove_all.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="../admin/remove_all.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-person-fill-exclamation"></i> Danger Zone
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold fs-5 <?php if(basename($_SERVER['PHP_SELF']) == 'update_news.php') echo 'rounded-4 border border-4 bg-primary text-white' ?>" href="../admin/update_news.php">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-newspaper"></i> Update News
                </a>
              </li>
            </ul>
          </div>
        </nav>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        