<?php
require_once('auth.php');

// Database connection using PDO
try {
  $db = new PDO('sqlite:../../database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}

$email = $_SESSION['email'];

// Specify the tag you want to filter by
$tag = isset($_GET['tag']) ? $_GET['tag'] : '';
?>

<!DOCTYPE html>
<html data-bs-theme="dark">
  <head>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Genre: <?php echo $tag; ?></title>
    <?php include ('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include ('header.php'); ?>
    <div class="dropdown mt-3">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=newest&tag=<?php echo urlencode($tag); ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?by=oldest&tag=<?php echo urlencode($tag); ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?by=popular&tag=<?php echo urlencode($tag); ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">popular</a></li>
        <li><a href="?by=view&tag=<?php echo urlencode($tag); ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'view') echo 'active'; ?>">most viewed</a></li>
        <li><a href="?by=least&tag=<?php echo urlencode($tag); ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'least') echo 'active'; ?>">least viewed</a></li>
      </ul> 
    </div> 
        <?php 
        if(isset($_GET['by'])){
          $sort = $_GET['by'];
 
          switch ($sort) {
            case 'newest':
            include "genre_desc.php";
            break;
            case 'oldest':
            include "genre_asc.php";
            break;
            case 'popular':
            include "genre_pop.php";
            break;
            case 'view':
            include "genre_view.php";
            break;
            case 'least':
            include "genre_least.php";
            break;
          }
        }
        else {
          include "genre_desc.php";
        }
        
        ?>
    <?php include ('../../bootstrapjs.php'); ?>
  </body>
</html>
