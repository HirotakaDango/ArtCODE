<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <link rel="stylesheet" href="session.css">
    <?php include('../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('terms.php');?>
    <div class="d-flex justify-content-center background-image">
      <div class="modal modal-sheet position-static d-block container-fluid" tabindex="-1" role="dialog" id="modalSignin">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content bg-dark bg-opacity-25 shadow">
             <a class="btn bg-dark bg-opacity-25 position-absolute top-0 start-0 z-3" style="border-radius: 0.5rem 0 0.5rem 0;" href="session.php"><i class="bi bi-chevron-left fs-4 text-stroke-2"></i></a>
            <div class="modal-body">
              <div class="text-center text-white fw-bold mt-4">
                <h2 class="fw-bold">HELLO, NEW USER?</h2>
                <h2 class="mb-5 fw-bold">REGISTER TO EXPLORE</h2>
              </div>
              <div class="modal-body p-4 pt-0">
                <form class="" action="session_code.php" method="post">
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
                  <p class="text-white fw-bold">Already have an account? <a href="login.php" class="text-decoration-none btn btn-primary btn-sm text-white fw-bold rounded-pill opacity-75">Signin</a></p>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>
