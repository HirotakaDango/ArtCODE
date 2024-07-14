<?php
require_once('../auth.php');
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include('../bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
      <h5>Choose user you want to message with.</h5>
    </div>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>