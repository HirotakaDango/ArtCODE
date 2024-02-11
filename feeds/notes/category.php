<?php
require_once('auth.php');

$email = $_SESSION['email'];

$db = new PDO('sqlite:../../database.sqlite');

// get the tag from the URL parameter
$categoryBy = isset($_GET['q']) ? $_GET['q'] : '';
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <title>Category: <?php echo $categoryBy; ?></title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="dropdown mt-3">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=newest&q=<?php echo $categoryBy; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?by=oldest&q=<?php echo $categoryBy; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?by=date&q=<?php echo $categoryBy; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'date') echo 'active'; ?>">date</a></li>
        <li><a href="?by=starred&q=<?php echo $categoryBy; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'starred') echo 'active'; ?>">starred</a></li>
      </ul> 
    </div> 
        <?php 
        if(isset($_GET['by'])){
          $sort = $_GET['by'];
 
          switch ($sort) {
            case 'newest':
            include "category_desc.php";
            break;
            case 'oldest':
            include "category_asc.php";
            break;
            case 'date':
            include "category_date.php";
            break;
            case 'starred':
            include "category_starred.php";
            break;
          }
        }
        else {
          include "category_desc.php";
        }
        
        ?>
    <div class="mt-5"></div>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>