<?php
require_once('../../auth.php');

// Connect to the database using PDO
$db = new PDO('sqlite:../../database.sqlite');

// Retrieve the user's email from the session
$email = $_SESSION['email'];

// Handle form submission to delete history
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_history'])) {
  // Delete all history entries for the user's email
  $deleteStmt = $db->prepare("DELETE FROM history WHERE email = :email");
  $deleteStmt->bindParam(':email', $email);
  $deleteStmt->execute();

  // Get the current page URL
  $currentUrl = $_SERVER['REQUEST_URI'];

  // Redirect to the current page to prevent duplicate form submissions
  header("Location: $currentUrl");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>History</title>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('../../header.php'); ?>
    <div class="container mt-2">
      <h5 class="mb-2 text-center fw-bold"><i class="bi bi-clock-history"></i> History</h5>
      <p class="fw-semibold text-center">All of your activities after explore images will be recorded here. You can delete them if you want.</p>
      <!-- Delete All History Button -->
      <form class="mb-4" method="POST" action="history.php" onsubmit="return confirm('Are you sure you want to delete all history?');">
        <input type="hidden" name="delete_history" value="true">
        <button type="submit" class="btn btn-sm btn-danger w-100 fw-semibold">Delete All History</button>
      </form>
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
          }
        }
        else {
          include "index_desc.php";
        }
        
        ?>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>