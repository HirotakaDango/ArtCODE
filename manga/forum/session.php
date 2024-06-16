<?php
session_start();
$db = new PDO('sqlite:database.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT NOT NULL, password TEXT NOT NULL)");

if (isset($_POST['login'])) {
  $username = htmlspecialchars(trim($_POST['username']));
  $password = htmlspecialchars($_POST['password']);

  if (empty($username) || empty($password)) {
    echo 'Please enter username and password';
    exit;
  }

  $query = "SELECT * FROM users WHERE username=:username AND password=:password";
  $stmt = $db->prepare($query);
  $stmt->execute(array(':username' => $username, ':password' => $password));
  $user = $stmt->fetch();

  if ($user) {
    $_SESSION['user_id'] = $user['id'];
    setcookie('user_id', $user['id'], time() + (365 * 24 * 60 * 60), '/');
    header('Location: index.php');
    exit;
  } else {
    echo 'Invalid username or password';
    exit;
  }
} elseif (isset($_POST['register'])) {
  $username = htmlspecialchars(trim($_POST['username']));
  $password = htmlspecialchars($_POST['password']);

  if (empty($username) || empty($password)) {
    echo 'Please enter username and password';
    exit;
  }

  $query = "SELECT * FROM users WHERE username=:username";
  $stmt = $db->prepare($query);
  $stmt->execute(array(':username' => $username));
  $user = $stmt->fetch();

  if ($user) {
    echo 'Username already taken';
    exit;
  }

  $query = "INSERT INTO users (username, password) VALUES (:username, :password)";
  $stmt = $db->prepare($query);
  $stmt->execute(array(':username' => $username, ':password' => $password));

  $_SESSION['user_id'] = $db->lastInsertId();
  setcookie('user_id', $_SESSION['user_id'], time() + (365 * 24 * 60 * 60), '/');
  header('Location: index.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <title>Login/Register</title>
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
    <div class="container">
      <div class="modal modal-sheet d-block p-4 mt-5 py-md-5" tabindex="-1" role="dialog" id="modalSignin">
        <div class="modal-dialog" role="document">
          <div class="modal-content rounded-4 border-0">
            <div class="modal-body p-md-5 pt-0">
              <h1 class="fw-bold mb-0 fs-2 text-center py-4 mb-4">Login and Register</h1>
              <form class="" method="post">
                <div class="form-floating mb-3">
                  <input type="text" name="username" class="form-control rounded-3" id="floatingInput" placeholder="name@example.com">
                  <label for="floatingInput">Username</label>
                </div>
                <div class="form-floating mb-3">
                  <input type="password" name="password" class="form-control rounded-3" id="floatingPassword" placeholder="Password">
                  <label for="floatingPassword">Password</label>
                </div>
                <div class="btn-group w-100 gap-3">
                  <button class="btn btn-primary fw-bold rounded w-50" type="submit" name="login">Login</button>
                  <button class="btn btn-primary fw-bold rounded w-50" type="submit" name="register">Register</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>