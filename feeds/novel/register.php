<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <link rel="stylesheet" href="session.css">
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="../../swup/transitions.css" />
    <script type="module" src="../../swup/swup.js"></script>
  </head>
  <body>
    <main id="swup" class="transition-main">
      <?php include('terms.php');?>
      <div class="d-flex justify-content-center d-md-none d-lg-none background-image position-absolute top-50 start-50 translate-middle">
        <div class="modal modal-sheet position-static d-block container-fluid" tabindex="-1" role="dialog" id="modalSignin">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark bg-opacity-25 shadow border-0 rounded-5">
              <div class="modal-body">
                <a class="btn bg-dark bg-opacity-25 position-absolute top-0 start-0 z-3" style="border-radius: 1.5rem 0 1.5rem 0;" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/?tourl=<?php echo urlencode(isset($_GET['tourl']) ? $_GET['tourl'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/feeds/novel/'); ?>"><i class="bi bi-chevron-left fs-4 text-stroke-2"></i></a>
                <div class="text-center text-white fw-bold mt-4">
                  <h2 class="fw-bold">Welcome to register</h2>
                  <h2 class="mb-5 fw-bold">Sign up to explore</h2>
                </div>
                <div class="modal-body p-4 pt-0">
                  <form action="session_code.php" method="post">
                    <input type="hidden" name="tourl" value="<?php echo urlencode(isset($_GET['tourl']) ? $_GET['tourl'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/feeds/novel/'); ?>">
                    <div class="form-floating mb-3">
                      <input name="artist" type="text" class="form-control rounded-3 bg-dark bg-opacity-25" maxlength="40" id="floatingInput" placeholder="Your name" required>
                      <label class="fw-bold text-white fw-medium" for="floatingInput">Your name</label>
                    </div>
                    <div class="form-floating mb-3">
                      <input name="email" type="email" class="form-control rounded-3 bg-dark bg-opacity-25" id="floatingInput" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" placeholder="name@example.com" required>
                      <label class="fw-bold text-white fw-medium" for="floatingInput">Email address</label>
                    </div>
                    <div class="form-floating mb-3">
                      <input name="password" type="password" class="form-control rounded-3 bg-dark bg-opacity-25" id="floatingPassword" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" placeholder="Password" required>
                      <label class="fw-bold text-white fw-medium" for="floatingPassword">Password</label>
                    </div>
                    <p class="text-white fw-bold"><input class="form-check-input bg-light bg-opacity-25" type="checkbox" value="" id="flexCheckDefault" required> By clicking this, you'll agree with the <a class="text-white" href="#" data-bs-target="#terms" data-bs-toggle="modal">terms of service</a>.</p>
                    <button name="register" class="w-100 fw-bold mb-2 btn btn-lg rounded-3 btn-primary" type="submit">Register</button>
                    <p class="text-white fw-bold">Already have an account? <a href="login.php?tourl=<?php echo urlencode(isset($_GET['tourl']) ? $_GET['tourl'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/feeds/novel/'); ?>" class="text-decoration-none btn btn-primary btn-sm text-white fw-bold rounded-pill opacity-75">Signin</a></p>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="d-none d-md-block d-lg-block">
        <div class="position-absolute top-50 start-50 translate-middle">
          <div class="row justify-content-center p-0">
            <div class="col-md-12 w-100 p-0 col-lg-10">
              <div class="wrap d-md-flex d-lg-flex" style="max-width: 675px;">
                <div class="text-wrap p-4 p-lg-5 rounded-start-4 bg-dark bg-gradient bg-opacity-75 text-center" style="max-width: 290px;">
                  <div class="justify-content-center d-flex align-items-center h-100">
                    <div class="container">
                      <h2 class="fw-bold">Welcome to signup</h2>
                      <h5 class="text-nowrap fw-bold">Sign up to explore</h5>
                      <p class="fw-medium">Already have an account?</p>
                      <a class="btn btn-sm btn-outline-light rounded-pill fw-bold" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/?tourl=<?php echo urlencode(isset($_GET['tourl']) ? $_GET['tourl'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/feeds/novel/'); ?>"><i class="bi bi-chevron-left text-stroke-2"></i> back</a>
                      <a class="btn btn-sm btn-outline-light rounded-pill fw-bold" href="login.php?tourl=<?php echo urlencode(isset($_GET['tourl']) ? $_GET['tourl'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/feeds/novel/'); ?>">Sign in</a>
                    </div>
                  </div>
                </div>
                <div class="container-fluid rounded-end-4 bg-dark bg-opacity-25" style="width: 385px;">
                  <div class="text-center">
                    <h3 class="mt-3 fw-bold">Sign Up</h3>
                  </div>
                  <div class="justify-content-center d-flex align-items-center h-100">
                    <div class="modal-body p-4 pt-0">
                      <form class="" action="session_code.php" method="post">
                        <input type="hidden" name="tourl" value="<?php echo urlencode(isset($_GET['tourl']) ? $_GET['tourl'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/feeds/novel/'); ?>">
                        <div class="form-floating mb-3">
                          <input name="artist" type="text" class="form-control rounded-3 bg-dark bg-opacity-25" maxlength="40" id="floatingInput" placeholder="Your name" required>
                          <label class="fw-bold text-white fw-medium" for="floatingInput">Your name</label>
                        </div>
                        <div class="form-floating mb-3">
                          <input name="email" type="email" class="form-control rounded-3 bg-dark bg-opacity-25" id="floatingInput" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" placeholder="name@example.com" required>
                          <label class="fw-bold text-white fw-medium" for="floatingInput">Email address</label>
                        </div>
                        <div class="form-floating mb-3">
                          <input name="password" type="password" class="form-control rounded-3 bg-dark bg-opacity-25" id="floatingPassword" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" placeholder="Password" required>
                          <label class="fw-bold text-white fw-medium" for="floatingPassword">Password</label>
                        </div>
                        <div class="d-flex">
                          <input class="form-check-input bg-light bg-opacity-25 me-2" type="checkbox" value="" id="flexCheckDefault" required> <p class="text-white fw-bold">By clicking this, you'll agree with the <a class="text-white" href="#" data-bs-target="#terms" data-bs-toggle="modal">terms of service</a>.</p>
                        </div>  
                        <button name="register" class="w-100 fw-bold mb-4 btn btn-lg rounded-3 btn-primary" type="submit">Register</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>