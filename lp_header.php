  <header>
    <div class="d-flex flex-column flex-md-row align-items-center text-secondary fw-bold pb-3 mb-4 container">
      <h1 href="/" class="d-flex align-items-center text-dark text-decoration-none">
        <div class="row">
          <div class="col-md-12 text-center py-2">
            <h1 class="animate__animated animate__fadeInDown"><a class="text-decoration-none text-dark fw-bold" href="index.php">ArtCODE</a></h1>
            <h4 class="animate__animated animate__fadeInUp text-secondary fw-bold">Inspiring Art Collection</h4>
          </div>
        </div>    
      </h1>
      <nav class="d-inline-flex mt-2 mt-md-0 ms-md-auto">
        <a class="me-3 py-2 text-secondary text-decoration-none <?php if(basename($_SERVER['PHP_SELF']) == 'session.php') echo 'border-bottom border-3' ?>" href="session.php">Features</a>
        <a class="me-3 py-2 text-secondary text-decoration-none" data-bs-toggle="modal" data-bs-target="#signin">Signin</a>
        <a class="me-3 py-2 text-secondary text-decoration-none" data-bs-toggle="modal" data-bs-target="#signup">Signup</a>
        <a class="me-3 py-2 text-secondary text-decoration-none <?php if(basename($_SERVER['PHP_SELF']) == 'preview_guest.php') echo 'border-bottom border-3' ?>" href="preview_guest.php">Explore</a>
        <a class="py-2 text-secondary text-decoration-none <?php if(basename($_SERVER['PHP_SELF']) == 'newspage.php') echo 'border-bottom border-3' ?>" href="newspage.php">News</a>
      </nav>
    </div>
  </header>