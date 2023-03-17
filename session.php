<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  </head>
  <body>
    <?php include('landing_page.php');?>
    <div class="modal fade" id="signin" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalToggleLabel">Sign In</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <center><h1><i class="bi bi-person-circle"></i></h1></center>
            <center><h2 class="fw-bold">WELCOME BACK!</h2></center>
            <center><h2 class="mb-5 fw-bold">LOGIN TO CONTINUE</h2></center>
            <div class="modal-body p-4 pt-0">
              <form class="" action="session_code.php" method="post">
                <div class="form-floating mb-3">
                  <input name="username" type="email" class="form-control rounded-3" id="floatingInput" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" placeholder="name@example.com" required>
                  <label for="floatingInput">Email address</label>
                </div>
                <div class="form-floating mb-3">
                  <input name="password" type="password" class="form-control rounded-3" id="floatingPassword" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" placeholder="Password" required>
                  <label for="floatingPassword">Password</label>
                </div>
                <button name="login" class="w-100 fw-bold mb-2 btn btn-lg rounded-3 btn-primary" type="submit">Login</button>
                <p>Don't have an account? <button class="text-decoration-none btn btn-primary btn-sm text-white fw-bold rounded-pill opacity-75" data-bs-target="#signup" data-bs-toggle="modal">Signup</button></p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="signup" aria-hidden="true" aria-labelledby="exampleModalToggleLabel2" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalToggleLabel2">Sign Up</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <center><h1><i class="bi bi-person-circle"></i></h1></center>
            <center><h2 class="fw-bold">HELLO, NEW USER?</h2></center>
            <center><h2 class="mb-5 fw-bold">REGISTER TO CONTINUE</h2></center>
            <div class="modal-body p-4 pt-0">
              <form class="" action="session_code.php" method="post">
                <div class="form-floating mb-3">
                  <input name="artist" type="text" class="form-control rounded-3" maxlength="40" id="floatingInput" placeholder="Your name" required>
                  <label for="floatingInput">Your name</label>
                </div>
                <div class="form-floating mb-3">
                  <input name="username" type="email" class="form-control rounded-3" id="floatingInput" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" placeholder="name@example.com" required>
                  <label for="floatingInput">Email address</label>
                </div>
                <div class="form-floating mb-3">
                  <input name="password" type="password" class="form-control rounded-3" id="floatingPassword" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" placeholder="Password" required>
                  <label for="floatingPassword">Password</label>
                </div>
                <button name="register" class="w-100 fw-bold mb-2 btn btn-lg rounded-3 btn-primary" type="submit">Register</button>
                <p>Already have an account? <button class="text-decoration-none btn btn-primary btn-sm text-white fw-bold rounded-pill opacity-75" data-bs-target="#signin" data-bs-toggle="modal">Signin</button></p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>
