<?php
require_once('../../auth.php');

// Connect to the SQLite database
$db = new SQLite3('../../database.sqlite');

$email = $_SESSION['email'];
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Define the limit and offset for the query
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT); // Assuming $email is the email you want to search for
$resultNum = $queryNum->execute();
$user = $resultNum->fetchArray(SQLITE3_ASSOC);

$numpage = $user['numpage'];

$perPage = empty($numpage) ? 50 : $numpage;

// Calculate the offset
$offset = ($page - 1) * $perPage;

// Fetch the total count of emails
$totalResult = $db->query("
  SELECT COUNT(*) AS count FROM inboxes
  WHERE to_email = '{$email}' AND title LIKE '%{$search}%'
");
$totalRow = $totalResult->fetchArray(SQLITE3_ASSOC);
$totalCount = $totalRow['count'];
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Inboxes</title>
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
  </head>
  <body>
    <?php include('../../header.php'); ?>
    <div class="container mt-4">
      <h1 class="mb-4 fw-bold">My Inboxes</h1>
      <form method="get" class="mb-4">
        <div class="input-group w-100">
          <input type="text" class="form-control border-0 bg-body-tertiary rounded-start-4 focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>" name="search" value="<?php echo $search; ?>" placeholder="Search by title or content">
          <button class="btn bg-body-tertiary rounded-end-4" type="submit"><i class="bi bi-search"></i></button>
        </div>
      </form>
    </div>
    <div class="dropdown container">
      <button class="btn btn-sm fw-bold rounded-pill mb-2 btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        sort by
      </button>
      <ul class="dropdown-menu">
        <?php
        // Get current query parameters, excluding 'by' and 'page'
        $queryParams = array_diff_key($_GET, array('by' => '', 'page' => ''));
        
        // Define sorting options and labels
        $sortOptions = [
          'newest' => 'newest',
          'oldest' => 'oldest',
          'date_newest' => 'newest by date',
          'date_oldest' => 'oldest by date',
          'order_asc' => 'from A to Z',
          'order_desc' => 'from Z to A',
          'read' => 'already read',
          'noread' => 'not read'
        ];
    
        // Loop through each sort option
        foreach ($sortOptions as $key => $label) {
          // Determine if the current option is active
          $activeClass = (!isset($_GET['by']) && $key === 'newest') || (isset($_GET['by']) && $_GET['by'] === $key) ? 'active' : '';
          
          // Generate the dropdown item with the appropriate active class
          echo '<li><a href="?' . http_build_query(array_merge($queryParams, ['by' => $key, 'page' => isset($_GET['page']) ? $_GET['page'] : '1'])) . '" class="dropdown-item fw-bold ' . $activeClass . '">' . $label . '</a></li>';
        }
        ?>
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
        case 'date_newest':
        include "index_date_desc.php";
        break;
        case 'date_oldest':
        include "index_date_asc.php";
        break;
        case 'order_desc':
        include "index_order_desc.php";
        break;
        case 'order_asc':
        include "index_order_asc.php";
        break;
        case 'read':
        include "index_read.php";
        break;
        case 'noread':
        include "index_not_read.php";
        break;
      }
    }
    else {
      include "index_desc.php";
    }
    
    ?>
    <?php
    // Calculate total pages
    $totalPages = ceil($totalCount / $perPage);
    $currentUrl = strtok($_SERVER["REQUEST_URI"], '?');
    $queryParams = array_diff_key($_GET, array('page' => ''));
    ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $currentUrl . '?' . http_build_query(array_merge($queryParams, ['page' => 1])); ?>">
          <i class="bi text-stroke bi-chevron-double-left"></i>
        </a>
      <?php endif; ?>
    
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $currentUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $page - 1])); ?>">
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="' . $currentUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $i])) . '">' . $i . '</a>';
          }
        }
      ?>
    
      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $currentUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $page + 1])); ?>">
          <i class="bi text-stroke bi-chevron-right"></i>
        </a>
      <?php endif; ?>
    
      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $currentUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $totalPages])); ?>">
          <i class="bi text-stroke bi-chevron-double-right"></i>
        </a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>