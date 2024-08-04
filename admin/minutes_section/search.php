<?php
// admin/minutes_section/index.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();

// Retrieve the email from the session
$email = $_SESSION['admin']['email'];

// Connect to the SQLite database
$db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/database.sqlite');

$searchPage = isset($_GET['q']) ? $_GET['q'] : null;
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <title>Search for <?php echo $searchPage; ?></title>
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
            <div class="dropdown my-2">
              <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-images"></i> sort by
              </button>
              <ul class="dropdown-menu">
                <li><a href="?q=<?php echo $searchPage; ?>&by=newest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
                <li><a href="?q=<?php echo $searchPage; ?>&by=oldest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
                <li><a href="?q=<?php echo $searchPage; ?>&by=popular&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">popular</a></li>
                <li><a href="?q=<?php echo $searchPage; ?>&by=view&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'view') echo 'active'; ?>">most viewed</a></li>
                <li><a href="?q=<?php echo $searchPage; ?>&by=least&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'least') echo 'active'; ?>">least viewed</a></li>
              </ul>
            </div>
                <?php 
                if(isset($_GET['by'])){
                  $sort = $_GET['by'];
         
                  switch ($sort) {
                    case 'newest':
                    include "search_desc.php";
                    break;
                    case 'oldest':
                    include "search_asc.php";
                    break;
                    case 'popular':
                    include "search_pop.php";
                    break;
                    case 'view':
                    include "search_view.php";
                    break;
                    case 'least':
                    include "search_least.php";
                    break;
                  }
                }
                else {
                  include "search_desc.php";
                }
                
                ?>
            <div class="mt-5"></div>
          </div>
        </div>
      </div>
    </div>
    <style>
      .text-shadow {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);
      }
    </style>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
