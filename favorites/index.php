<?php
require_once('../auth.php');

// Connect to the database
$db = new PDO('sqlite:../database.sqlite');

$email = $_SESSION['email'];

// Get the current user's ID
$current_user_id = $_GET['id'];

// Get the current user's email and artist
$query = $db->prepare('SELECT email, artist FROM users WHERE id = :id');
$query->bindParam(':id', $current_user_id);
$query->execute();
$current_user = $query->fetch();
$current_email = $current_user['email'];
$current_artist = $current_user['artist'];

// Get the total count of favorite images for the current user
$query = $db->prepare('SELECT COUNT(*) FROM images JOIN favorites ON images.id = favorites.image_id WHERE favorites.email = :email');
$query->bindParam(':email', $current_email);
$query->execute();
$total = $query->fetchColumn();

// Process any favorite/unfavorite requests
if (isset($_POST['favorite']) || isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  
  // Check if the image ID is valid
  $query = $db->prepare('SELECT COUNT(*) FROM images WHERE id = :id');
  $query->bindParam(':id', $image_id);
  $query->execute();
  $valid_image_id = $query->fetchColumn();
  
  if ($valid_image_id) {
    // Check if the image has already been favorited by the current user
    $query = $db->prepare('SELECT COUNT(*) FROM favorites WHERE email = :email AND image_id = :image_id');
    $query->bindParam(':email', $email);
    $query->bindParam(':image_id', $image_id);
    $query->execute();
    $existing_fav = $query->fetchColumn();

    if (isset($_POST['favorite'])) {
      if ($existing_fav == 0) {
        $query = $db->prepare('INSERT INTO favorites (email, image_id) VALUES (:email, :image_id)');
        $query->bindParam(':email', $email);
        $query->bindParam(':image_id', $image_id);
        $query->execute();
      }
    } elseif (isset($_POST['unfavorite'])) {
      if ($existing_fav > 0) {
        $query = $db->prepare('DELETE FROM favorites WHERE email = :email AND image_id = :image_id');
        $query->bindParam(':email', $email);
        $query->bindParam(':image_id', $image_id);
        $query->execute();
      }
    }
  }
  
  // Get the current page URL
  $currentUrl = $_SERVER['REQUEST_URI'];

  // Redirect to the current page to prevent duplicate form submissions
  header("Location: $currentUrl");
  exit();
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $current_artist; ?>'s favorite</title>
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="../style.css">
  </head>
  <body>
    <?php include('../header.php'); ?>
    <div class="dropdown mt-1">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=newest&id=<?php echo $current_user_id; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?by=oldest&id=<?php echo $current_user_id; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?by=popular&id=<?php echo $current_user_id; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">popular</a></li>
        <li><a href="?by=view&id=<?php echo $current_user_id; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'view') echo 'active'; ?>">most viewed</a></li>
        <li><a href="?by=least&id=<?php echo $current_user_id; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'least') echo 'active'; ?>">least viewed</a></li>
        <li><a href="?by=liked&id=<?php echo $current_user_id; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'liked') echo 'active'; ?>">liked</a></li>
        <li><a href="?by=order_asc&id=<?php echo $current_user_id; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'order_asc') echo 'active'; ?>">from A to Z</a></li>
        <li><a href="?by=order_desc&id=<?php echo $current_user_id; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'order_desc') echo 'active'; ?>">from Z to A</a></li>
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
    <script>
      function goBack() {
        window.location.href = "../artist.php?id=<?php echo $current_user_id; ?>";
      }
    </script>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html> 