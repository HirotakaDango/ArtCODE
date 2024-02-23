    <nav class="navbar fixed-top navbar-expand-md navbar-expand-lg bg-body-tertiary">
      <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">ArtCODE (Music)</a>
        <button class="navbar-toggler border-0 focus-ring focus-ring-dark" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
          <i class="bi bi-list fs-2" style="-webkit-text-stroke: 2px;"></i>
        </button>
        <div class="collapse navbar-collapse justify-content-center" id="navbarNavDropdown">
          <div class="position-absolute start-50 translate-middle-x d-none d-md-block">
            <ul class="navbar-nav">
              <li class="nav-item">
                <a class="nav-link fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/music/?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=newest<?php echo isset($_GET['mode']) ? ($_GET['mode'] === 'lists' ? '_lists' : ($_GET['mode'] === 'grid' ? '' : $_GET['mode'])) : 'grid'; ?>">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'upload.php') echo 'active' ?>" href="upload.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>">Upload</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'favorite.php') echo 'active' ?>" href="favorite.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>">Favorites</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'all_artist.php') echo 'active' ?>" href="all_artist.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'asc' || $_GET['by'] === 'desc') ? $_GET['by'] : 'desc') : (isset($_GET['by']) && ($_GET['by'] === 'asc_lists' || $_GET['by'] === 'desc_lists') ? $_GET['by'] : 'desc_lists'); ?>">Artists</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'all_album.php') echo 'active' ?>" href="all_album.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'asc' || $_GET['by'] === 'desc') ? $_GET['by'] : 'desc') : (isset($_GET['by']) && ($_GET['by'] === 'asc_lists' || $_GET['by'] === 'desc_lists') ? $_GET['by'] : 'desc_lists'); ?>">Albums</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'profile.php') echo 'active' ?>" href="profile.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>">My Songs</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-bold" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/profile/?by=newest">Profile</a>
              </li>
            </ul>
          </div>
          <ul class="navbar-nav d-md-none d-lg-none">
            <div class="text-center">
              <li class="nav-item">
                <a class="nav-link fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/music/?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=newest<?php echo isset($_GET['mode']) ? ($_GET['mode'] === 'lists' ? '_lists' : ($_GET['mode'] === 'grid' ? '' : $_GET['mode'])) : 'grid'; ?>">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'upload.php') echo 'active' ?>" href="upload.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>">Upload</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'favorite.php') echo 'active' ?>" href="favorite.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>">Favorites</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'all_artist.php') echo 'active' ?>" href="all_artist.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'asc' || $_GET['by'] === 'desc') ? $_GET['by'] : 'desc') : (isset($_GET['by']) && ($_GET['by'] === 'asc_lists' || $_GET['by'] === 'desc_lists') ? $_GET['by'] : 'desc_lists'); ?>">Artists</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'all_album.php') echo 'active' ?>" href="all_album.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'asc' || $_GET['by'] === 'desc') ? $_GET['by'] : 'desc') : (isset($_GET['by']) && ($_GET['by'] === 'asc_lists' || $_GET['by'] === 'desc_lists') ? $_GET['by'] : 'desc_lists'); ?>">Albums</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'profile.php') echo 'active' ?>" href="profile.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>">My Songs</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-bold" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/profile/?by=newest">Profile</a>
              </li>
            </div>
          </ul>
          <form class="d-flex ms-auto mt-2 mt-md-0 md-lg-0" action="search.php" role="search">
            <input type="hidden" name="mode" value="<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>">
            <input type="hidden" name="by" value="<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>">
            <input class="form-control rounded-end-0 rounded-pill fw-medium focus-ring focus-ring-dark border-end-0" name="q" type="search" placeholder="Search" aria-label="Search">
            <button class="btn btn-outline-success rounded-start-0 rounded-pill fw-medium" type="submit">Search</button>
          </form>
        </div>
      </div>
    </nav>
    <br><br>