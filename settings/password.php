<?php
require_once('../auth.php');

// check if form is submitted
if (isset($_POST['submit'])) {
  // get input values and limit password length to 40 characters
  $current_password = substr(filter_var($_POST['current_password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW), 0, 40);
  $new_password = substr(filter_var($_POST['new_password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW), 0, 40);
  $confirm_password = substr(filter_var($_POST['confirm_password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW), 0, 40);

  // get email from session
  $email = $_SESSION['email'];

  // get password from database
  $stmt = $db->prepare('SELECT password FROM users WHERE email=:email');
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $result = $stmt->execute();
  $row = $result->fetchArray(SQLITE3_ASSOC);
  $password = $row['password'];

  // check if current password is correct
  if ($current_password == $password) {
    // check if new password and confirm password match
    if ($new_password == $confirm_password) {
      // update password in database
      $stmt = $db->prepare('UPDATE users SET password=:new_password WHERE email=:email');
      $stmt->bindValue(':new_password', $new_password, SQLITE3_TEXT);
      $stmt->bindValue(':email', $email, SQLITE3_TEXT);
      $stmt->execute();

      // set success message
      $_SESSION['success'] = "Password successfully changed";

      // redirect to current page
      header("Location: password.php");
      exit();
    } else {
      // display error message
      $error = "New password and confirm password do not match";
    }
  } else {
    // display error message
    $error = "Current password is incorrect";
  }
}
?>

    <main id="swup" class="transition-main">
      <?php include('setheader.php'); ?>
        <?php if (isset($_SESSION['success'])): ?>
          <div class="alert alert-success"><?php echo $_SESSION['success']; ?></div>
        <?php unset($_SESSION['success']); endif; ?>
        <?php if (isset($error)): ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <div class="container mb-5 mt-4">
          <div class="d-md-none mb-4">
            <div class="d-flex">
              <a class="text-decoration-none text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" href="/settings/">
                <i class="bi bi-chevron-left" style="-webkit-text-stroke: 2px;"></i>
              </a>
            </div>
          </div>
          <h3 class="fw-bold mb-4">
            <i class="bi bi-key-fill"></i> Change Password
          </h3>
          <div class="card border-0 bg-body-tertiary rounded-4 shadow-sm p-4 mb-4">
            <h5 class="fw-bold">
              <i class="bi bi-person-circle me-2"></i> Update Password
            </h5>
            <p class="text-muted mb-4">Update your password below. Choose a strong password for better security.</p>
            <form method="POST">
              <div class="form-floating mb-2">
                <input type="password" class="form-control rounded-3 border fw-bold border-4" name="current_password" placeholder="Enter current password" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$">
                <label for="floatingPassword" class="fw-bold">Enter current password</label>
              </div>
              <div class="form-floating mb-2">
                <input type="password" class="form-control rounded-3 border fw-bold border-4" name="new_password" placeholder="Type new password" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$">
                <label for="floatingPassword" class="fw-bold">Type new password</label>
              </div>
              <div class="form-floating mb-2">
                <input type="password" class="form-control rounded-3 border fw-bold border-4" name="confirm_password" placeholder="Confirm new password" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$">
                <label for="floatingPassword" class="fw-bold">Confirm new password</label>
              </div>
              <button type="submit" class="btn btn-primary w-100 fw-bold mb-2" name="submit">Save</button>
              <a class="text-decoration-none fw-bold text-primary mb-2" href="setsupport.php">Having trouble?</a>
            </form>
          </div>
        </div>
      <?php include('end.php'); ?>
    </main>