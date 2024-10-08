<?php
// admin/images_section/users/index.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();

// Retrieve the email from the session
$email = $_SESSION['admin']['email'];

// Connect to the SQLite database
$db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/database.sqlite');
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <title>All Users</title>
    <?php include('../../../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>
  <body>
    <div class="container-fluid px-0">
      <div class="row g-0">
        <div class="col-auto">
          <?php include('../../admin_header.php'); ?>
        </div>
        <div class="col overflow-auto vh-100">
          <?php include('../../navbar.php'); ?>
          <div>
            <div class="dropdown">
              <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-images"></i> sort by
              </button>
              <ul class="dropdown-menu">
                <li><a href="?by=ascending&category=<?php echo isset($_GET['category']) ? $_GET['category'] : 'A'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'ascending') echo 'active'; ?>">ascending</a></li>
                <li><a href="?by=descending&category=<?php echo isset($_GET['category']) ? $_GET['category'] : 'A'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'descending') echo 'active'; ?>">descending</a></li>
                <li><a href="?by=newest&category=<?php echo isset($_GET['category']) ? $_GET['category'] : 'A'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
                <li><a href="?by=oldest&category=<?php echo isset($_GET['category']) ? $_GET['category'] : 'A'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
                <li><a href="?by=popular&category=<?php echo isset($_GET['category']) ? $_GET['category'] : 'A'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">most followed</a></li>
                <li><a href="?by=least&category=<?php echo isset($_GET['category']) ? $_GET['category'] : 'A'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'least') echo 'active'; ?>">least followed</a></li>
                <li><a href="?by=followed&category=<?php echo isset($_GET['category']) ? $_GET['category'] : 'A'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'followed') echo 'active'; ?>">followed</a></li>
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
                case 'followed':
                  include "users_followed.php";
                  break;
              }
            }
            else {
              include "users_order_asc.php"; // Include ascending by default
            }
            
            ?>
            <button class="z-3 btn btn-primary btn-md rounded-pill fw-bold position-fixed bottom-0 end-0 m-2" id="scrollToTopBtn" onclick="scrollToTop()"><i class="bi bi-chevron-up" style="-webkit-text-stroke: 3px;"></i></button>
          </div>
        </div>
      </div>
    </div>
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
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>
