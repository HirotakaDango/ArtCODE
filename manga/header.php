    <nav class="navbar navbar-expand-lg bg-body-tertiary">
      <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="index.php">ArtCODE - Manga</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0 fw-medium">
            <li class="nav-item">
              <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'forum/') === false) echo 'active' ?>" href="/manga/index.php"><i class="bi bi-house-fill"></i> Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'tags.php') echo 'active' ?>" href="/manga/tags.php"><i class="bi bi-tags-fill"></i> Tags</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'artists.php') echo 'active' ?>" href="/manga/artists.php"><i class="bi bi-people-fill"></i> Artists</a>
            </li>
            <?php
              // Check if the user is logged in
              $loggedInUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
                
              if (isset($loggedInUserId)) {
                // If the user is logged in, show the profile button
                $isProfileActive = (basename($_SERVER['PHP_SELF']) == 'favorites.php' && isset($_GET['uid']) && $_GET['uid'] == $loggedInUserId);
                echo '<li class="nav-item"><a class="nav-link' . ($isProfileActive ? ' active' : '') . '" href="/manga/favorites.php?uid=' . $loggedInUserId . '"><i class="bi bi-heart-fill"></i> Favorites</a></li>';
              } else {
                // If the user is not logged in, show a login button
                echo '<li class="nav-item"><a class="nav-link" href="/manga/session.php"><i class="bi bi-box-arrow-in-right"></i> Login/Register</a></li>';
              }
            ?>
            <li class="nav-item">
              <div class="dropdown">
                <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'forum/') !== false) echo 'active'; ?> dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-chat-left-text"></i> Forum</a>
                <ul class="dropdown-menu rounded-4 shadow border-0">
                  <li><a class="dropdown-item fw-medium <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'forum/') !== false) echo 'active'; ?>" href="/manga/forum/">Boards</a></li>
                  <li><a class="dropdown-item fw-medium <?php if (basename($_SERVER['PHP_SELF']) == 'upload.php' && strpos($_SERVER['PHP_SELF'], 'forum/') !== false) echo 'active'; ?>" href="/manga/forum/upload.php">Upload</a></li>
                  <?php
                    // Check if the user is logged in
                    $loggedInUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
                
                    if (isset($loggedInUserId)) {
                      // If the user is logged in, show the profile button
                      $isProfileActive = (basename($_SERVER['PHP_SELF']) == 'user.php' && isset($_GET['id']) && $_GET['id'] == $loggedInUserId);
                      echo '<li><a class="dropdown-item fw-medium' . ($isProfileActive ? ' active' : '') . '" href="/manga/forum/user.php?id=' . $loggedInUserId . '">Profile</a></li>';
                    } else {
                      // If the user is not logged in, show a login button
                      echo '<li><a class="dropdown-item fw-medium" href="/manga/forum/session.php">Login</a></li>';
                    }
                  ?>
                  <li><a class="dropdown-item fw-medium <?php if (basename($_SERVER['PHP_SELF']) == 'settings.php' && strpos($_SERVER['PHP_SELF'], 'forum/settings.php') !== false) echo 'active'; ?>" href="/manga/forum/settings.php">Settings</a></li>
                  <?php if (strpos($_SERVER['PHP_SELF'], '/forum/') !== false) echo '<li><a class="dropdown-item fw-medium ' . (basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : '') . '" href="#" data-bs-toggle="modal" data-bs-target="#searchModal">Search</a></li>'; ?>
                </ul>
              </div>
            </li>
          </ul>
          <form class="d-flex" role="search" action="/manga/index.php">
            <input class="form-control me-2 fw-medium" type="search" placeholder="Search" aria-label="Search" name="search">
            <button class="btn btn-outline-light fw-medium" type="submit"><i class="bi bi-search"></i></button>
          </form>
        </div>
      </div>
    </nav>
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content bg-transparent border-0">
          <div class="modal-body">
            <form class="input-group" role="search" action="search.php">
              <input class="form-control rounded-start-4 border-0 bg-body-tertiary focus-ring focus-ring-dark" name="q" type="search" placeholder="Search" aria-label="Search">
              <button class="btn rounded-end-4 border-0 bg-body-tertiary" type="submit"><i class="bi bi-search"></i></button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'forum/') !== false): ?><button type="button" class="z-3 btn bg-dark-subtle rounded-pill border-0 btn-sm position-fixed end-0 bottom-0 m-2 fw-medium" data-bs-toggle="modal" data-bs-target="#infoModal"><i class="bi bi-question-circle"></i> info</button><?php endif; ?>
    <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Help</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>If you want to upload/display image and youtube, use image link address, only support jpg, jpeg, png, and gif. Make sure to use "http/https"</p>
            <p>image link example: https://i.imgur.com/8e3UNUk.png</p>
            <p>youtube link example: https://www.youtube.com/watch?v=</p>
          </div>
        </div>
      </div>
    </div>