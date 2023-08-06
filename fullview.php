<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}

// Connect to the database using PDO
$db = new PDO('sqlite:database.sqlite');

// Get the filename from the query string
$filename = $_GET['artworkid'];

// Get the image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id = :filename");
$stmt->bindParam(':filename', $filename);
$stmt->execute();
$image = $stmt->fetch();
$image_id = $image['id'];

// Get all child images associated with the current image from the "image_child" table
$stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :image_id");
$stmt->bindParam(':image_id', $image_id);
$stmt->execute();
$child_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?> 

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $image['title']; ?></title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php if (empty($image['filename'])) : ?>
      <div class="position-absolute top-50 start-50 translate-middle text-nowrap">
        <h1 class="fw-bold">Image not found</h1>
        <div class="d-flex justify-content-center">
          <a class="btn btn-primary fw-bold" href="/">back to home</a>
        </div>
      </div>
    <?php else : ?>
      <img src="images/<?php echo $image['filename']; ?>" class="mb-1" style="height: 100%; width: 100%;" alt="<?php echo $image['title']; ?>">
      <a id="scrollButton" class="btn btn-primary position-fixed end-0 top-50 rounded-end-0 rounded-start-pill fw-bold" href="image.php?artworkid=<?php echo $image['id']; ?>"><i class="bi bi-arrow-left-circle-fill"></i> back</a>
    <?php endif; ?>
    <?php foreach ($child_images as $child_image) : ?>
      <?php if (empty($child_image['filename'])) : ?>
        <div class="position-absolute top-50 start-50 translate-middle text-nowrap">
          <h1 class="fw-bold">Image not found</h1>
          <div class="d-flex justify-content-center">
            <a class="btn btn-primary fw-bold" href="/">back to home</a>
          </div>
        </div>
      <?php else : ?>
        <img src="images/<?php echo $child_image['filename']; ?>" class="mb-1" style="height: 100%; width: 100%;" alt="<?php echo $image['title']; ?>">
      <?php endif; ?>
    <?php endforeach; ?>
    <style>
      #scrollButton {
        transition: opacity 0.5s ease-in-out; /* Add smooth opacity transition */
        opacity: 1; /* Initially visible */
      }
    </style>
    <script>
      var lastScrollPosition = 0;

      window.addEventListener("scroll", function() {
        var currentScrollPosition = window.pageYOffset || document.documentElement.scrollTop;
        var scrollButton = document.getElementById("scrollButton");

        if (currentScrollPosition > lastScrollPosition) {
          scrollButton.style.opacity = "0"; // Scroll down, fade out button
        } else {
          scrollButton.style.opacity = "1"; // Scroll up, fade in button
        }

        lastScrollPosition = currentScrollPosition;
      });
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>