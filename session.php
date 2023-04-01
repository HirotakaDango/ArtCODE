<?php
// Connect to the SQLite database using parameterized query
$db = new SQLite3('database.sqlite'); 
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT, password TEXT, artist TEXT, pic TEXT, desc TEXT, bgpic TEXT, token TEXT, twitter TEXT, pixiv TEXT, other TEXT)");
$stmt->execute();
$stmt = $db->prepare('CREATE TABLE IF NOT EXISTS news (id INTEGER PRIMARY KEY, title TEXT, description TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, ver TEXT, verlink TEXT)');
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS images (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, username TEXT, tags TEXT, title TEXT, imgdesc TEXT, link TEXT, date DATETIME)");
$stmt->execute();
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
  </head>
  <body>
    <?php include('landing_page.php');?>
    <?php include('session_user.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>
