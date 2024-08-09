<?php
// admin/music_section/favorite.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();

// Retrieve the email from the session
$email = $_SESSION['admin']['email'];

// Connect to the SQLite database
$db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/database.sqlite');

// Pagination
$page = isset($_GET['page']) ? $_GET['page'] : 1;
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <title>Favorites</title>
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>
  <body>
    <div class="container-fluid px-0">
      <div class="row g-0">
        <div class="col-auto">
          <?php include('../admin_header.php'); ?>
        </div>
        <div class="col overflow-auto vh-100">
          <?php include('../navbar.php'); ?>
          <div>
            <div class="container-fluid mt-2">
              <div class="container-fluid d-flex">
                <div class="btn-group ms-auto">
                  <a class="btn border-0 link-body-emphasis" href="?mode=grid&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>"><i class="bi bi-grid-fill"></i></a>
                  <a class="btn border-0 link-body-emphasis" href="?mode=lists&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>"><i class="bi bi-view-list"></i></a>
                </div>
              </div>
                <?php 
                if(isset($_GET['mode'])){
                  $sort = $_GET['mode'];
         
                  switch ($sort) {
                    // grid layout
                    case 'grid':
                    include "favorite_grid.php";
                    break;
                    case 'lists':
                    include "favorite_lists.php";
                    break;
                  }
                }
                else {
                  include "favorite_grid.php";
                }
                
                ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <style>
      .text-shadow {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);
      }
    </style>

    <!-- Pagination -->
    <div class="container mt-3">
      <div class="pagination d-flex gap-1 justify-content-center mt-3">
        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
          <a class="btn btn-sm btn-primary fw-bold" href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?mode=' . (isset($_GET['mode']) ? $_GET['mode'] : 'grid') . '&page=' . $i . '">' . $i . '</a>';
          }
        }
        ?>

        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
          <a class="btn btn-sm btn-primary fw-bold" href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
        <?php endif; ?>
      </div>
    </div>
    <div class="mt-5"></div>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
