<?php
// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$page = $page < 1 ? 1 : $page;

// Set the limit of images per page
$limit = 50;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Connect to the SQLite database
$db = new SQLite3('../../database.sqlite');

// Determine if the user is viewing their favorited images or all images
if (isset($_GET['by']) && $_GET['by'] === 'liked') {
  // User is viewing favorited images
  $stmtTotal = $db->prepare("
    SELECT COUNT(images.id) as total
    FROM images
    LEFT JOIN favorites ON images.id = favorites.image_id
    WHERE favorites.email = :email
  ");
  $stmtTotal->bindValue(':email', $email, SQLITE3_TEXT);
  $resultTotal = $stmtTotal->execute();
  $rowTotal = $resultTotal->fetchArray(SQLITE3_ASSOC);
  $totalImages = $rowTotal['total'];
} else {
  // User is viewing all images
  $totalImages = $db->querySingle("SELECT COUNT(*) FROM images");
}

// Calculate the total number of pages
$totalPages = ceil($totalImages / $limit);

// Ensure the page number is within the valid range
$page = min(max($page, 1), $totalPages);

// Calculate the offset again after ensuring valid page number
$offset = ($page - 1) * $limit;
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gallerium</title>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
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
      <a href="?by=newest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="btn btn-sm fw-bold rounded-pill me-2 btn-outline-dark <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a>
      <a href="?by=oldest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="btn btn-sm fw-bold rounded-pill me-2 btn-outline-dark <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a>
      <a href="?by=popular&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="btn btn-sm fw-bold rounded-pill me-2 btn-outline-dark <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">popular</a>
      <a href="?by=view&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="btn btn-sm fw-bold rounded-pill me-2 btn-outline-dark <?php if(isset($_GET['by']) && $_GET['by'] == 'view') echo 'active'; ?>">most viewed</a>
      <a href="?by=least&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="btn btn-sm fw-bold rounded-pill me-2 btn-outline-dark <?php if(isset($_GET['by']) && $_GET['by'] == 'least') echo 'active'; ?>">least viewed</a>
      <a href="?by=order_asc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="btn btn-sm fw-bold rounded-pill me-2 btn-outline-dark <?php if(isset($_GET['by']) && $_GET['by'] == 'order_asc') echo 'active'; ?>">from A to Z</a>
      <a href="?by=order_desc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="btn btn-sm fw-bold rounded-pill me-2 btn-outline-dark <?php if(isset($_GET['by']) && $_GET['by'] == 'order_desc') echo 'active'; ?>">from Z to A</a>
      <a href="?by=daily&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="btn btn-sm fw-bold rounded-pill me-2 btn-outline-dark <?php if(isset($_GET['by']) && $_GET['by'] == 'daily') echo 'active'; ?>">daily</a>
      <a href="?by=week&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="btn btn-sm fw-bold rounded-pill me-2 btn-outline-dark <?php if(isset($_GET['by']) && $_GET['by'] == 'week') echo 'active'; ?>">week</a>
      <a href="?by=month&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="btn btn-sm fw-bold rounded-pill me-2 btn-outline-dark <?php if(isset($_GET['by']) && $_GET['by'] == 'month') echo 'active'; ?>">month</a>
      <a href="?by=year&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="btn btn-sm fw-bold rounded-pill me-2 btn-outline-dark <?php if(isset($_GET['by']) && $_GET['by'] == 'year') echo 'active'; ?>">year</a>
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
        case 'popular':
        include "index_pop.php";
        break;
        case 'view':
        include "index_view.php";
        break;
        case 'least':
        include "index_least.php";
        break;
        case 'top':
        include "top.php";
        break;
        case 'order_asc':
        include "index_order_asc.php";
        break;
        case 'order_desc':
        include "index_order_desc.php";
        break;
        case 'daily':
        include "index_daily.php";
        break;
        case 'week':
        include "index_week.php";
        break;
        case 'month':
        include "index_month.php";
        break;
        case 'year':
        include "index_year.php";
        break;
      }
    }
    else {
      include "index_desc.php";
    }
    
    ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'newest'; ?>&page=1">
          <i class="bi text-stroke bi-chevron-double-left"></i>
        </a>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'newest'; ?>&page=<?php echo $page - 1; ?>">
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
          $byParam = isset($_GET['by']) ? $_GET['by'] : 'newest';
          echo '<a class="btn btn-sm btn-primary fw-bold" href="?by=' . $byParam . '&page=' . $i . '">' . $i . '</a>';
        }
      }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'newest'; ?>&page=<?php echo $page + 1; ?>">
          <i class="bi text-stroke bi-chevron-right"></i>
        </a>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'newest'; ?>&page=<?php echo $totalPages; ?>">
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