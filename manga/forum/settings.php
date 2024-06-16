<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: session.php');
  exit();
}

// Establish database connection
try {
  $db = new PDO('sqlite:database.db');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Check if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get input data
  $current_password = htmlspecialchars($_POST['current_password']);
  $new_password = htmlspecialchars($_POST['new_password']);
  $confirm_password = htmlspecialchars($_POST['confirm_password']);

  // Validate input data
  $errors = [];
  if (empty($current_password)) {
    $errors[] = "Please enter your current password.";
  }
  if (empty($new_password)) {
    $errors[] = "Please enter a new password.";
  }
  if ($new_password !== $confirm_password) {
    $errors[] = "New password and confirm password do not match.";
  }

  // Check if current password is correct
  $stmt = $db->prepare("SELECT * FROM users WHERE id = :user_id AND password = :password");
  $stmt->bindParam(":user_id", $user_id);
  $stmt->bindParam(":password", $current_password);
  $stmt->execute();
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$user) {
    $errors[] = "Current password is incorrect.";
  }

  // If no errors, update password in database
  if (empty($errors)) {
    $stmt = $db->prepare("UPDATE users SET password = :new_password WHERE id = :user_id");
    $stmt->bindParam(":new_password", $new_password);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $success_message = "Password updated successfully.";
  }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <title>Settings</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include('../bootstrap.php'); ?>
    <?php include('../connection.php'); ?>
    <link rel="icon" type="image/png" href="<?php echo $web; ?>/icon/favicon.png">
	<meta property="og:url" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Forum">
    <meta property="og:description" content="This is just a simple forum.">
    <meta property="og:image" content="<?php echo $web; ?>/icon/favicon.png">
  </head>
  <body>
    <main id="swup" class="transition-main">
    <?php include('../header.php'); ?>
    <div class="container mt-3">
      <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
      <?php endif; ?>
      <?php if (!empty($errors)): ?>
          <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endforeach; ?>
      <?php endif; ?>

      <form method="post">
        <div class="form-floating mb-2">
          <input type="password" class="form-control rounded border-3 focus-ring focus-ring-dark" name="current_password" placeholder="Enter current password" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$">
          <label for="floatingPassword" class="fw-bold"><small>Enter current password</small></label>
        </div>
        <div class="form-floating mb-2">
          <input type="password" class="form-control rounded border-3 focus-ring focus-ring-dark" name="new_password" max placeholder="Type new password"length="40" pattern="^[a-zA-Z0-9_@.-]+$">
          <label for="floatingPassword" class="fw-bold"><small>Type new password</small></label>
        </div>
        <div class="form-floating mb-2">
          <input type="password" class="form-control rounded border-3 focus-ring focus-ring-dark" name="confirm_password" placeholder="Confirm new password" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$">
          <label for="floatingPassword" class="fw-bold"><small>Confirm new password</small></label>
        </div>
        <div class="btn-group gap-2 w-100">
          <button type="submit" class="btn btn-secondary fw-bold w-50 rounded" name="submit">Save</button>
          <a href="index.php" class="btn btn-secondary fw-bold w-50 rounded">Back</a>
        </div> 
      </form>
    </div>
    </main>
  </body>
</html>
