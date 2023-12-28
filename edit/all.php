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
    <title>All Images From <?php echo $image['title']; ?></title>
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container-fluid mt-3 mb-5">
      <nav aria-label="breadcrumb">
        <div class="d-none d-md-block d-lg-block">
          <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary bg-opacity-25 rounded-3" style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);">
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">Home</a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis py-2 text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/image.php?artworkid=<?php echo $image['id']; ?>"><?php echo $image['title']; ?></a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis py-2 text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/edit/?id=<?php echo $image_id; ?>">Edit <?php echo $image['title']; ?></a>
            </li>
            <li class="breadcrumb-item mb-2 mb-md-0">
              <a class="link-body-emphasis py-2 text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/edit/upload.php?id=<?php echo $image_id; ?>">Upload new images to <?php echo $image['title']; ?></a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis py-2 text-decoration-none fw-bold" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/edit/all.php?id=<?php echo $image['id']; ?>">All images from <?php echo $image['title']; ?></a>
            </li>
          </ol>
        </div>
        <div class="d-md-none d-lg-none">
          <a class="btn bg-body-tertiary p-3 bg-opacity-25 fw-bold w-100 text-start mb-2" data-bs-toggle="collapse" href="#collapseModal" role="button" aria-expanded="false" aria-controls="collapseExample">
            <i class="bi bi-list" style="-webkit-text-stroke: 1px;"></i> Menu
          </a>
          <div class="collapse bg-body-tertiary bg-opacity-25 mb-2 rounded" id="collapseModal">
            <div class="btn-group-vertical w-100">
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">Home</a>
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/image.php?artworkid=<?php echo $image['id']; ?>"><?php echo $image['title']; ?></a>
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/edit/?id=<?php echo $image['id']; ?>">Edit <?php echo $image['title']; ?></a>
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/edit/upload.php?id=<?php echo $image['id']; ?>">Upload new images to <?php echo $image['title']; ?></a>
              <a class="btn py-2 rounded text-start fw-bold" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/edit/all.php?id=<?php echo $image['id']; ?>"><i class="bi bi-chevron-right small" style="-webkit-text-stroke: 2px;"></i> All images from <?php echo $image['title']; ?></a>
            </div>
          </div>
        </div>
      </nav>
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
            <div class="d-flex position-absolute bottom-0 start-0 p-3 fw-bold w-100 bg-dark bg-opacity-75">
              <div>
                <p>Image Title: <?php echo $image['title']; ?></p>
                <p>Image Size: <?php echo getImageSizeInMB($child_image['filename']); ?> MB</p>
              </div>
              <button type="button" class="btn btn-outline-light border-0 fw-bold ms-auto my-auto" data-bs-toggle="modal" data-bs-target="#deleteImage_<?php echo $child_image['id']; ?>">
                <i class="bi bi-trash-fill"></i>
              </button>
            </div>
          </div>
          <div class="modal fade" id="deleteImage_<?php echo $child_image['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content rounded-3 shadow">
                <div class="modal-body p-4 text-center">
                  <h5 class="mb-0">Delete this image "<?php echo $child_image['filename']; ?>"?</h5>
                  <p class="mb-0">This action can't be undone</p>
                </div>
                <form method="POST" action="delete_image_child.php">
                  <div class="modal-footer flex-nowrap p-0">
                    <input type="hidden" name="image_id" value="<?php echo $child_image['id']; ?>">
                    <button type="submit" class="btn btn-lg btn-link fs-6 text-danger text-decoration-none col-6 py-3 m-0 rounded-0 border-end"><strong>Yes, delete</strong></button>
                    <button type="button" class="btn btn-lg btn-link fs-6 text-decoration-none col-6 py-3 m-0 rounded-0" data-bs-dismiss="modal">Cancel</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
