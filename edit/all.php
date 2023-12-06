<?php
require_once('../auth.php');

// Connect to the database using PDO
$db = new PDO('sqlite:../database.sqlite');

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  // Redirect to index.php if not logged in
  header("Location: ../index.php");
  exit;
}

// Get the filename from the query string
$filename = $_GET['id'];

// Get the current image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id = :filename ");
$stmt->bindParam(':filename', $filename);
$stmt->execute();
$image = $stmt->fetch();

// Get the ID of the current image and the email of the owner
$image_id = $image['id'];
$email = $image['email'];

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
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container-fluid mt-3 mb-5">
      <nav aria-label="breadcrumb">
        <div class="d-none d-md-block d-lg-block">
          <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-3">
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">Home</a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis py-2 text-decoration-none text-white fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/edit/?id=<?php echo $image['id']; ?>"><?php echo $image['title']; ?></a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis border-bottom border-3 py-2 text-decoration-none text-white fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/edit/all.php?id=<?php echo $image['id']; ?>">All images from <?php echo $image['title']; ?></a>
            </li>
          </ol>
        </div>
        <div class="d-md-none d-lg-none">
          <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-3">
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">Home</a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis py-2 text-decoration-none text-white fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/edit/?id=<?php echo $image['id']; ?>"><?php echo $image['title']; ?></a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis border-bottom border-3 py-2 text-decoration-none text-white fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/edit/all.php?id=<?php echo $image['id']; ?>">All images from <?php echo $image['title']; ?></a>
            </li>
          </ol>
        </div>
      </nav>
      <?php foreach ($child_images as $child_image) : ?>
        <?php if (empty($child_image['filename'])) : ?>
          <div class="position-absolute top-50 start-50 translate-middle text-nowrap">
            <h1 class="fw-bold">Image not found</h1>
            <div class="d-flex justify-content-center">
              <a class="btn btn-primary fw-bold" href="/">back to home</a>
            </div>
          </div>
        <?php else : ?>
          <div class="position-relative">
            <img src="../images/<?php echo $child_image['filename']; ?>" class="mb-1 rounded" style="height: 100%; width: 100%;" alt="<?php echo $image['title']; ?>">
            <div class="position-absolute bottom-0 start-0 text-white p-3 fw-bold w-100 bg-dark bg-opacity-75">
              <p>Image Title: <?php echo $image['title']; ?></p>
              <p>Image Size: <?php echo getImageSizeInMB($child_image['filename']); ?> MB</p>
            </div>
            <form action="delete_image_child.php" method="post">
              <input type="hidden" name="image_id" value="<?php echo $child_image['id']; ?>">
              <button type="submit" class="btn btn-danger fw-bold position-absolute top-0 end-0 m-2">delete</button>
            </form>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
      <?php
      // Function to calculate the size of an image in MB
      function getImageSizeInMB($filename) {
        return round(filesize('../images/' . $filename) / (1024 * 1024), 2);
      }

      // Get the total size of images from 'images' table
      $stmt = $db->prepare("SELECT * FROM images WHERE id = :filename");
      $stmt->bindParam(':filename', $filename);
      $stmt->execute();
      $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Get the total size of images from 'image_child' table
      $stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :filename");
      $stmt->bindParam(':filename', $filename);
      $stmt->execute();
      $image_childs = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $images_total_size = 0;
      foreach ($images as $image) {
        $images_total_size += getImageSizeInMB($image['filename']);
      }

      $image_child_total_size = 0;
      foreach ($image_childs as $image_child) {
        $image_child_total_size += getImageSizeInMB($image_child['filename']);
      }

      $total_size = $images_total_size + $image_child_total_size;
      ?>
    </div>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
