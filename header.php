<?php
// Connect to the SQLite database
$db1 = new SQLite3('database.sqlite');

// Get the artist name from the database
$email1 = $_SESSION['email'];
$stmt1 = $db1->prepare("SELECT pic FROM users WHERE email = :email");
$stmt1->bindValue(':email', $email1);
$result1 = $stmt1->execute();
$row1 = $result1->fetchArray();
$pic1 = $row1['pic'];
?>

    <!-- Navbar -->
    <nav class="navbar fixed-top navbar-expand-md navbar-expand-lg navbar-light bg-white shadow-lg shadow-sm shadow-md">
      <div class="container-fluid">
        <button class="navbar-toggler1 d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
          <img src="icon/toggle1.svg" width="22" height="22">
        </button> 
        <a class="navbar-brand text-secondary fw-bold" href="index.php">
          ArtCODE
        </a>
        <div class="dropdown nav-right">
          <a class="nav-link px-2 text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <img class="rounded-circle object-fit-cover shadow border border-1" width="32" height="32" src="<?php echo !empty($pic1) ? $pic1 : "icon/profile.svg"; ?>" alt="Profile Picture" style="margin-top: -2px;">
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item hover-effect fw-bold <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'text-white active' : 'text-s'; ?>" href="profile.php">
                <i class="bi bi-person-circle"></i> Profile
              </a>
            </li>
            <li>
              <a class="dropdown-item hover-effect fw-bold <?php echo (basename($_SERVER['PHP_SELF']) == 'setting.php') ? 'text-white active' : 'text-s'; ?>" href="setting.php">
                <i class="bi bi-gear-fill"></i> Settings
              </a>
            </li>
            <li>
              <a class="dropdown-item hover-effect fw-bold <?php echo (basename($_SERVER['PHP_SELF']) == 'favorite.php') ? 'text-white active' : 'text-s'; ?>" href="favorite.php">
                <i class="bi bi-heart-fill"></i> Favorites
              </a>
            </li>
            <?php if(isset($_SESSION['email']) && isset($_COOKIE['token'])): ?>
              <li>
                <a class="dropdown-item hover-effect fw-bold text-s" href="#" data-bs-toggle="modal" data-bs-target="#logOut">
                  <i class="bi bi-door-open-fill"></i> Logout
                </a>
              </li>
            <?php else: ?>
              <li>
                <a class="dropdown-item hover-effect fw-bold <?php echo (basename($_SERVER['PHP_SELF']) == 'session.php') ? 'text-white active' : 'text-s'; ?>" href="session.php">
                  <i class="bi bi-door-closed-fill"></i> Signin
                </a>
              </li>
            <?php endif; ?> 
          </ul>
        </div> 
        <div class="offcanvas offcanvas-start" tabindex="-1" id="navbar" aria-labelledby="navbarLabel">
          <div class="offcanvas-header">
            <h5 class="offcanvas-title text-secondary" id="navbarLabel">Menu</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body">
            <!-- Mobile -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 fw-bold d-none-sm">
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active' ?>" href="index.php">
                  <i class="bi bi-house-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Home</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'forum.php') echo 'active' ?>" href="forum.php">
                  <i class="bi bi-chat-left-dots-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Forum</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'imgupload.php') echo 'active' ?>" href="imgupload.php">
                  <i class="bi bi-cloud-arrow-up-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Uploads</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'popular.php') echo 'active' ?>" href="popular.php">
                  <i class="bi bi-star-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Popular</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'notification.php') echo 'active' ?>" href="notification.php">
                  <i class="bi bi-bell-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Notification</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'status.php') echo 'active' ?>" href="status.php">
                  <i class="bi bi-card-text fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Status</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'tags.php') echo 'active' ?>" href="tags.php">
                  <i class="bi bi-tags-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Tags</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'users.php') echo 'active' ?>" href="users.php">
                  <i class="bi bi-people-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Users</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'global.php') echo 'active' ?>" href="global.php">
                  <i class="bi bi-compass-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Explore</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'news.php') echo 'active' ?>" href="news.php">
                  <i class="bi bi-newspaper fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Update & News</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'support.php') echo 'active' ?>" href="support.php">
                  <i class="bi bi-headset fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Support</span>
                </a>
              </li>
            </ul>
            <!-- end -->
            
            <!-- Desktop -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 fw-bold d-none-md-lg">
              <li class="nav-item">
                <a class="nav-link nav-center btn btn-smaller btn-outline-secondary rounded-pill text-nowrap <?php if(basename($_SERVER['PHP_SELF']) == 'imgupload.php') echo 'active text-white' ?>" href="imgupload.php">
                  <i class="bi bi-cloud-arrow-up-fill fs-5"></i> uploads
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active' ?>" href="index.php">
                  Home
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'forum.php') echo 'active' ?>" href="forum.php">
                  Forum
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'popular.php') echo 'active' ?>" href="popular.php">
                  Popular
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'notification.php') echo 'active' ?>" href="notification.php">
                  Notification
                </a>
              </li>
              <li class="nav-item">
                <div class="dropdown-center">
                  <a class="btn btn-sm" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-grid-3x3-gap-fill fs-5 text-secondary"></i>
                  </a>

                  <ul class="dropdown-menu" style="width: 200px;">
                    <li class="nav-item">
                      <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'status.php') echo 'active' ?>" href="status.php">
                        <i class="bi bi-card-text fs-5"></i>
                        <span class="d-lg-inline ms-2">Status</span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'tags.php') echo 'active' ?>" href="tags.php">
                        <i class="bi bi-tags-fill fs-5"></i>
                        <span class="d-lg-inline ms-2">Tags</span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'users.php') echo 'active' ?>" href="users.php">
                        <i class="bi bi-people-fill fs-5"></i>
                        <span class="d-lg-inline ms-2">Users</span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'global.php') echo 'active' ?>" href="global.php">
                        <i class="bi bi-compass-fill fs-5"></i>
                        <span class="d-lg-inline ms-2">Explore</span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'news.php') echo 'active' ?>" href="news.php">
                        <i class="bi bi-newspaper fs-5"></i>
                        <span class="d-lg-inline ms-2">Update & News</span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'support.php') echo 'active' ?>" href="support.php">
                        <i class="bi bi-headset fs-5"></i>
                        <span class="d-lg-inline ms-2">Support</span>
                      </a>
                    </li> 
                  </ul>
                </div>
              </li>
            </ul>
            <!-- end -->
          </div>
        </div>
      </div>
    </nav>
    <br><br>
    <!-- Modal -->
    <div class="modal fade" id="logOut" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content rounded-3 shadow">
          <div class="modal-body p-4 text-center">
            <h5 class="mb-0">Do you want to end the session?</h5>
            <p class="mb-0 mt-2">You can always comeback whenever you want later.</p>
          </div>
          <div class="modal-footer flex-nowrap p-0">
            <a class="btn btn-lg btn-link text-danger fs-6 text-decoration-none col-6 py-3 m-0 rounded-0 border-end" href="logout.php"><strong>Yes, end the session!</strong></a>
            <button type="button" class="btn btn-lg btn-link fs-6 text-decoration-none col-6 py-3 m-0 rounded-0" data-bs-dismiss="modal">Cancel, keep it!</button>
          </div>
        </div>
      </div>
    </div> 
    <style>
      .hover-effect:hover {
        color: white;
        background-color: #0d6efd;
      }
      
      .text-s {
        color: #6c757d;
      }
      
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
        
        .d-none-sm {
          display: none;
        }
      }
      
      @media (max-width: 767px) {
        .d-none-md-lg {
          display: none;
        }
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
      
      .btn-smaller {
        padding: 2px 4px;
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