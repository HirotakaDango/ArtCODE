<?php
require_once('../auth.php');

// Connect to the database using PDO
$db = new PDO('sqlite:../database.sqlite');

// Get the filename from the query string
$artworkId = $_GET['artworkid'];

// Get the current image information from the database
$stmt = $db->prepare("SELECT * FROM private_images WHERE id = :artworkid ");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$image = $stmt->fetch();

// Check if the image exists in the database
if (!$image) {
  header("Location: /error.php");
  exit; // Stop further execution
}

// (Rest of your code remains the same, setting up previous/next private_images, following actions, etc.)

// New Section: Extract the filename only (e.g., d7361061dd44_i0.png)
$filenameWithoutExtension = preg_replace('/.*\/([^\/]+)$/i', '$1', $image['filename']);

// New Section: Extract the base path up to `imageassets_<unique_id>` (e.g., uid_1/data/imageid-24/imageassets_d7361061dd44)
$baseFilename = preg_replace('/(.*imageassets_[^\/]+)\/[^\/]+$/i', '$1', $image['filename']);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $image['title']; ?></title>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
    <style>
      body, html {
        padding: 0;
        margin: 0;
        width: 100%;
        height: 100%;
        overflow: hidden; /* Remove scrollbars */
      }
      iframe {
        border: none; /* Remove default border */
        width: 100%;
        height: 100%;
        display: block; /* Ensure iframe takes up the full container */
      }
      .spinner-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
        width: 100%;
        background-color: #fff; /* Optional: background color for better visibility */
        position: absolute;
      }
    </style>
  </head>
  <body>
    <div class="spinner-container" id="spinner">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
    <!-- The iframe loads the viewer page first -->
    <iframe src="/private_images/viewer.php?<?php echo $baseFilename; ?>#pid=<?php echo $filenameWithoutExtension; ?>" sandbox="allow-scripts allow-same-origin" onload="hideSpinner()"></iframe>
    <button class="position-absolute end-0 bottom-0 m-3 btn btn-primary rounded-pill fw-bold btn-sm" onclick="window.location.reload();">
      <i class="bi bi-arrow-clockwise"></i>
    </button>
  
    <script>
      function hideSpinner() {
        // Hide the spinner first
        document.getElementById('spinner').style.display = 'none';
        // Then, redirect to the artworkid_load.php page
        window.location.href = 'private_artworkid_load.php?artworkid=<?php echo $image["id"]; ?>';
      }
    </script>
  </body>
</html>