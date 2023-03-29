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
            <li><a class="dropdown-item text-secondary fw-bold" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a></li>
            <li><a class="dropdown-item text-secondary fw-bold" href="setting.php"><i class="bi bi-gear-fill"></i> Settings</a></li>
            <li><a class="dropdown-item text-secondary fw-bold" href="favorite.php"><i class="bi bi-heart-fill"></i> Favorites</a></li>
            <li><a class="dropdown-item text-secondary fw-bold" href="logout.php"><i class="bi bi-door-open-fill"></i> Logout</a></li>
          </ul>
        </div> 
      </div>
    </nav>