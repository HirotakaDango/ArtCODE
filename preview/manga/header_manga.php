        <nav class="py-2 my-3 container" aria-label="breadcrumb">
          <div class="d-none d-md-block d-lg-block">
            <div class="p-3 rounded-4 shadow bg-body-tertiary">
              <div class="btn-group w-100">
                <a class="btn py-2 rounded link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/feeds/manga/') === false) echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./">Home</a>
                <a class="btn py-2 rounded link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'parodies.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./parodies.php">Parodies</a>
                <a class="btn py-2 rounded link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'characters.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./characters.php">Characters</a>
                <a class="btn py-2 rounded link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'tags.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./tags.php">Tags</a>
                <a class="btn py-2 rounded link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'artists.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./artists.php">Artists</a>
                <a class="btn py-2 rounded link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'groups.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./groups.php">Groups</a>
                <form class="d-flex ms-4" role="search" action="./">
                  <input class="form-control me-2 fw-medium" type="search" placeholder="Search" aria-label="Search" name="search">
                  <button class="btn btn-outline-dark fw-medium" type="submit"><i class="bi bi-search"></i></button>
                </form>
              </div>
            </div>
          </div>
          <div class="d-md-none d-lg-none">
            <a class="btn bg-secondary p-3 bg-opacity-25 fw-bold w-100 text-start" data-bs-toggle="collapse" href="#collapseModal" role="button" aria-expanded="false" aria-controls="collapseExample">
              <i class="bi bi-list" style="-webkit-text-stroke: 1px;"></i> Menu
            </a>
            <div class="collapse bg-secondary bg-opacity-25 mt-2 rounded" id="collapseModal">
              <div class="btn-group-vertical w-100">
                <a class="btn py-2 rounded text-start link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/preview/manga/') === false) echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./">Home</a>
                <a class="btn py-2 rounded text-start link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'parodies.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./parodies.php">Parodies</a>
                <a class="btn py-2 rounded text-start link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'characters.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./characters.php">Characters</a>
                <a class="btn py-2 rounded text-start link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'tags.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./tags.php">Tags</a>
                <a class="btn py-2 rounded text-start link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'artists.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./artists.php">Artists</a>
                <a class="btn py-2 rounded text-start link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'groups.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./groups.php">Groups</a>
              </div>
              <form class="d-flex p-2" role="search" action="./">
                <input class="form-control me-2 fw-medium" type="search" placeholder="Search" aria-label="Search" name="search">
                <button class="btn btn-outline-dark fw-medium" type="submit"><i class="bi bi-search"></i></button>
              </form>
            </div>
          </div>
        </nav>