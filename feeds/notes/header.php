    <nav class="navbar fixed-top navbar-expand bg-body-tertiary">
      <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">ArtCODE (Notes)</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-center" id="navbarNavDropdown">
          <div class="position-absolute start-50 translate-middle-x">
            <ul class="navbar-nav">
              <li class="nav-item">
                <a class="nav-link fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active' ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/notes/">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'upload.php') echo 'active' ?>" href="upload.php">Upload</a>
              </li>
            </ul>
          </div>
          <form class="d-flex ms-auto" action="genre.php" role="search">
            <input class="form-control me-2" name="tag" type="search" placeholder="Search" aria-label="Search">
            <button class="btn btn-outline-success" type="submit">Search</button>
          </form>
        </div>
      </div>
    </nav>
    <br><br>