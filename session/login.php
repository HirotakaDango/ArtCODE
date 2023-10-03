<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <link rel="stylesheet" href="session.css">
    <?php include('../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="../swup/transitions.css" />
    <script type="module" src="../swup/swup.js"></script>
  </head>
  <body>
    <main id="swup" class="transition-main">
      <?php include('help.php');?>
      <div class="d-flex justify-content-center d-md-none d-lg-none background-image position-absolute top-50 start-50 translate-middle">
        <div class="modal modal-sheet position-static d-block container-fluid" tabindex="-1" role="dialog" id="modalSignin">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark bg-opacity-25 shadow">
              <div class="modal-body">
                <a class="btn bg-dark bg-opacity-25 position-absolute top-0 start-0" style="border-radius: 0.5rem 0 0.5rem 0;" href="features.php"><i class="bi bi-chevron-left fs-4 text-stroke-2"></i></a>
                <div class="text-center text-white fw-bold mt-4">
                  <h2 class="fw-bold">Welcome to login</h2>
                  <h2 class="mb-5 fw-bold">Sign in to continue</h2>
                </div>
                <div class="modal-body p-4 pt-0">
                  <form class="" action="session_code.php" method="post">
                    <div class="form-floating mb-3">
                      <input name="email" type="email" class="form-control rounded-3 bg-dark bg-opacity-25" id="floatingInput" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" placeholder="name@example.com" required>
                      <label class="fw-bold text-white fw-medium" for="floatingInput">Email address</label>
                    </div>
                    <div class="form-floating mb-3">
                      <input name="password" type="password" class="form-control rounded-3 bg-dark bg-opacity-25" id="floatingPassword" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" placeholder="Password" required>
                      <label class="fw-bold text-white fw-medium" for="floatingPassword">Password</label>
                    </div>
                    <div class="form-check mb-3">
                      <input class="form-check-input bg-light bg-opacity-25" type="checkbox" id="remember" name="remember">
                      <label class="form-check-label fw-bold text-white fw-medium" for="remember">Remember Me</label>
                    </div>
                    <button name="login" class="w-100 fw-bold mb-2 btn btn-lg rounded-3 btn-primary" type="submit">Login</button>
                    <p class="text-white fw-medium fw-bold">Don't have an account? <a href="register.php" class="text-decoration-none text-white btn btn-primary btn-sm text-white fw-bold rounded-pill white-75">Signup</a></p>
                    <p class="text-white fw-medium fw-bold">Having a trouble with your <a class="text-white" data-bs-target="#help" href="#" data-bs-toggle="modal">account</a>?</p>
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
              <div class="wrap d-md-flex d-lg-flex">
                <div class="text-wrap p-4 p-lg-5 rounded-start-4 bg-dark bg-gradient bg-opacity-75 text-center">
                  <div class="justify-content-center d-flex align-items-center h-100">
                    <div class="container w-100">
                      <h2 class="fw-bold">Welcome to login</h2>
                      <h5 class="text-nowrap fw-bold">Sign in to continue</h5>
                      <p class="fw-medium">Don't have an account?</p>
                      <a class="btn btn-sm btn-outline-light rounded-pill fw-bold" href="features.php"><i class="bi bi-chevron-left text-stroke-2"></i> back</a>
                      <a class="btn btn-sm btn-outline-light rounded-pill fw-bold" href="register.php">Sign up</a>
                      <p class="mt-5 fw-medium fw-medium">Having a trouble with your <a class="text-white" data-bs-target="#help" href="#" data-bs-toggle="modal">account</a>?</p>
                    </div>
                  </div>
                </div>
                <div class="container-fluid rounded-end-4 bg-dark bg-opacity-25">
                  <div class="text-center">
                      <h3 class="mt-3 fw-bold">Sign In</h3>
                    </div>
                  <div class="justify-content-center d-flex align-items-center h-100">
                  <div class="modal-body p-4 pt-0">
                    <form class="" action="session_code.php" method="post">
                      <div class="form-floating mb-3">
                        <input name="email" type="email" class="form-control rounded-3 bg-dark bg-opacity-25" id="floatingInput" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" placeholder="name@example.com" required>
                        <label class="fw-bold text-white fw-medium" for="floatingInput">Email address</label>
                      </div>
                      <div class="form-floating mb-3">
                        <input name="password" type="password" class="form-control rounded-3 bg-dark bg-opacity-25" id="floatingPassword" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" placeholder="Password" required>
                        <label class="fw-bold text-white fw-medium" for="floatingPassword">Password</label>
                      </div>
                      <div class="form-check mb-3">
                        <input class="form-check-input bg-light bg-opacity-25" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label fw-bold text-white fw-medium" for="remember">Remember Me</label>
                      </div>
                      <button name="login" class="w-100 fw-bold mb-2 btn btn-lg rounded-3 btn-primary" type="submit">Login</button>
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
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>
