        <section>
          <link rel="stylesheet" type="text/css" href="style.css" />
          <div class="container-fluid">
            <header class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between py-3 mb-4">
              <div class="col-md-3 mb-2 mb-md-0">
                <div href="/" class="d-flex align-items-center text-dark text-decoration-none">
                  <div class="row">
                    <div class="col-md-12 text-center py-2">
                      <h1>
                        <a class="text-decoration-none text-white text-shadow fw-bold" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>">ArtCODE</a>
                      </h1>
                      <h6 class="text-white text-shadow small fw-bold">Inspiring Art Collection</h6>
                    </div>
                  </div>
                </div>
              </div>
              <ul class="nav col-12 col-md-auto mb-2 justify-content-center mb-md-0 fw-bold">
                <li><a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session" class="nav-link text-white text-shadow px-2 clickable-card rounded-pill">Home</a></li>
                <li><a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/#features" class="nav-link text-white text-shadow px-2 clickable-card rounded-pill">Features</a></li>
                <li><a href="newspage.php" class="nav-link text-white text-shadow px-2 clickable-card rounded-pill">News</a></li>
                <li><a href="preview_guest.php" class="nav-link text-white text-shadow px-2 clickable-card rounded-pill">Explore</a></li>
              </ul>
              <div class="col-md-3 text-end">
                <a href="login.php" class="btn btn-sm btn-danger rounded-pill shadow fw-bold me-2">sign in</a>
                <a href="register.php" class="btn btn-sm btn-outline-light shadow text-shadow rounded-pill fw-bold">sign up</a>
              </div>
            </header>
          </div>
        </section>