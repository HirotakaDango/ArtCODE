        <section>
          <div class="container-fluid">
            <header class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between py-3 mb-4">
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
              <ul class="nav col-12 col-md-auto mb-2 justify-content-center mb-md-0 fw-bold">
                <li><a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session" class="nav-link px-2 clickable-card <?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'text-white' : 'text-dark'; ?> rounded-pill">Home</a></li>
                <li><a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/#features" class="nav-link px-2 clickable-card <?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'text-white' : 'text-dark'; ?> rounded-pill">Features</a></li>
                <li><a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/newspage.php" class="nav-link px-2 clickable-card <?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'text-white' : 'text-dark'; ?> rounded-pill">News</a></li>
                <li><a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/preview_guest.php" class="nav-link px-2 clickable-card <?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'text-white' : 'text-dark'; ?> rounded-pill">Explore</a></li>
              </ul>
              <div class="col-md-3 text-end d-none d-md-block d-lg-block">
                <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/login.php" class="btn btn-danger rounded-pill fw-bold me-2">sign in</a>
                <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/register.php" class="btn rounded-pill <?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'btn-outline-light' : 'btn-outline-dark'; ?> fw-bold">sign up</a>
              </div>
            </header>
          </div>
        </section>