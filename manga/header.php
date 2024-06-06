    <nav class="navbar navbar-expand-lg bg-body-tertiary">
      <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="index.php">ArtCODE - Manga</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0 fw-medium">
            <li class="nav-item">
              <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active' ?>" href="index.php"><i class="bi bi-house-fill"></i> Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'tags.php') echo 'active' ?>" href="tags.php"><i class="bi bi-tags-fill"></i> Tags</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'artists.php') echo 'active' ?>" href="artists.php"><i class="bi bi-people-fill"></i> Artist</a>
            </li>
          </ul>
          <form class="d-flex" role="search" action="index.php">
            <input class="form-control me-2 fw-medium" type="search" placeholder="Search" aria-label="Search" name="search">
            <button class="btn btn-outline-light fw-medium" type="submit"><i class="bi bi-search"></i></button>
          </form>
        </div>
      </div>
    </nav>