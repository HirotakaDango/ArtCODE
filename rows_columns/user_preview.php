<?php
$id = $_GET['id'];
$source = "user_load.php?id=" . urlencode($id);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
    <iframe src="<?php echo htmlspecialchars($source, ENT_QUOTES, 'UTF-8'); ?>" onload="hideSpinner()"></iframe>
  
    <script>
      function hideSpinner() {
        document.getElementById('spinner').style.display = 'none';
      }
    </script>
  </body>
</html>