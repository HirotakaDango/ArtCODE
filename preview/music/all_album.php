<?php
$db = new SQLite3('../../database.sqlite');

// Pagination
$page = isset($_GET['page']) ? $_GET['page'] : 1;
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Albums</title>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('../header_preview.php'); ?>
    <div class="container-fluid d-flex">
      <!-- only visible for grid mode -->
      <div class="dropdown mt-3 me-auto <?php echo ((isset($_GET['by']) && ($_GET['by'] === 'newest_lists' || $_GET['by'] === 'oldest_lists' || $_GET['by'] === 'popular_lists' || $_GET['by'] === 'desc_lists' || $_GET['by'] === 'asc_lists')) || (strpos($_SERVER['REQUEST_URI'], 'all_album_desc_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'all_album_asc_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'all_album_pop_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'all_album_order_asc_lists.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'all_album_order_desc_lists.php') !== false)) ? 'd-none' : ''; ?>">
        <button class="btn btn-sm fw-bold rounded-pill mb-2 btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-images"></i> sort by
        </button>
        <ul class="dropdown-menu">
          <li><a href="?by=newest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
          <li><a href="?by=oldest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
          <li><a href="?by=popular&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">popular</a></li>
          <li><a href="?by=asc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'asc') echo 'active'; ?>">ascending</a></li>
          <li><a href="?by=desc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'desc') echo 'active'; ?>">descending</a></li>
        </ul> 
      </div>
      <!-- only visible for lists mode -->
      <div class="dropdown mt-3 me-auto <?php echo ((isset($_GET['by']) && ($_GET['by'] === 'newest' || $_GET['by'] === 'oldest' || $_GET['by'] === 'popular' || $_GET['by'] === 'desc' || $_GET['by'] === 'asc')) || (strpos($_SERVER['REQUEST_URI'], 'all_album_desc_grid.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'all_album_asc_grid.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'all_album_pop_grid.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'all_album_order_asc_grid.php') !== false) || (strpos($_SERVER['REQUEST_URI'], 'all_album_order_desc_grid.php') !== false)) ? 'd-none' : ''; ?>">
        <button class="btn btn-sm fw-bold rounded-pill mb-2 btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-images"></i> sort by
        </button>
        <ul class="dropdown-menu">
          <li><a href="?by=newest_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest_lists') echo 'active'; ?>">newest</a></li>
          <li><a href="?by=oldest_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest_lists') echo 'active'; ?>">oldest</a></li>
          <li><a href="?by=popular_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular_lists') echo 'active'; ?>">popular</a></li>
          <li><a href="?by=asc_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'asc_lists') echo 'active'; ?>">ascending</a></li>
          <li><a href="?by=desc_lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'desc_lists') echo 'active'; ?>">descending</a></li>
        </ul> 
      </div>
      <div class="btn-group mt-2 pt-1">
        <a class="btn border-0 link-body-emphasis" href="?mode=grid&by=<?php echo isset($_GET['by']) ? str_replace('_lists', '', $_GET['by']) : 'desc'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>"><i class="bi bi-grid-fill"></i></a>
        <a class="btn border-0 link-body-emphasis" href="?mode=lists&by=<?php echo isset($_GET['by']) ? (strpos($_GET['by'], '_lists') === false ? $_GET['by'] . '_lists' : $_GET['by']) : 'desc'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>"><i class="bi bi-view-list"></i></a>
      </div>
    </div>
    <div class="container-fluid">
        <?php 
        if(isset($_GET['by'])){
          $sort = $_GET['by'];
 
          switch ($sort) {
            // grid layout
            case 'newest':
            include "all_album_desc_grid.php";
            break;
            case 'oldest':
            include "all_album_asc_grid.php";
            break;
            case 'popular':
            include "all_album_pop_grid.php";
            break;
            case 'asc':
            include "all_album_order_asc_grid.php";
            break;
            case 'desc':
            include "all_album_order_desc_grid.php";
            break;
            // vertical lists layout
            case 'newest_lists':
            include "all_album_desc_lists.php";
            break;
            case 'oldest_lists':
            include "all_album_asc_lists.php";
            break;
            case 'popular_lists':
            include "all_album_pop_lists.php";
            break;
            case 'asc_lists':
            include "all_album_order_asc_lists.php";
            break;
            case 'desc_lists':
            include "all_album_order_desc_lists.php";
            break;
          }
        }
        else {
          include "all_album_desc_grid.php";
        }
        
        ?>
    </div>

    <!-- Pagination -->
    <div class="container mt-3">
      <div class="pagination d-flex gap-1 justify-content-center mt-3">
        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'grid'; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
          <a class="btn btn-sm btn-primary fw-bold" href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'grid'; ?>&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
        <?php endif; ?>

        <?php
        // Calculate the range of page numbers to display
        $startPage = max($page - 2, 1);
        $endPage = min($page + 2, $totalPages);

        // Display page numbers within the range
        for ($i = $startPage; $i <= $endPage; $i++) {
          if ($i === $page) {
            echo '<span class="btn btn-sm btn-primary active fw-bold">' . $i . '</span>';
          } else {
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?mode=' . (isset($_GET['mode']) ? $_GET['mode'] : 'grid') . '&by=' . (isset($_GET['by']) ? $_GET['by'] : 'desc') . '&page=' . $i . '">' . $i . '</a>';
          }
        }
        ?>

        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'grid'; ?>&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
          <a class="btn btn-sm btn-primary fw-bold" href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'grid'; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
        <?php endif; ?>
      </div>
    </div>
    <style>
      .text-shadow {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);
      }
    </style>
    <div class="mt-5"></div>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
