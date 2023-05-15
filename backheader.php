    <nav class="navbar fixed-top navbar-expand-md navbar-light bg-white shadow-sm shadow-md shadow-lg">
      <div class="container-fluid">
        <button class="navbar-toggler1 d-md-none" type="button" onclick="goBack()">
          <img src="icon/back.svg" width="22" height="22">
        </button> 
        <a class="navbar-brand text-secondary fw-bold" href="index.php">
          ArtCODE
        </a>
        <div class="dropdown nav-right">
          <a class="nav-link px-2 text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle fs-5"></i>
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
                <a class="dropdown-item hover-effect fw-bold text-s" data-bs-toggle="modal" data-bs-target="#logOut">
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
      </div>
    </nav>
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

        .width-vw {
          width: 89vw;
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
        
        .d-none-sm {
          display: none;
        }
        
        .width-vw {
          width: 75vw;
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