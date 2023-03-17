    <nav class="navbar fixed-top navbar-expand-md navbar-light bg-white shadow-sm">
      <div class="container-fluid">
        <button class="navbar-toggler1 d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
          <img src="icon/toggle1.svg" width="22" height="22">
        </button> 
        <a class="navbar-brand text-secondary fw-bold" href="index.php">
          ArtCODE
        </a>
          <div class="dropdown nav-right">
            <a class="nav-link px-2 text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle fs-5"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-start">
              <li><a class="dropdown-item text-secondary fw-bold" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a></li>
              <li><a class="dropdown-item text-secondary fw-bold" href="setting.php"><i class="bi bi-gear-fill"></i> Settings</a></li>
              <li><a class="dropdown-item text-secondary fw-bold" href="favorite.php"><i class="bi bi-heart-fill"></i> Favorites</a></li>
              <li><a class="dropdown-item text-secondary fw-bold" href="logout.php"><i class="bi bi-door-open-fill"></i> Logout</a></li>
            </ul>
          </div> 
        <div class="offcanvas offcanvas-start w-50" tabindex="-1" id="navbar" aria-labelledby="navbarLabel">
          <div class="offcanvas-header">
            <h5 class="offcanvas-title text-secondary" id="navbarLabel">Menu</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 fw-bold">
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'setting.php') echo 'active' ?>" href="setting.php">
                  <i class="bi bi-gear-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">All Settings</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'yourname.php') echo 'active' ?>" href="yourname.php">
                  <i class="bi bi-person-circle fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Profile's Name</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'propic.php') echo 'active' ?>" href="propic.php">
                  <i class="bi bi-person-square fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Profile's Photo</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'bg.php') echo 'active' ?>" href="bg.php">
                  <i class="bi bi-images fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Background</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'desc.php') echo 'active' ?>" href="desc.php">
                  <i class="bi bi-person-vcard fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Description</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'setpass.php') echo 'active' ?>" href="setpass.php">
                  <i class="bi bi-key-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Password</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'analytic.php') echo 'active' ?>" href="analytic.php">
                  <i class="bi bi-pie-chart-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Analytics</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'setsupport.php') echo 'active' ?>" href="setsupport.php">
                  <i class="bi bi-headset fs-5"></i>
                  <span class="d-md-none d-lg-inline ms-2">Support</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>
    <br><br>
    <style>
      @media (min-width: 768px) {
        .navbar-nav {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
        }
      
        .nav-center {
          margin-left: 15px;
          margin-right: 15px;
        }
      
        .nav-right {
          position: absolute;
          right: 10px;
          top: 10;
          align-items: center;
        }
      }
      
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
      }
    
      .navbar {
        height: 45px;
      }
      
      .navbar-brand {
        font-size: 18px;
      }

      @media (min-width: 992px) {
        .navbar-toggler1 {
          display: none;
        }
      }
    
      .navbar-toggler1 {
        background-color: #ededed;
        border: none;
        font-size: 8px;
        margin-top: -2px;
        margin-left: 8px;
        border-radius: 5px;
        padding: 6px;
        transition: background-color 0.3s ease; 
      }

      .navbar-toggler1:hover {
        background-color: rgba(0,0,0,0.2);
      }

    </style>