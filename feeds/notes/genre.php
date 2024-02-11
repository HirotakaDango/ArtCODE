<?php
require_once('auth.php');

$email = $_SESSION['email'];

$db = new PDO('sqlite:../../database.sqlite');

// get the tag from the URL parameter
$tag = isset($_GET['tag']) ? $_GET['tag'] : '';

// count the total number of posts with the given tag and current email
$stmt = $db->prepare('SELECT COUNT(*) FROM posts WHERE tags LIKE :tag AND email = :email');
$stmt->bindValue(':tag', '%' . $tag . '%');
$stmt->bindValue(':email', $email);
$stmt->execute();
$total_posts = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <title>Posts by Genre: <?php echo $tag ?></title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="dropdown mt-3">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=newest&tag=<?php echo $tag; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?by=oldest&tag=<?php echo $tag; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?by=date&tag=<?php echo $tag; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'date') echo 'active'; ?>">date</a></li>
        <li><a href="?by=starred&tag=<?php echo $tag; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'starred') echo 'active'; ?>">starred</a></li>
      </ul> 
    </div> 
        <?php 
        if(isset($_GET['by'])){
          $sort = $_GET['by'];
 
          switch ($sort) {
            case 'newest':
            include "genre_desc.php";
            break;
            case 'oldest':
            include "genre_asc.php";
            break;
            case 'date':
            include "genre_date.php";
            break;
            case 'starred':
            include "genre_starred.php";
            break;
          }
        }
        else {
          include "genre_desc.php";
        }
        
        ?>
    <div class="mt-5"></div>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>