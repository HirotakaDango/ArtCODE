<?php
require_once('auth.php');
$db = new SQLite3('../../database.sqlite');
$email = $_SESSION['email'];

$db->exec("CREATE TABLE IF NOT EXISTS favorites_music (id INTEGER PRIMARY KEY AUTOINCREMENT, music_id INTEGER, email TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS music (id INTEGER PRIMARY KEY AUTOINCREMENT, file TEXT, email TEXT, cover TEXT, album TEXT, title TEXT, description TEXT, lyrics TEXT)");
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArtCODE - Music</title>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="dropdown mt-3">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=newest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?by=oldest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?by=album&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'album') echo 'active'; ?>">album</a></li>
        <li><a href="?by=asc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'asc') echo 'active'; ?>">ascending</a></li>
        <li><a href="?by=desc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'desc') echo 'active'; ?>">descending</a></li>
      </ul> 
    </div> 
        <?php 
        if(isset($_GET['by'])){
          $sort = $_GET['by'];
 
          switch ($sort) {
            case 'newest':
            include "index_desc.php";
            break;
            case 'oldest':
            include "index_asc.php";
            break;
            case 'album':
            include "index_album.php";
            break;
            case 'desc':
            include "index_order_desc.php";
            break;
            case 'asc':
            include "index_order_asc.php";
            break;
          }
        }
        else {
          include "index_desc.php";
        }
        
        ?>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
