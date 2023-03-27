<?php
  session_start();

  // Connect to the SQLite database
  $db = new SQLite3('database.sqlite');

  // Create the users table if it doesn't exist
  $db->exec("CREATE TABLE IF NOT EXISTS users (username TEXT, password TEXT)");

  // Check if the user is logging in or registering
  if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the email and password fields are not empty
    if (empty($username) || empty($password)) {
      echo "Please enter both email and password.";
    } else {
      // Check if the user exists in the database
      $stmt = $db->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
      $stmt->bindValue(':username', $username);
      $stmt->bindValue(':password', $password);
      $result = $stmt->execute();
      $user = $result->fetchArray();
      if ($user) {
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit;
      } else {
        echo "Incorrect username or password.";
      }
    }
  } elseif (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
      echo "Username and password are required.";
      exit;
    } else {
      // Check if the username is already taken
      $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
      $stmt->bindValue(':username', $username);
      $result = $stmt->execute();
      $user = $result->fetchArray();
      if ($user) {
        echo "Username already taken.";
      } else {
        // Add the new user to the database
        $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':password', $password);
        $stmt->execute();
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit;
      }
    }
  }
?>

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
    <br>
    <div class="container">
      <div id="login-form">
        <br>
        <center><h1><i class="bi bi-person-circle"></i></h1></center>
        <center><h2 class="fw-bold">WELCOME BACK!</h2></center>
        <center><h2 class="mb-5 fw-bold">LOGIN TO CONTINUE</h2></center>
        <div class="modal-body p-4 pt-0">
          <form class="" method="post">
            <div class="form-floating mb-3">
              <input name="username" type="text" class="form-control rounded-3" id="floatingInput" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" placeholder="name@example.com">
              <label for="floatingInput">Username</label>
            </div>
            <div class="form-floating mb-3">
              <input name="password" type="password" class="form-control rounded-3" id="floatingPassword" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" placeholder="Password">
              <label for="floatingPassword">Password</label>
            </div>
            <button name="login" class="w-100 fw-bold mb-2 btn btn-lg rounded-3 btn-primary" type="submit">Login</button>
            <p>Don't have an account? <a href="#" onclick="showRegisterForm()">Register</a></p>
          </form>
        </div>
      </div>
      <div id="register-form" style="display:none;">
        <br>
        <center><h1><i class="bi bi-person-circle"></i></h1></center>
        <center><h2 class="fw-bold">HELLO, NEW USER?</h2></center>
        <center><h2 class="mb-5 fw-bold">REGISTER TO CONTINUE</h2></center>
        <div class="modal-body p-4 pt-0">
          <form class="" method="post">
            <div class="form-floating mb-3">
              <input name="username" type="text" class="form-control rounded-3" id="floatingInput" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" placeholder="name@example.com">
              <label for="floatingInput">Username</label>
            </div>
            <div class="form-floating mb-3">
              <input name="password" type="password" class="form-control rounded-3" id="floatingPassword" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" placeholder="Password">
              <label for="floatingPassword">Password</label>
            </div>
            <button name="register" class="w-100 fw-bold mb-2 btn btn-lg rounded-3 btn-primary" type="submit">Register</button>
            <p>Already have an account? <a href="#" onclick="showLoginForm()">Login</a></p>
          </form>
        </div>
      </div>
    </div>
    <script>
      function showLoginForm() {
        document.getElementById('login-form').style.display = 'block';
        document.getElementById('register-form').style.display = 'none';
      }
      function showRegisterForm() {
        document.getElementById('login-form').style.display = 'none';
        document.getElementById('register-form').style.display = 'block';
      }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>