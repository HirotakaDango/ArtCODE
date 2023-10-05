  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <style>
    .animate__animated.animate__delay-0-5s {
      -webkit-animation-delay: calc(1s * 0.5);
      animation-delay: calc(1s * 0.5);
      -webkit-animation-delay: calc(var(--animate-delay) * 0.5);
      animation-delay: calc(var(--animate-delay) * 0.5);
    }
      
    .animate__animated.animate__delay-1-5s {
      -webkit-animation-delay: calc(1s * 1.5);
      animation-delay: calc(1s * 1.5);
      -webkit-animation-delay: calc(var(--animate-delay) * 1.5);
      animation-delay: calc(var(--animate-delay) * 1.5);
    }
      
    .animate__animated.animate__delay-2-5s {
      -webkit-animation-delay: calc(1s * 2.5);
      animation-delay: calc(1s * 2.5);
      -webkit-animation-delay: calc(var(--animate-delay) * 2.5);
      animation-delay: calc(var(--animate-delay) * 2.5);
    } 
      
    .animate__animated.animate__delay-3-5s {
      -webkit-animation-delay: calc(1s * 3.5);
      animation-delay: calc(1s * 3.5);
      -webkit-animation-delay: calc(var(--animate-delay) * 3.5);
      animation-delay: calc(var(--animate-delay) * 3.5);
    }
  </style>
  <header>
    <div class="d-flex flex-column flex-md-row align-items-center text-secondary fw-bold pb-3 mb-4 container">
      <h1 href="/" class="d-flex align-items-center text-dark text-decoration-none">
        <div class="row">
          <div class="col-md-12 text-center py-2">
            <h1 class="animate__animated animate__fadeInDown"><a class="text-decoration-none text-dark fw-bold" href="/">ArtCODE</a></h1>
            <h4 class="animate__animated animate__fadeInUp text-secondary fw-bold">Inspiring Art Collection</h4>
          </div>
        </div>    
      </h1>
      <nav class="d-inline-flex mt-2 mt-md-0 ms-md-auto">
        <a class="me-3 py-2 text-secondary text-decoration-none animate__animated animate__fadeInDown animate__delay-1s <?php if(basename($_SERVER['PHP_SELF']) == 'features.php') echo 'border-bottom border-3' ?>" href="features.php">Features</a>
        <a class="me-3 py-2 text-secondary text-decoration-none animate__animated animate__fadeInDown animate__delay-1-5s" href="login.php">Signin</a>
        <a class="me-3 py-2 text-secondary text-decoration-none animate__animated animate__fadeInDown animate__delay-2s" href="register.php">Signup</a>
        <a class="me-3 py-2 text-secondary text-decoration-none animate__animated animate__fadeInDown animate__delay-2-5s <?php if(basename($_SERVER['PHP_SELF']) == 'preview_guest.php') echo 'border-bottom border-3' ?>" href="preview_guest.php">Explore</a>
        <a class="py-2 text-secondary text-decoration-none animate__animated animate__fadeInDown animate__delay-3s <?php if(basename($_SERVER['PHP_SELF']) == 'newspage.php') echo 'border-bottom border-3' ?>" href="newspage.php">News</a>
      </nav>
    </div>
  </header>