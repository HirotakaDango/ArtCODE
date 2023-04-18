<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$username = 'Admin';
$password = 'Admin';

if (isset($_GET['username'])) {
  $username = filter_var($_GET['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
}

if (isset($_GET['password'])) {
  $password = filter_var($_GET['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
}

if (isset($_GET['username']) && isset($_GET['password']) && $username === 'Ray' && $password === 'dunnoboiiizzz') {
  $_SESSION['authenticated'] = true;
} else if (isset($_GET['username']) || isset($_GET['password'])) {
  $alert = '
    <div class="alert alert-danger">
      You entered the wrong username or password, please try again!
    </div>
  ';
}

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
  echo '
        <!DOCTYPE html>
        <html>
        <head>
          <link rel="icon" type="image/png" href="icon.png">
          <meta charset="UTF-8">
          <link rel="manifest" href="manifest.json">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
          <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
        </head>
        <body>
          <div class="modal modal-sheet d-block p-4 py-md-5" tabindex="-1" role="dialog" id="modalSignin">
            <div class="modal-dialog" role="document">
              <div class="modal-content rounded-4 shadow">
                <div class="text-center p-5 pb-4 border-bottom-0">
                  <i class="bi bi-person-fill-gear display-2"></i>
                  <h1 class="fw-bold mb-0 mt-2 fs-2">ADMIN SECTION</h1>
                </div>
 
                <div class="modal-body p-5 pt-0">
                  ' . ($alert ?? '') . '
                  <form class="">
                    <div class="form-floating mb-3">
                      <input type="text" class="form-control text-secondary fw-bold rounded-3" name="username" id="floatingInput" placeholder="Enter username" required>
                      <label class="fw-bold text-secondary" for="floatingInput">Enter username</label>
                    </div>
                    <div class="form-floating mb-3">
                      <input type="password" class="form-control text-secondary fw-bold rounded-3" name="password" id="floatingPassword" placeholder="Enter password" required>
                      <label class="fw-bold text-secondary" for="floatingPassword">Enter password</label>
                    </div>
                    <button class="fw-bold w-100 mb-2 btn btn-lg rounded-3 btn-primary" type="submit">Submit</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </body>
        </html>
       ';
  exit;
}

header('Location: ../admin/index.php');
exit;
?>
