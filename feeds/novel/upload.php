<?php
require_once('../../auth.php');

$db = new PDO('sqlite:../../database.sqlite');

$email = $_SESSION['email'];

// Check if an image was uploaded
if (isset($_FILES['image'])) {
  $image = $_FILES['image'];

  // Check if the image is valid
  if ($image['error'] == 0) {
    // Generate a unique file name
    $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;

    // Save the original image
    move_uploaded_file($image['tmp_name'], 'images/' . $filename);

    // Determine the image type and generate the thumbnail
    switch ($ext) {
      case 'jpg':
      case 'jpeg':
        $source = imagecreatefromjpeg('images/' . $filename);
        break;
      case 'png':
        $source = imagecreatefrompng('images/' . $filename);
        break;
      case 'gif':
        $source = imagecreatefromgif('images/' . $filename);
        break;
      default:
        echo "Error: Unsupported image format.";
        exit;
    }

    // Calculate the new dimensions based on the aspect ratio of the original image
    $source_width = imagesx($source);
    $source_height = imagesy($source);
    $source_ratio = $source_width / $source_height;
    $thumbnail_width = 300;
    $thumbnail_height = round($thumbnail_width / $source_ratio);

    // Calculate the target dimensions while maintaining the original aspect ratio
    if ($source_ratio > 1) {
      $thumbnail_height = round($thumbnail_width / $source_ratio);
    } else {
      $thumbnail_width = round($thumbnail_height * $source_ratio);
    }

    $thumbnail = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
    imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $source_width, $source_height);

    switch ($ext) {
      case 'jpg':
      case 'jpeg':
        imagejpeg($thumbnail, 'thumbnails/' . $filename);
        break;
      case 'png':
        imagepng($thumbnail, 'thumbnails/' . $filename);
        break;
      case 'gif':
        imagegif($thumbnail, 'thumbnails/' . $filename);
        break;
      default:
        echo "Error: Unsupported image format.";
        exit;
    }

    // Retrieve the title and description values from the form submission
    $title = $_POST['title'];
    $description = $_POST['description'];
    $content = $_POST['content'];
    $tags = $_POST['tags'];

    // Add the image to the database
    $stmt = $db->prepare("INSERT INTO novel (email, filename, title, description, content, tags, date) VALUES (:email, :filename, :title, :description, :content, :tags, :date)");
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':filename', $filename);
    $stmt->bindValue(':title', $title);
    $stmt->bindValue(':description', nl2br($description));
    $stmt->bindValue(':content', nl2br($content));
    $stmt->bindValue(':tags', $tags);

    // Bind the current date with the desired format
    $currentDate = date("Y/m/d");
    $stmt->bindValue(':date', $currentDate);

    $stmt->execute();

    header("Location: index.php");
    exit;
  } else {
    echo "Error uploading image.";
  }
}
?>

<!DOCTYPE html>
<html data-bs-theme="dark">
  <head>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload</title>
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include ('header.php'); ?>
    <div class="container">
      <h2 class="text-center fw-bold mb-4">UPLOAD YOUR WORKS</h2>
      <form method="post" enctype="multipart/form-data">
        <?php if (isset($_GET['error'])): ?>
          <p><?php echo $_GET['error']; ?></p>
        <?php endif ?>
        <div class="row featurette">
          <div class="col-md-7 order-md-2">
            <img class="d-block border border-2 object-fit-cover rounded mb-2" id="file-ip-1-preview" style="height: 340px; width: 100%;">
          </div>
          <div class="col-md-5 order-md-1">
            <input class="form-control mb-2" type="file" name="image" type="file" id="file-ip-1" accept="image/*" onchange="showPreview(event);" required>
            <div class="input-group mb-2 gap-2">
              <input class="form-control rounded" type="text" placeholder="Title" id="title" name="title" required>
              <input class="form-control rounded" type="text" placeholder="Tags" id="tags" name="tags" required>
            </div>
            <textarea class="form-control mb-2" type="text" placeholder="description" id="description" style="height: 247px;" name="description" required></textarea>
          </div>
        </div>
        <textarea class="form-control mb-2" type="text" placeholder="content" id="content" style="height: 200px;" name="content" required></textarea>
        <button class="btn btn-primary fw-bold w-100" type="submit">upload</button>
      </form>
    </div>
    <br>
    <script>
      function showPreview(event){
        if(event.target.files.length > 0){
          var src = URL.createObjectURL(event.target.files[0]);
          var preview = document.getElementById("file-ip-1-preview");
          preview.src = src;
          preview.style.display = "block";
        }
      }
    </script>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>