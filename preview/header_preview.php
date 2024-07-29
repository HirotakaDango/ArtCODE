    <!-- Navbar -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <nav class="navbar fixed-top navbar-expand-md navbar-expand-lg navbar-light bg-body-tertiary">
      <div class="container-fluid position-relative">
        <button class="navbar-toggler1 d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
          <img src="/icon/toggle1.svg" width="22" height="22">
        </button> 
        <a class="text-dark navbar-brand fw-bold" href="/">
          ArtCODE
        </a>
        <div class="position-absolute top-50 start-50 translate-middle d-none d-md-block" style="padding-bottom: 0.1em;">
          <a class="btn border-0 fw-bold text-decoration-none text-dark link-body-emphasis <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'preview/home/') !== false) echo 'bg-dark-subtle rounded-pill py-1'; ?>" href="/">Home</a>
          <a class="btn border-0 fw-bold text-decoration-none text-dark link-body-emphasis" href="/manga/">Manga</a>
          <a class="btn border-0 fw-bold text-decoration-none text-dark link-body-emphasis <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'preview/music/') !== false) echo 'bg-dark-subtle rounded-pill py-1'; ?>" href="/preview/music/">Music</a>
          <a class="btn border-0 fw-bold text-decoration-none text-dark link-body-emphasis <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'preview/keyword/') !== false) echo 'bg-dark-subtle rounded-pill py-1'; ?>" href="#" data-bs-toggle="modal" data-bs-target="#searchModal">Search</a>
        </div>
        <div class="dropdown nav-right">
          <div class="btn-group gap-1">
            <a class="btn border-0 btn-sm link-body-emphasis px-0 fw-bold d-md-none" href="/session/login">sign in</a>
            <a class="btn border-0 btn-sm link-body-emphasis px-0 fw-bold d-none d-md-block" href="/session/login">login</a>
            <a class="btn border-0 btn-sm link-body-emphasis px-0 fw-bold d-none d-md-block" href="#"> / </a>
            <a class="btn border-0 btn-sm link-body-emphasis px-0 fw-bold d-none d-md-block" href="/session/register">register</a>
          </div>
        <div class="offcanvas offcanvas-start" tabindex="-1" id="navbar" aria-labelledby="navbarLabel">
          <div class="offcanvas-header">
            <a class="text-decoration-none link-body-emphasis link-light" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>"><h5 class="offcanvas-title fw-bold" id="navbarLabel">ArtCODE</h5></a>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body">
            <!-- Mobile -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 fw-bold d-md-none d-lg-none">
              <form action="/preview/keyword/" method="GET" class="mb-3">
                <div class="input-group">
                  <input type="text" name="q" class="form-control text-lowercase fw-bold rounded-end-0 rounded-4 border-0 bg-body-tertiary">
                  <button type="submit" class="btn bg-body-tertiary link-body-emphasis border-0 rounded-start-0 rounded-4"><i class="bi bi-search" style="-webkit-text-stroke: 1px;"></i></button>
                </div>
              </form>
              <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-100 d-flex justify-content-center align-items-center text-center flex-column mt-2" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/manga/">
                <i class="bi bi-journals fs-5"></i>
                <span class="d-lg-inline">Manga</span>
              </a>
              <div class="btn-group gap-2 w-100 mt-2">
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'home/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/">
                  <i class="bi bi-house-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Home</span>
                </a>
                <a class="btn bg-body-tertiary border-0 link-body-emphasis rounded-4 fw-bold p-3 w-50 d-flex justify-content-center align-items-center text-center flex-column <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'feeds/scrolls/') !== false) echo 'opacity-75 shadow'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/scrolls/">
                  <i class="bi bi-distribute-vertical fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none">Music</span>
                </a>
              </div>
            </ul>
            <!-- end -->
          </div>
        </div>
      </div>
    </nav>
    <br><br>
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content bg-transparent border-0">
          <div class="modal-body">
            <form class="input-group" role="search" action="/preview/keyword/">
              <input class="form-control rounded-start-4 border-0 bg-body-tertiary focus-ring focus-ring-light" name="q" type="search" placeholder="Search" aria-label="Search">
              <button class="btn rounded-end-4 border-0 bg-body-tertiary" type="submit"><i class="bi bi-search"></i></button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="mb-1"></div>
    <style>
      @media (min-width: 768px) {
        .navbar-nav {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
        }
      
        .nav-center {
          margin-left: 15px;
          margin-right: 15px;
        }
      
        .nav-right {
          position: absolute;
          right: 10px;
          top: 10;
          align-items: center;
        }
        
        .d-none-sm {
          display: none;
        }
      }
      
      @media (max-width: 767px) {
        .d-none-md-lg {
          display: none;
        }
        
        .navbar-brand {
          position: static;
          display: block;
          text-align: center;
          margin: auto;
          transform: none;
        }

        .navbar-brand {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          font-size: 18px;
        }
      }
      
      .btn-smaller {
        padding: 2px 4px;
      }
      
      .navbar {
        height: 45px;
      }
      
      .navbar-brand {
        font-size: 18px;
      }

      @media (min-width: 992px) {
        .navbar-toggler1 {
          display: none;
        }
      }
    
      .navbar-toggler1 {
        background-color: light;
        border: none;
        font-size: 8px;
        margin-top: -3px;
        border-radius: 5px;
        padding: 6px;
        transition: background-color 0.3s ease; 
      }
    </style>