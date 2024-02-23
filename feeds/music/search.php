<?php
require_once('auth.php');
$db = new SQLite3('../../database.sqlite');
$email = $_SESSION['email'];

// Pagination
$searchPage = isset($_GET['q']) ? $_GET['q'] : null;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search for <?php echo $searchPage; ?></title>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container-fluid d-flex">
      <!-- only visible for grid mode -->
      <div class="dropdown mt-3 me-auto <?php echo ((isset($_GET['by']) && ($_GET['by'] === 'newest_lists' || $_GET['by'] === 'oldest_lists' || $_GET['by'] === 'popular_lists' || $_GET['by'] === 'albumasc_lists' || $_GET['by'] === 'albumdesc_lists' || $_GET['by'] === 'asc_lists' || $_GET['by'] === 'artistasc_lists' || $_GET['by'] === 'artistdesc_lists' || $_GET['by'] === 'desc_lists')) || (strpos($_SERVER['REQUEST_URI'], 'search_desc_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'search_asc_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'search_pop_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'search_album_asc_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'search_album_desc_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'search_order_asc_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'search_order_desc_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'search_artist_asc_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'search_artist_desc_lists.php') !== false)) ? 'd-none' : ''; ?>">
        <button class="btn btn-sm fw-bold rounded-pill mb-2 btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-images"></i> sort by
        </button>
        <ul class="dropdown-menu">
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&q=<?php echo $searchPage; ?>&by=newest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&q=<?php echo $searchPage; ?>&by=oldest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&q=<?php echo $searchPage; ?>&by=popular&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">popular</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&q=<?php echo $searchPage; ?>&by=albumasc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'albumasc') echo 'active'; ?>">album ascending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&q=<?php echo $searchPage; ?>&by=albumdesc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'albumdesc') echo 'active'; ?>">album descending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&q=<?php echo $searchPage; ?>&by=artistasc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'artistasc') echo 'active'; ?>">artist ascending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&q=<?php echo $searchPage; ?>&by=artistdesc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'artistdesc') echo 'active'; ?>">artist descending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&q=<?php echo $searchPage; ?>&by=asc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'asc') echo 'active'; ?>">ascending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&q=<?php echo $searchPage; ?>&by=desc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'desc') echo 'active'; ?>">descending</a></li>
        </ul> 
      </div>
      <!-- only visible for lists mode -->
      <div class="dropdown mt-3 me-auto <?php echo ((isset($_GET['by']) && ($_GET['by'] === 'newest' || $_GET['by'] === 'oldest' || $_GET['by'] === 'popular' || $_GET['by'] === 'albumasc' || $_GET['by'] === 'albumdesc' || $_GET['by'] === 'asc' || $_GET['by'] === 'desc' || $_GET['by'] === 'artistasc' || $_GET['by'] === 'artistdesc')) || (strpos($_SERVER['REQUEST_URI'], 'search_desc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'search_asc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'search_pop.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'search_album_asc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'search_album_desc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'search_order_asc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'search_order_desc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'search_artist_asc.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'search_artist_desc.php') !== false)) ? 'd-none' : ''; ?>">
        <button class="btn btn-sm fw-bold rounded-pill mb-2 btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-images"></i> sort by
        </button>
        <ul class="dropdown-menu">
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&q=<?php echo $searchPage; ?>&by=newest_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest_lists') echo 'active'; ?>">newest</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&q=<?php echo $searchPage; ?>&by=oldest_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest_lists') echo 'active'; ?>">oldest</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&q=<?php echo $searchPage; ?>&by=popular_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular_lists') echo 'active'; ?>">popular</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&q=<?php echo $searchPage; ?>&by=albumasc_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'albumasc_lists') echo 'active'; ?>">album ascending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&q=<?php echo $searchPage; ?>&by=albumdesc_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'albumdesc_lists') echo 'active'; ?>">album descending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&q=<?php echo $searchPage; ?>&by=artistasc_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'artistasc_lists') echo 'active'; ?>">artist ascending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&q=<?php echo $searchPage; ?>&by=artistdesc_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'artistdesc_lists') echo 'active'; ?>">artist descending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&q=<?php echo $searchPage; ?>&by=asc_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'asc_lists') echo 'active'; ?>">ascending</a></li>
          <li><a href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&q=<?php echo $searchPage; ?>&by=desc_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'desc_lists') echo 'active'; ?>">descending</a></li>
        </ul> 
      </div>
      <div class="btn-group mt-2 pt-1">
        <a class="btn border-0 link-body-emphasis" href="?mode=grid&by=<?php echo isset($_GET['by']) ? str_replace('_lists', '', $_GET['by']) : 'newest'; ?>&q=<?php echo $searchPage; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>"><i class="bi bi-grid-fill"></i></a>
        <a class="btn border-0 link-body-emphasis" href="?mode=lists&by=<?php echo isset($_GET['by']) ? (strpos($_GET['by'], '_lists') === false ? $_GET['by'] . '_lists' : $_GET['by']) : 'desc'; ?>&q=<?php echo $searchPage; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>"><i class="bi bi-view-list"></i></a>
      </div>
    </div>
        <?php 
        if(isset($_GET['by'])){
          $sort = $_GET['by'];
 
          switch ($sort) {
            // grid layout
            case 'newest':
            include "search_desc.php";
            break;
            case 'oldest':
            include "search_asc.php";
            break;
            case 'popular':
            include "search_pop.php";
            break;
            case 'albumasc':
            include "search_album_asc.php";
            break;
            case 'albumdesc':
            include "search_album_desc.php";
            break;
            case 'asc':
            include "search_order_asc.php";
            break;
            case 'desc':
            include "search_order_desc.php";
            break;
            case 'artistasc':
            include "search_artist_asc.php";
            break;
            case 'artistdesc':
            include "search_artist_desc.php";
            break;
            // vertical lists layout
            case 'newest_lists':
            include "search_desc_lists.php";
            break;
            case 'oldest_lists':
            include "search_asc_lists.php";
            break;
            case 'popular_lists':
            include "search_pop_lists.php";
            break;
            case 'albumasc_lists':
            include "search_album_asc_lists.php";
            break;
            case 'albumdesc_lists':
            include "search_album_desc_lists.php";
            break;
            case 'asc_lists':
            include "search_order_asc_lists.php";
            break;
            case 'desc_lists':
            include "search_order_desc_lists.php";
            break;
            case 'artistasc_lists':
            include "search_artist_asc_lists.php";
            break;
            case 'artistdesc_lists':
            include "search_artist_desc_lists.php";
            break;
          }
        }
        else {
          include "search_desc.php";
        }
        
        ?>
    <style>
      .text-shadow {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);
      }
    </style>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
