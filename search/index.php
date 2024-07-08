<?php
require_once('../auth.php');

$email = $_SESSION['email'];

// Establish a connection to the SQLite database
$db = new SQLite3('../database.sqlite');

$searchTerm = $_GET['q'];
$by = isset($_GET['by']) ? $_GET['by'] : 'newest';

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $existing_fav = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = $image_id");

  if ($existing_fav == 0) {
    $db->exec("INSERT INTO favorites (email, image_id) VALUES ('$email', $image_id)");
  }
  
  // Redirect to the same page with the appropriate sorting parameter
  $page = isset($_GET['page']) ? $_GET['page'] : 1; // check if page is set, default to 1
  $by = isset($_GET['by']) ? $_GET['by'] : 'newest'; // check if by is set, default to newest
  header("Location: {$_SERVER['REQUEST_URI']}");
  exit(); 
  
} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $db->exec("DELETE FROM favorites WHERE email = '$email' AND image_id = $image_id");

  // Redirect to the same page with the appropriate sorting parameter
  $page = isset($_GET['page']) ? $_GET['page'] : 1; // check if page is set, default to 1
  $by = isset($_GET['by']) ? $_GET['by'] : 'newest'; // check if by is set, default to newest
  header("Location: {$_SERVER['REQUEST_URI']}");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $_GET['q']; ?></title>
    <link rel="manifest" href="../manifest.json">
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <div class="dropdown">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=newest&year=<?php echo isset($_GET['year']) ? $_GET['year'] : 'all'; ?>&q=<?php echo $searchTerm; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?by=oldest&year=<?php echo isset($_GET['year']) ? $_GET['year'] : 'all'; ?>&q=<?php echo $searchTerm; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?by=popular&year=<?php echo isset($_GET['year']) ? $_GET['year'] : 'all'; ?>&q=<?php echo $searchTerm; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">popular</a></li>
        <li><a href="?by=view&year=<?php echo isset($_GET['year']) ? $_GET['year'] : 'all'; ?>&q=<?php echo $searchTerm; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'view') echo 'active'; ?>">most viewed</a></li>
        <li><a href="?by=least&year=<?php echo isset($_GET['year']) ? $_GET['year'] : 'all'; ?>&q=<?php echo $searchTerm; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'least') echo 'active'; ?>">least viewed</a></li>
        <li><a href="?by=liked&year=<?php echo isset($_GET['year']) ? $_GET['year'] : 'all'; ?>&q=<?php echo $searchTerm; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'liked') echo 'active'; ?>">liked</a></li>
        <li><a href="?by=order_asc&year=<?php echo isset($_GET['year']) ? $_GET['year'] : 'all'; ?>&q=<?php echo $searchTerm; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'order_asc') echo 'active'; ?>">from A to Z</a></li>
        <li><a href="?by=order_desc&year=<?php echo isset($_GET['year']) ? $_GET['year'] : 'all'; ?>&q=<?php echo $searchTerm; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'order_desc') echo 'active'; ?>">from Z to A</a></li>
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
            case 'popular':
            include "index_pop.php";
            break;
            case 'view':
            include "index_view.php";
            break;
            case 'least':
            include "index_least.php";
            break;
            case 'liked':
            include "index_liked.php";
            break;
            case 'order_asc':
            include "index_order_asc.php";
            break;
            case 'order_desc':
            include "index_order_desc.php";
            break;
          }
        }
        else {
          include "index_desc.php";
        }
        
        ?>
    <style>
      .text-stroke {
        -webkit-text-stroke: 1px;
      }

      .text-shadow {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);
      }
      
      .overlay {
        position: relative;
        display: flex;
        flex-direction: column; /* Change to column layout */
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5); /* Adjust background color and opacity */
        text-align: center;
        position: absolute;
        top: 0;
        left: 0;
        border-radius: 0.5rem;
      }

      .overlay i {
        font-size: 48px; /* Adjust icon size */
        position: absolute;
        bottom: 45%;
      }

      .overlay span {
        font-size: 18px; /* Adjust text size */
        margin-top: 35px; /* Add spacing between icon and text */
      }
      
      .rounded-custom {
        border-radius: 0.5rem;
      }
    </style>
    <script>
      function shareImage(userId) {
        // Compose the share URL
        var shareUrl = '../image.php?artworkid=' + userId;

        // Check if the Share API is supported by the browser
        if (navigator.share) {
          navigator.share({
          url: shareUrl
        })
          .then(() => console.log('Shared successfully.'))
          .catch((error) => console.error('Error sharing:', error));
        } else {
          console.log('Share API is not supported in this browser.');
          // Provide an alternative action for browsers that do not support the Share API
          // For example, you can open a new window with the share URL
          window.open(shareUrl, '_blank');
        }
      }
    </script>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>