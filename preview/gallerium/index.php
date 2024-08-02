<?php
// Connect to the SQLite database using parameterized query
$db = new SQLite3('../../database.sqlite');

// Get the total number of images
$totalImages = $db->querySingle("SELECT COUNT(*) FROM images");

// Set the limit of images per page
$limit = 25;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$page = $page < 1 ? 1 : $page;

// Calculate the total number of pages
$totalPages = ceil($totalImages / $limit);

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gallerium</title>
    <link rel="manifest" href="../../manifest.json">
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <?php include('../../bootstrapcss.php'); ?>
    <style>
      body {
        margin: 0;
        padding: 0;
        overflow-x: hidden;
      }
      .masonry-container {
        width: 100%;
        padding: 0;
        box-sizing: border-box;
      }

      .masonry-grid {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -5px;
      }

      .masonry-grid-item {
        width: calc(25% - 10px);
        margin: 0 5px 10px 5px;
        box-sizing: border-box;
      }

      @media (max-width: 1200px) {
        .masonry-grid-item {
          width: calc(33.333% - 10px);
        }
      }

      @media (max-width: 768px) {
        .masonry-grid-item {
          width: calc(50% - 10px);
        }
      }

      @media (max-width: 480px) {
        .masonry-grid-item {
          width: calc(50% - 10px);
        }
      }
    </style>
  </head>
  <body>
    <?php include('../header_preview.php'); ?>
    <div class="overflow-x-auto text-nowrap px-2 mt-3 mb-4">
      <a href="?by=day&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="btn btn-sm fw-bold rounded-pill me-2 btn-outline-dark <?php if(!isset($_GET['by']) || $_GET['by'] == 'day') echo 'active'; ?>">this day</a>
      <a href="?by=week&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="btn btn-sm fw-bold rounded-pill me-2 btn-outline-dark <?php if(isset($_GET['by']) && $_GET['by'] == 'week') echo 'active'; ?>">this week</a>
      <a href="?by=month&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="btn btn-sm fw-bold rounded-pill me-2 btn-outline-dark <?php if(isset($_GET['by']) && $_GET['by'] == 'month') echo 'active'; ?>">this month</a>
      <a href="?by=year&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="btn btn-sm fw-bold rounded-pill me-2 btn-outline-dark <?php if(isset($_GET['by']) && $_GET['by'] == 'year') echo 'active'; ?>">this year</a>
      <a href="?by=alltime&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="btn btn-sm fw-bold rounded-pill me-2 btn-outline-dark <?php if(isset($_GET['by']) && $_GET['by'] == 'alltime') echo 'active'; ?>">all time</a>
    </div>
    <?php 
    if(isset($_GET['by'])){
      $sort = $_GET['by'];
    
      switch ($sort) {
        case 'day':
          include "daily.php";
          break;
        case 'week':
          include "week.php";
          break;
        case 'month':
          include "month.php";
          break;
        case 'year':
          include "year.php";
          break;
        case 'alltime':
          include "alltime.php";
          break;
      }
    }
    else {
      include "daily.php";
    }
    ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'daily'; ?>&page=1">
          <i class="bi text-stroke bi-chevron-double-left"></i>
        </a>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'daily'; ?>&page=<?php echo $page - 1; ?>">
          <i class="bi text-stroke bi-chevron-left"></i>
        </a>
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
          // Use parentheses to properly handle the ternary operator within the concatenation
          $byParam = isset($_GET['by']) ? $_GET['by'] : 'daily';
          echo '<a class="btn btn-sm btn-primary fw-bold" href="?by=' . $byParam . '&page=' . $i . '">' . $i . '</a>';
        }
      }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'daily'; ?>&page=<?php echo $page + 1; ?>">
          <i class="bi text-stroke bi-chevron-right"></i>
        </a>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'daily'; ?>&page=<?php echo $totalPages; ?>">
          <i class="bi text-stroke bi-chevron-double-right"></i>
        </a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>
    <script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
    <script src="https://unpkg.com/imagesloaded@5/imagesloaded.pkgd.min.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var grid = document.querySelector('#masonry-grid');
        var msnry = new Masonry(grid, {
          itemSelector: '.masonry-grid-item',
          percentPosition: true,
          gutter: 0
        });

        imagesLoaded(grid).on('progress', function() {
          msnry.layout();
        });
      });
    </script>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>