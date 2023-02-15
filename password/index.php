<?php
session_start();

$password = 'admin';

if (isset($_GET['p']) && $_GET['p'] === $password) {
    $_SESSION['authenticated'] = true;
}

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    echo '
        <html>
        <head>
        <link rel="icon" type="image/png" href="icon.png">
        <meta charset="UTF-8">
        <link rel="manifest" href="manifest.json">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 100px;
        }
        input[type="password"] {
            padding: 10px;
            font-size: 18px;
            margin-bottom: 20px;
        }
        input[type="submit"] {
            padding: 10px 20px;
            font-size: 18px;
            background-color: lightblue;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        </style>
        </head>
        <body>
            <center><h1 style="color: dark; margin-top: 50px; font-family: sans-serif;">MY IDE</h1></center>
            <form action="' . $_SERVER['PHP_SELF'] . '" method="get">
                <input type="password" name="p" placeholder="Enter password">
                <input type="submit" value="Submit">
            </form>
        </body>
        </html>
    ';
    exit;
}

include 'management.php';
?>
<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
      navigator.serviceWorker.register('sw.js').then(function(registration) {
        console.log('ServiceWorker registration successful with scope: ', registration.scope);
      }, function(err) {
        console.log('ServiceWorker registration failed: ', err);
      });
    });
  }
</script>
