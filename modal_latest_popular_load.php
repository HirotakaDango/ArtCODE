<?php
require_once('auth.php');

// Connect to the database using PDO
$db = new PDO('sqlite:database.sqlite');
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Latest and Popular</title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Include jQuery library -->
    <p class="fw-bold ms-2 mt-2">Latest Images</p>
    <?php
      include('latest.php');
    ?>
    <p class="fw-bold ms-2 mt-5">Popular Images</p>
    <?php
      include('most_popular.php');
    ?>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>