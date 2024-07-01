        <section>
          <!-- Desktop -->
          <div class="container-fluid pt-2 px-md-5 animate__animated animate__fadeInDown animate__delay-1s d-none d-md-block d-lg-block">
            <header class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between mb-4 mb-md-0">
              <div class="col-md-3 mb-2 mb-md-0">
                <div href="/" class="d-flex align-items-center text-dark text-decoration-none">
                  <div class="row">
                    <div class="col-md-12 text-center py-2">
                      <h1>
                        <a class="text-decoration-none <?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'text-white' : 'text-dark'; ?> fw-bold" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>">ArtCODE</a>
                      </h1>
                      <h6 class="<?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'text-white' : 'text-dark'; ?> small fw-bold">Inspiring Art Collection</h6>
                    </div>
                  </div>
                </div>
              </div>
              <ul class="nav col-12 col-md-auto mb-2 justify-content-center mb-md-0 fw-bold gap-2">
                <li><a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/?tourl=<?php echo urlencode(isset($_GET['tourl']) ? $_GET['tourl'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/home/'); ?>" class="nav-link px-2 link-body-emphasis <?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'text-white' : 'text-dark'; ?> rounded-pill">Home</a></li>
                <li><a href="#features" class="nav-link px-2 link-body-emphasis <?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'text-white' : 'text-dark'; ?> rounded-pill">Features</a></li>
                <li><a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/newspage.php" class="nav-link px-2 link-body-emphasis <?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'text-white' : 'text-dark'; ?> rounded-pill">News</a></li>
                <li>
                  <div class="dropdown">
                    <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" class="nav-link px-2 link-body-emphasis <?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'text-white' : 'text-dark'; ?> rounded-pill">Explores</a>
                    <ul class="dropdown-menu rounded-4 border-0">
                      <li><a class="dropdown-item fw-bold" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/preview_guest.php?tourl=<?php echo urlencode(isset($_GET['tourl']) ? $_GET['tourl'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/home/'); ?>">Explore Images</a></li>
                      <li><a class="dropdown-item fw-bold" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/music/?tourl=<?php echo urlencode(isset($_GET['tourl']) ? $_GET['tourl'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/home/'); ?>">Explore Music</a></li>
                      <li><a class="dropdown-item fw-bold" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/manga/">Explore Manga</a></li>
                    </ul>
                  </div>
                </li>
              </ul>
              <div class="col-md-3 text-end d-none d-md-block d-lg-block">
                <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/login.php?tourl=<?php echo urlencode(isset($_GET['tourl']) ? $_GET['tourl'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/home/'); ?>" class="btn text-light-subtle border-0 link-body-emphasis fw-bold me-2"><i class="bi bi-person-fill-up"></i> sign in</a>
                <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/register.php?tourl=<?php echo urlencode(isset($_GET['tourl']) ? $_GET['tourl'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/home/'); ?>" class="btn rounded-pill <?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'btn-outline-light' : 'btn-outline-dark'; ?> fw-bold"><i class="bi bi-person-plus-fill"></i> sign up for free</a>
              </div>
            </header>
          </div>
          <!-- Mobile -->
          <nav class="navbar navbar-dark bg-transparent d-md-none d-lg-none">
            <div class="container-fluid">
              <a class="navbar-brand mx-auto <?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'text-white' : 'text-dark'; ?>" href="/"><h1 class="fw-bold">ArtCODE</h1></a>
              <button class="btn border-0 link-body-emphasis position-absolute start-0 top-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar">
                <i class="bi bi-list fs-1" style="-webkit-text-stroke: 2px;"></i>
              </button>
              <div class="offcanvas offcanvas-start w-100 text-bg-dark" tabindex="-1" id="offcanvasDarkNavbar" aria-labelledby="offcanvasDarkNavbarLabel">
                <div class="offcanvas-header">
                  <h5 class="offcanvas-title" id="offcanvasDarkNavbarLabel">Menu</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                  <div data-bs-theme="dark">
                    <div class="btn-group gap-2 w-100">
                      <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'index.php') !== false) echo 'opacity-75 shadow'; ?>" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/">
                        <i class="bi bi-house-fill fs-5"></i>
                        <span class="d-md-none d-lg-inline d-lg-none">Home</span>
                      </a>
                      <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/#features">
                        <i class="bi bi-book-half fs-5"></i>
                        <span class="d-md-none d-lg-inline d-lg-none">Features</span>
                      </a>
                    </div>
                    <div class="btn-group gap-2 w-100 mt-2">
                      <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/notes/') !== false) echo 'opacity-75 shadow'; ?>" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/newspage.php">
                        <i class="bi bi-newspaper fs-5"></i>
                        <span class="d-md-none d-lg-inline d-lg-none">News</span>
                      </a>
                      <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column" data-bs-toggle="collapse" href="#collapseExplore" role="button" aria-expanded="false" aria-controls="collapseExample">
                        <i class="bi bi-compass-fill fs-5"></i>
                        <span class="d-md-none d-lg-inline d-lg-none">Explores</span>
                      </a>
                    </div>
                    <div class="collapse-content">
                      <div class="collapse mt-2" id="collapseExplore">
                        <div class="card card-body rounded-4 border-0 bg-body-tertiary">
                          <div class="btn-group-vertical gap-2">
                            <h6 class="fw-bold text-start">Explores</h6>
                            <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/preview_guest.php">Explore Images</a></li>
                            <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/music/">Explore Music</a>
                            <a class="text-start btn bg-body-tertiary link-body-emphasis rounded-4 w-100 border-0 fw-bold" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/manga/">Explore Manga</a>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="container-fluid rounded-pill bg-body-tertiary p-2 mt-2">
                      <form class="d-flex" role="search" action="../search.php" method="GET">
                        <input class="form-control fw-medium me-2 border-0 rounded-start-5 bg-dark bg-opacity-50 focus-ring focus-ring-dark" name="search" type="search" placeholder="Search tags or titles..." aria-label="Search">
                        <div class="border-end border-start border-2"></div>
                        <button class="btn ms-2 border-0 rounded-end-5 bg-dark bg-opacity-50" type="submit"><i class="bi bi-search" style="-webkit-text-stroke: 2px;"></i></button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </nav>
        </section>