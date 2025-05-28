    <nav class="" aria-label="breadcrumb">
      <div class="d-none d-md-block d-lg-block py-2 my-3 container-fluid">
        <div class="p-3 rounded-4 shadow bg-body-tertiary">
          <div class="btn-group w-100 align-items-center">
            <a class="btn py-2 rounded link-body-emphasis border-0" href="./"><img src="<?php echo $web; ?>/icon/favicon.png" height="38" width="38"></a>
            <a class="btn py-2 rounded link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/manga/') === false) echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./">Home</a>
            <a class="btn py-2 rounded link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'parodies.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./parodies.php">Parodies</a>
            <a class="btn py-2 rounded link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'characters.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./characters.php">Characters</a>
            <a class="btn py-2 rounded link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'tags.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./tags.php">Tags</a>
            <a class="btn py-2 rounded link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'artists.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./artists.php">Artists</a>
            <a class="btn py-2 rounded link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'groups.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./groups.php">Groups</a>
            <div class="btn-group">
              <a class="btn py-2 rounded link-body-emphasis dropdown-toggle <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/forum/') !== false) echo 'fw-bold'; else echo 'fw-medium'; ?>" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Forum</a>
              <ul class="dropdown-menu rounded-4 shadow border-0">
                <li><a class="dropdown-item fw-medium <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'forum/') !== false) echo 'active'; ?>" href="./forum/">Boards</a></li>
                <li><a class="dropdown-item fw-medium <?php if (basename($_SERVER['PHP_SELF']) == 'upload.php' && strpos($_SERVER['PHP_SELF'], 'forum/') !== false) echo 'active'; ?>" href="./forum/upload.php">Upload</a></li>
                <?php
                  $loggedInUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
                  if (isset($loggedInUserId)) {
                    $isProfileActive = (basename($_SERVER['PHP_SELF']) == 'user.php' && isset($_GET['id']) && $_GET['id'] == $loggedInUserId);
                ?>
                    <li><a class="dropdown-item fw-medium<?php echo ($isProfileActive ? ' active' : ''); ?>" href="./forum/user.php?id=<?php echo $loggedInUserId; ?>">Profile</a></li>
                    <li><a class="dropdown-item fw-medium<?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php' ? ' active' : ''); ?>" href="./forum/settings.php">Settings</a></li>
                <?php
                  } else {
                ?>
                    <li><a class="dropdown-item fw-medium" href="./forum/session.php">Login</a></li>
                <?php } ?>
                <?php if (strpos($_SERVER['PHP_SELF'], '/forum/') !== false) echo '<li><a class="dropdown-item fw-medium ' . (basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : '') . '" href="#" data-bs-toggle="modal" data-bs-target="#searchModal">Search</a></li>'; ?>
              </ul>
            </div>
            <?php
              if (isset($loggedInUserId)) {
                $isProfileActive = (basename($_SERVER['PHP_SELF']) == 'favorites.php' && isset($_GET['uid']) && $_GET['uid'] == $loggedInUserId);
                echo '<a class="btn py-2 rounded link-body-emphasis' . ($isProfileActive ? ' fw-bold' : ' fw-medium') . '" href="./favorites.php?uid=' . $loggedInUserId . '">Favorites</a>';
              } else {
                echo '<a class="btn py-2 rounded link-body-emphasis fw-medium" href="./session.php">Login/Register</a>';
              }
            ?>
            <form class="d-flex ms-4" role="search" action="./">
              <input class="form-control fw-medium w-100 rounded rounded-end-0 border-0 bg-dark-subtle" type="search" placeholder="Search" aria-label="Search" name="search" value="<?php echo isset($_GET['search']) && $_GET['search'] !== '' ? htmlspecialchars($_GET['search']) : ''; ?>">
              <button class="btn btn-dark rounded rounded-start-0" type="submit"><i class="bi bi-search"></i></button>
            </form>
          </div>
        </div>
      </div>
      
      <!-- Mobile -->
      <div class="d-md-none d-lg-none bg-body-tertiary p-2">
        <div class="d-flex align-items-center gap-2">
          <a href="./"><img src="<?php echo $web; ?>/icon/favicon.png" height="38" width="38"></a>
          <form class="d-flex w-100" role="search" action="./">
            <input class="form-control fw-medium w-100 rounded-1 rounded-end-0 border-0 bg-dark-subtle" type="search" placeholder="Search" aria-label="Search" name="search" value="<?php echo isset($_GET['search']) && $_GET['search'] !== '' ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button class="btn btn-light rounded-1 rounded-start-0" type="submit"><i class="bi bi-search"></i></button>
          </form>
          <a class="btn btn-light rounded-1 p-0 px-2" data-bs-toggle="collapse" href="#collapseModal" role="button" aria-expanded="false" aria-controls="collapseModal">
            <span style="font-size: 1.5em;">&#9776;</span>
          </a>
        </div>
      </div>
      <div class="collapse bg-secondary bg-opacity-25" id="collapseModal">
        <div class="btn-group-vertical w-100">
          <a class="btn py-2 rounded text-start link-body-emphasis <?php echo (basename($_SERVER['PHP_SELF']) === 'index.php' && (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/' || preg_match('/\/(index\.php)?$/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)))) ? 'fw-bold' : 'fw-medium'; ?>" href="./">Home</a>
          <a class="btn py-2 rounded text-start link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'parodies.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./parodies.php">Parodies</a>
          <a class="btn py-2 rounded text-start link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'characters.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./characters.php">Characters</a>
          <a class="btn py-2 rounded text-start link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'tags.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./tags.php">Tags</a>
          <a class="btn py-2 rounded text-start link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'artists.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./artists.php">Artists</a>
          <a class="btn py-2 rounded text-start link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'groups.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./groups.php">Groups</a>
          <a class="btn py-2 rounded text-start link-body-emphasis fw-medium" data-bs-toggle="collapse" href="#collapseForum" role="button" aria-expanded="false" aria-controls="collapseForum">Forum</a>
          <div class="collapse" id="collapseForum">
            <div class="btn-group-vertical w-100 px-2 mb-2">
              <a class="btn py-2 rounded text-start link-body-emphasis fw-medium <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'forum/') !== false) echo 'fw-bold'; ?>" href="./forum/">Boards</a>
              <a class="btn py-2 rounded text-start link-body-emphasis fw-medium <?php if (basename($_SERVER['PHP_SELF']) == 'upload.php' && strpos($_SERVER['PHP_SELF'], 'forum/') !== false) echo 'fw-bold'; ?>" href="./forum/upload.php">Upload</a>
              <?php
                if (isset($loggedInUserId)) {
                  $isProfileActive = (basename($_SERVER['PHP_SELF']) == 'user.php' && isset($_GET['id']) && $_GET['id'] == $loggedInUserId);
              ?>
                  <a class="btn py-2 rounded text-start link-body-emphasis fw-medium<?php echo ($isProfileActive ? ' fw-bold' : ''); ?>" href="./forum/user.php?id=<?php echo $loggedInUserId; ?>">Profile</a>
                  <a class="btn py-2 rounded text-start link-body-emphasis fw-medium<?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php' ? ' fw-bold' : ''); ?>" href="./forum/settings.php">Settings</a>
              <?php
                } else {
              ?>
                  <a class="btn py-2 rounded text-start link-body-emphasis fw-medium" href="./forum/session.php">Login</a>
              <?php } ?>
              <?php if (strpos($_SERVER['PHP_SELF'], '/forum/') !== false) echo '<a class="btn py-2 rounded text-start link-body-emphasis fw-medium ' . (basename($_SERVER['PHP_SELF']) == 'search.php' ? 'fw-bold' : '') . '" href=\"#\" data-bs-toggle=\"modal\" data-bs-target=\"#searchModal\">Search</a>'; ?>
            </div>
          </div>
          <?php
            if (isset($loggedInUserId)) {
              $isProfileActive = (basename($_SERVER['PHP_SELF']) == 'favorites.php' && isset($_GET['uid']) && $_GET['uid'] == $loggedInUserId);
              echo '<a class="btn py-2 rounded text-start link-body-emphasis' . ($isProfileActive ? ' fw-bold' : ' fw-medium') . '" href="./favorites.php?uid=' . $loggedInUserId . '">Favorites</a>';
            } else {
              echo '<a class="btn py-2 rounded text-start link-body-emphasis fw-medium" href="./session.php">Login/Register</a>';
            }
          ?>
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
