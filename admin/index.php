<?php
require_once('prompt.php'); 

if(isset($_GET['page'])){
  $page = $_GET['page'];
} else {
  header('Location: ../admin/index.php?page=dashboard');
  exit;
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Admin Section</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
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
                <a class="nav-link text-nav fw-bold <?php if(isset($_GET['page']) && $_GET['page'] == 'dashboard') echo 'fs-4'; ?>" aria-current="page" href="?page=dashboard">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-diagram-3-fill"></i> Dashboard
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold <?php if(isset($_GET['page']) && $_GET['page'] == 'users') echo 'fs-4'; ?>" href="?page=users">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-people-fill"></i> All Users
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold <?php if(isset($_GET['page']) && $_GET['page'] == 'images') echo 'fs-4'; ?>" href="?page=images">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-images"></i> All Images
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold <?php if(isset($_GET['page']) && $_GET['page'] == 'danger_zone') echo 'fs-4'; ?>" href="?page=danger_zone">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-person-fill-exclamation"></i> Danger Zone
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-nav fw-bold <?php if(isset($_GET['page']) && $_GET['page'] == 'news') echo 'fs-4'; ?>" href="?page=news">
                  <span class="align-text-bottom"></span>
                  <i class="bi bi-newspaper"></i> Update News
                </a>
              </li>
            </ul>
          </div>
        </nav>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
          <?php 
            switch ($page) {
              case 'dashboard':
                include "dashboard.php";
                break;
              case 'users':
                include "edit_users.php";
                break;
              case 'images':
                include "remove_images.php";
                break;
              case 'danger_zone':
                include "remove_all.php";
                break;
              case 'news':
                include "update_news.php";
                break;
            }
          ?>
        </main> 
      </div>
    </div>
    <style>
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
        
        .text-nav {
          text-align: center;
        }
      }

      .navbar-brand {
        font-size: 18px;
      }
      
      @media (min-width: 992px) {
        .navbar-toggler1 {
          display: none;
        }
        
        .text-nav {
            text-align: left;
        }
      }
    
      .navbar-toggler1 {
        background-color: #ededed;
        border: none;
        font-size: 8px;
        margin-left: 8px;
        border-radius: 5px;
        padding: 6px;
        transition: background-color 0.3s ease; 
      }

      .navbar-toggler1:hover {
        background-color: rgba(0,0,0,0.2);
      }  
    </style>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>