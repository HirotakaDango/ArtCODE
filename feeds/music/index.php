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
    <div class="container-fluid d-flex">
      <!-- only visible for grid mode -->
      <div class="dropdown mt-3 me-auto <?php echo ((isset($_GET['by']) && ($_GET['by'] === 'newest_lists' || $_GET['by'] === 'oldest_lists' || $_GET['by'] === 'popular_lists' || $_GET['by'] === 'albumasc_lists' || $_GET['by'] === 'albumdesc_lists' || $_GET['by'] === 'asc_lists' || $_GET['by'] === 'artistasc_lists' || $_GET['by'] === 'artistdesc_lists' || $_GET['by'] === 'desc_lists')) || (strpos($_SERVER['REQUEST_URI'], 'index_desc_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'index_asc_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'index_pop_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'index_album_asc_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'index_album_desc_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'index_order_asc_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'index_order_desc_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'index_artist_asc_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'index_artist_desc_lists.php') !== false)) ? 'd-none' : ''; ?>">
        <button class="btn btn-sm fw-bold rounded-pill mb-2 btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-images"></i> sort by
        </button>
        <ul class="dropdown-menu">
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=newest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=oldest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=popular&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">popular</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=albumasc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'albumasc') echo 'active'; ?>">album ascending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=albumdesc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'albumdesc') echo 'active'; ?>">album descending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=artistasc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'artistasc') echo 'active'; ?>">artist ascending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=artistdesc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'artistdesc') echo 'active'; ?>">artist descending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=asc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'asc') echo 'active'; ?>">ascending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=desc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'desc') echo 'active'; ?>">descending</a></li>
        </ul> 
      </div>
      <!-- only visible for lists mode -->
      <div class="dropdown mt-3 me-auto <?php echo ((isset($_GET['by']) && ($_GET['by'] === 'newest' || $_GET['by'] === 'oldest' || $_GET['by'] === 'popular' || $_GET['by'] === 'albumasc' || $_GET['by'] === 'albumdesc' || $_GET['by'] === 'asc' || $_GET['by'] === 'desc' || $_GET['by'] === 'artistasc' || $_GET['by'] === 'artistdesc')) || (strpos($_SERVER['REQUEST_URI'], 'index_desc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'index_asc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'index_pop.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'index_album_asc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'index_album_desc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'index_order_asc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'index_order_desc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'index_artist_asc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'index_artist_desc.php') !== false)) ? 'd-none' : ''; ?>">
        <button class="btn btn-sm fw-bold rounded-pill mb-2 btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-images"></i> sort by
        </button>
        <ul class="dropdown-menu">
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=newest_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest_lists') echo 'active'; ?>">newest</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=oldest_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest_lists') echo 'active'; ?>">oldest</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=popular_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular_lists') echo 'active'; ?>">popular</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=albumasc_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'albumasc_lists') echo 'active'; ?>">album ascending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=albumdesc_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'albumdesc_lists') echo 'active'; ?>">album descending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=artistasc_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'artistasc_lists') echo 'active'; ?>">artist ascending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=artistdesc_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'artistdesc_lists') echo 'active'; ?>">artist descending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=asc_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'asc_lists') echo 'active'; ?>">ascending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=desc_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'desc_lists') echo 'active'; ?>">descending</a></li>
        </ul> 
      </div>
      <div class="btn-group mt-2 pt-1">
        <a class="btn border-0 link-body-emphasis" href="?mode=grid&by=<?php echo isset($_GET['by']) ? str_replace('_lists', '', $_GET['by']) : 'newest'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>"><i class="bi bi-grid-fill"></i></a>
        <a class="btn border-0 link-body-emphasis" href="?mode=lists&by=<?php echo isset($_GET['by']) ? (strpos($_GET['by'], '_lists') === false ? $_GET['by'] . '_lists' : $_GET['by']) : 'desc'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>"><i class="bi bi-view-list"></i></a>
      </div>
    </div>
        <?php 
        if(isset($_GET['by'])){
          $sort = $_GET['by'];
 
          switch ($sort) {
            // grid layout
            case 'newest':
            include "index_desc.php";
            break;
            case 'oldest':
            include "index_asc.php";
            break;
            case 'popular':
            include "index_pop.php";
            break;
            case 'albumasc':
            include "index_album_asc.php";
            break;
            case 'albumdesc':
            include "index_album_desc.php";
            break;
            case 'asc':
            include "index_order_asc.php";
            break;
            case 'desc':
            include "index_order_desc.php";
            break;
            case 'artistasc':
            include "index_artist_asc.php";
            break;
            case 'artistdesc':
            include "index_artist_desc.php";
            break;
            // vertical lists layout
            case 'newest_lists':
            include "index_desc_lists.php";
            break;
            case 'oldest_lists':
            include "index_asc_lists.php";
            break;
            case 'popular_lists':
            include "index_pop_lists.php";
            break;
            case 'albumasc_lists':
            include "index_album_asc_lists.php";
            break;
            case 'albumdesc_lists':
            include "index_album_desc_lists.php";
            break;
            case 'asc_lists':
            include "index_order_asc_lists.php";
            break;
            case 'desc_lists':
            include "index_order_desc_lists.php";
            break;
            case 'artistasc_lists':
            include "index_artist_asc_lists.php";
            break;
            case 'artistdesc_lists':
            include "index_artist_desc_lists.php";
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
