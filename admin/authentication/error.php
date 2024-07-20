<?php
// admin/authentication/error.php
session_start();
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : 'Unknown error';
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied</title>
    <?php include('../../bootstrapcss.php'); ?>
    <style>
      body {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background-color: #121212;
        color: #e0e0e0;
      }
      .container {
        text-align: center;
        padding: 20px;
        background: #1e1e1e;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
      }
      h1 {
        color: #f5a623;
      }
      a {
        color: #f5a623;
        text-decoration: none;
      }
      a:hover {
        text-decoration: underline;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <h1>Access Denied!</h1>
      <p><?php echo htmlspecialchars($error_message); ?></p>
      <a href="/admin/authentication/index.php">Go to Login</a>
    </div>
  </body>
</html>
