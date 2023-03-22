    <div class="modal fade" id="signin" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header border-bottom-0">
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
                <p class="text-secondary fw-bold">Don't have an account? <button class="text-decoration-none btn btn-primary btn-sm text-white fw-bold rounded-pill opacity-75" data-bs-target="#signup" data-bs-toggle="modal">Signup</button></p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="signup" aria-hidden="true" aria-labelledby="exampleModalToggleLabel2" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header border-bottom-0">
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
                <p class="text-secondary fw-bold">Already have an account? <button class="text-decoration-none btn btn-primary btn-sm text-white fw-bold rounded-pill opacity-75" data-bs-target="#signin" data-bs-toggle="modal">Signin</button></p>
                <p class="text-secondary fw-bold"><input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" required> By clicking this, you'll agree with the terms of service.</p>
                <button class="btn btn-sm rounded-pill text-white fw-bold btn-primary" data-bs-target="#terms" data-bs-toggle="modal">Terms of Service</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include('terms.php'); ?>