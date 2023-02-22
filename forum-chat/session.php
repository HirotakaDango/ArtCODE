<?php
session_start();
$db_file = '../message';

try{ $pdo = new PDO("sqlite:" . $db_file); }
catch(PDOException $e){ die("Could not connect to database: " . $e->getMessage()); }

if (isset($_SESSION['user_id'])) {
  header('Location: index.php');
  exit();
}
else {
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['action'] == 'login') {
      $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND password = ?");
      $stmt->execute([$_POST['username'], $_POST['password']]);
      $user_id = $stmt->fetchColumn();
      if ($user_id) {
        $_SESSION['user_id'] = $user_id;
        header('Location: index.php');
        exit();
      } else {
        $error = 'Invalid username or password';
      }
    } else if ($_POST['action'] == 'register') {
      $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
      $stmt->execute([$_POST['username']]);
      $user_id = $stmt->fetchColumn();
      if ($user_id) {
        $error = 'Username already exists';
      } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$_POST['username'], $_POST['password']]);
        $user_id = $pdo->lastInsertId();
        $_SESSION['user_id'] = $user_id;
        header('Location: index.php');
        exit();
      }
    }
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
  </head>
  <body>
    <div class="container mt-5 text-center">
    <div class="card">
      <?php if (isset($error)) { echo $error; } ?>

      <?php if (!isset($_GET['action']) || $_GET['action'] === 'login') : ?>
        <h1 class="mt-2">Login</h1>
        <form class="container" method="post">
          <div class="mb-3">
            <input type="text" name="username" class="form-control" id="username" placeholder="Username">
          </div>
          <div class="mb-3">
            <input type="password" name="password" class="form-control" id="password" placeholder="Password">
          </div>
          <input type="hidden" name="action" value="login">
          <button type="submit" class="btn btn-primary mb-3">Login</button>
          <p>Don't have an account? <a href="?action=register">Register here</a>.</p>
        </form>
      <?php endif; ?>

      <?php if (isset($_GET['action']) && $_GET['action'] === 'register') : ?>
        <h1 class="mt-2">Register</h1>
        <form class="container" method="post">
          <div class="mb-3">
            <input type="text" name="username" class="form-control" id="username" placeholder="Username">
          </div>
          <div class="mb-3">
            <input type="password" name="password" class="form-control" id="password" placeholder="Password">
          </div>
          <input type="hidden" name="action" value="register">
          <button type="submit" class="btn btn-primary mb-3">Register</button>
          <p>Already have an account? <a href="?action=login">Login here</a>.</p>
        </form>
      <?php endif; ?>
    </div>
    </div>
  </body>
</html>
<?php } ?>
