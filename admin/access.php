<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
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
            <center><h1 style="color: dark; margin-top: 50px;"><i class="bi bi-person-fill-gear"></i> ADMIN</h1></center>
            <form action="' . $_SERVER['PHP_SELF'] . '" method="get">
                <input type="password" name="p" placeholder="Enter password">
                <input type="submit" value="Submit">
            </form>
        </body>
        </html>
    ';
    exit;
}

include '../admin/index.php';
?>
