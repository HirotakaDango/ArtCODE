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
                  <input class="form-control fw-medium w-100 rounded rounded-end-0" type="search" placeholder="Search" aria-label="Search" name="search" value="<?php echo isset($_GET['search']) && $_GET['search'] !== '' ? $_GET['search'] : ''; ?>">
                  <button class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded rounded-start-0" type="submit"><i class="bi bi-search"></i></button>
                </form>
              </div>
            </div>
          </div>
          <div class="d-md-none d-lg-none bg-secondary p-2 bg-opacity-25 rounded">
            <div class="d-flex align-items-center gap-2">
              <form class="d-flex w-100" role="search" action="./">
                <input class="form-control fw-medium w-100 rounded-1 rounded-end-0" type="search" placeholder="Search" aria-label="Search" name="search" value="<?php echo isset($_GET['search']) && $_GET['search'] !== '' ? htmlspecialchars($_GET['search']) : 'Search'; ?>">
                <button class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-1 rounded-start-0" type="submit"><i class="bi bi-search"></i></button>
              </form>
              <a class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-1 p-0 px-2" data-bs-toggle="collapse" href="#collapseModal" role="button" aria-expanded="false" aria-controls="collapseExample">
                <i class="bi bi-list" style="-webkit-text-stroke: 1px; font-size: 1.5em;"></i>
              </a>
            </div>
            </div>
            <div class="collapse bg-secondary bg-opacity-25 mt-2 rounded" id="collapseModal">
            <div class="btn-group-vertical w-100">
              <a class="btn py-2 rounded text-start link-body-emphasis <?php echo (basename($_SERVER['PHP_SELF']) === 'index.php' && (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/' || preg_match('/\/(index\.php)?$/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)))) ? 'fw-bold' : 'fw-medium'; ?>" href="./">Home</a>
              <a class="btn py-2 rounded text-start link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'parodies.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./parodies.php">Parodies</a>
              <a class="btn py-2 rounded text-start link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'characters.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./characters.php">Characters</a>
              <a class="btn py-2 rounded text-start link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'tags.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./tags.php">Tags</a>
              <a class="btn py-2 rounded text-start link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'artists.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./artists.php">Artists</a>
              <a class="btn py-2 rounded text-start link-body-emphasis <?php if(basename($_SERVER['PHP_SELF']) == 'groups.php') echo 'fw-bold'; else echo 'fw-medium'; ?>" href="./groups.php">Groups</a>
            </div>
          </div>
        </nav>