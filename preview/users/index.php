<?php
// Establish SQLite connection
$db = new SQLite3('../../database.sqlite');
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>All Users</title>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('../header_preview.php'); ?>
    <?php include('../columns_header.php'); ?>
    <div class="dropdown">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=ascending&category=<?php echo isset($_GET['category']) ? $_GET['category'] : 'A'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'ascending') echo 'active'; ?>">ascending</a></li>
        <li><a href="?by=descending&category=<?php echo isset($_GET['category']) ? $_GET['category'] : 'A'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'descending') echo 'active'; ?>">descending</a></li>
        <li><a href="?by=newest&category=<?php echo isset($_GET['category']) ? $_GET['category'] : 'A'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?by=oldest&category=<?php echo isset($_GET['category']) ? $_GET['category'] : 'A'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?by=popular&category=<?php echo isset($_GET['category']) ? $_GET['category'] : 'A'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">most followed</a></li>
        <li><a href="?by=least&category=<?php echo isset($_GET['category']) ? $_GET['category'] : 'A'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'least') echo 'active'; ?>">least followed</a></li>
      </ul> 
    </div> 
    <?php 
    if(isset($_GET['by'])){
      $sort = $_GET['by'];
    
      switch ($sort) {
        case 'ascending':
          include "users_order_asc.php";
          break;
        case 'descending':
          include "users_order_desc.php";
          break;
        case 'newest':
          include "users_desc.php";
          break;
        case 'oldest':
          include "users_asc.php";
          break;
        case 'popular':
          include "users_pop.php";
          break;
        case 'least':
          include "users_least.php";
          break;
      }
    }
    else {
      include "users_order_asc.php"; // Include ascending by default
    }
    
    ?>
    <button class="z-3 btn btn-primary btn-md rounded-pill fw-bold position-fixed bottom-0 end-0 m-2" id="scrollToTopBtn" onclick="scrollToTop()"><i class="bi bi-chevron-up" style="-webkit-text-stroke: 3px;"></i></button>
    <script>
      // Show or hide the button based on scroll position
      window.onscroll = function() {
        showScrollButton();
      };

      // Function to show or hide the button based on scroll position
      function showScrollButton() {
        var scrollButton = document.getElementById("scrollToTopBtn");
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
          scrollButton.style.display = "block";
        } else {
          scrollButton.style.display = "none";
        }
      }

      // Function to scroll to the top of the page
      function scrollToTop() {
        document.body.scrollTop = 0; // For Safari
        document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE, and Opera
      }
    </script>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
