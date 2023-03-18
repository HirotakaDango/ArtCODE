<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: session.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <script src="script.js"></script>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  </head>
  <body>
    <?php include('header.php'); ?>
        <?php 
        if(isset($_GET['by'])){
            $sort = $_GET['by'];
 
            switch ($sort) {
                case 'newest':
                include "global_desc.php";
                break;
                case 'oldest':
                include "global_asc.php";
                break;
            }
        }
        else{
            include "global_desc.php";
        }
        
        ?>
    <style>
      @media (min-width: 768px) {
        .width-btn {
          width: 200px;
        }
      }
      
      @media (max-width: 767px) {
        .width-btn {
          width: 100px;
        } 
      } 
    </style>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>