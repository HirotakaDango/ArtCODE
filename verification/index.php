<?php
require_once('../auth.php');

$email = $_SESSION['email'];

// Connect to the SQLite database
$db = new SQLite3('../database.sqlite');

// Check if verification code is provided
if (isset($_GET['v'])) {
  $verificationCode = $_GET['v'];

  // Prepare a statement to retrieve the user with the given email and verification code
  $stmt = $db->prepare('SELECT id FROM users WHERE email = :email AND verification_code = :verification_code');
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':verification_code', $verificationCode, SQLITE3_TEXT);
  $result = $stmt->execute();

  // Check if the verification code is valid for the given email
  if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $userId = $row['id'];

    // Update the verified column to "yes"
    $updateStmt = $db->prepare('UPDATE users SET verified = "yes" WHERE id = :id');
    $updateStmt->bindValue(':id', $userId, SQLITE3_INTEGER);
    $updateStmt->execute();

    echo '<div class="alert alert-success" role="alert">Your account has been successfully verified! Redirecting to your profile in <span id="countdown">5</span> seconds.</div>';
    echo '<script>
      // Countdown and redirect
      let countdownElement = document.getElementById("countdown");
      let countdownValue = parseInt(countdownElement.textContent);
      const countdownInterval = setInterval(function() {
        countdownValue--;
        countdownElement.textContent = countdownValue;
        if (countdownValue <= 0) {
          clearInterval(countdownInterval);
          window.location.href = "../profile.php";
        }
      }, 1000);
    </script>';
  } else {
    echo '<div class="alert alert-danger" role="alert">Invalid verification code or email.</div>';
  }
} else {
  // Display form if verification code is not provided
  ?>
  <!DOCTYPE html>
  <html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Verify</title>
      <link rel="icon" type="image/png" href="/icon/favicon.png">
      <?php include('../bootstrapcss.php'); ?>
    </head>
    <body>
      <div class="container">
        <a class="btn btn-primary rounded-pill position-fixed top-0 start-0 m-2 fw-medium btn-sm" href="/profile.php"><i class="bi bi-arrow-left"></i> back</a>
        <div class="d-flex justify-content-center align-items-center vh-100 w-100">
          <div class="card p-3 border-0 bg-body-tertiary rounded-4 shadow w-100" style="max-width: 500px;">
            <div class="card-body">
              <h5 class="card-title fw-bold">Enter Your Verification Code</h5>
              <h6 class="small mb-4">You need to verify your account to upload your media. We implement this to prevent spamming from unverified accounts.</h6>
              <form action="" method="get">
                <div class="form-group">
                  <input type="text" id="verificationCode" name="v" class="form-control border-0 fw-medium" placeholder="Verification Code" required>
                </div>
                <button type="submit" class="btn btn-primary mt-2 fw-medium w-100">Verify</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </body>
  </html>
  <?php
}
?>