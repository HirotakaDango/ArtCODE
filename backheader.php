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
                <a class="dropdown-item hover-effect fw-bold text-s" href="logout.php">
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
    <style>
      .hover-effect:hover {
        color: white;
        background-color: #0d6efd;
      }
      
      .text-s {
        color: #6c757d;
      } 
    </style>