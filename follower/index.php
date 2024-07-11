<?php
require_once('../auth.php');

$db = new SQLite3('../database.sqlite');

$_GET['id'] && $user_id = $_GET['id'];

$stmt = $db->prepare("SELECT id AS userid, email, artist FROM users WHERE id = :id");
$stmt->bindValue(':id', $user_id);
$result = $stmt->execute();
$row = $result->fetchArray();
$email = $row['email'];
$userName = $row['artist'];
$userId = $row['userid'];
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <title><?php echo $userName; ?>'s followers</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <?php include('../contents/userheader.php'); ?>
    <div class="dropdown">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?id=<?php echo $user_id; ?>&by=ascending&category=<?php echo isset($_GET['category']) ? $_GET['category'] : 'A'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'ascending') echo 'active'; ?>">ascending</a></li>
        <li><a href="?id=<?php echo $user_id; ?>&by=descending&category=<?php echo isset($_GET['category']) ? $_GET['category'] : 'A'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'descending') echo 'active'; ?>">descending</a></li>
        <li><a href="?id=<?php echo $user_id; ?>&by=newest&category=<?php echo isset($_GET['category']) ? $_GET['category'] : 'A'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?id=<?php echo $user_id; ?>&by=oldest&category=<?php echo isset($_GET['category']) ? $_GET['category'] : 'A'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?id=<?php echo $user_id; ?>&by=popular&category=<?php echo isset($_GET['category']) ? $_GET['category'] : 'A'; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">most followed</a></li>
      </ul> 
    </div> 
    <?php 
    if(isset($_GET['by'])){
      $sort = $_GET['by'];
    
      switch ($sort) {
        case 'ascending':
          include "index_order_asc.php";
          break;
        case 'descending':
          include "index_order_desc.php";
          break;
        case 'popular':
          include "index_pop.php";
          break;
        case 'newest':
          include "index_desc.php";
          break;
        case 'oldest':
          include "index_asc.php";
          break;
      }
    }
    else {
      include "index_order_asc.php"; // Include ascending by default
    }
    
    ?>
    <div class="mt-5"></div>
    <button class="z-3 btn btn-primary btn-md rounded-pill fw-bold position-fixed bottom-0 end-0 m-2" id="scrollToTopBtn" onclick="scrollToTop()"><i class="bi bi-chevron-up" style="-webkit-text-stroke: 3px;"></i></button>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>