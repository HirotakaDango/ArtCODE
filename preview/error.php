<?php
// Connect to the database using PDO
$db = new PDO('sqlite:database.sqlite');
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include('../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="../style.css">
    <title>Error Page</title>
  </head>
  <body>
    <?php include('header_preview.php'); ?>
    <div class="container fw-bold mt-2">
      <div class="alert alert-danger text-center">
        <h2><i class="bi bi-exclamation-triangle-fill"></i> Error</h2>
        <p>Oops! Something went wrong.</p>
        <p>User probably delete this media or there's something wrong with the media.</p>
        <a href="javascript:history.back()" class="btn btn-primary fw-bold"><i class="bi bi-arrow-left" style="-webkit-text-stroke: 1px;"></i> Go Back</a>
      </div>
    </div>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>
