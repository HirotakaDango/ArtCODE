<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: session.php");
  exit;
}

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Check if any images were uploaded
if (isset($_FILES['image'])) {

  ob_start(); // Start output buffering to prevent header errors

  $images = $_FILES['image'];

  // Loop through each uploaded image
  for ($i = 0; $i < count($images['name']); $i++) {
    $image = array(
      'name' => $images['name'][$i],
      'type' => $images['type'][$i],
      'tmp_name' => $images['tmp_name'][$i],
      'error' => $images['error'][$i],
      'size' => $images['size'][$i]
    );

    // Check if the image is valid
    if ($image['error'] == 0) {
      // Generate a unique file name
      $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
      $filename = uniqid() . '.' . $ext;

      // Save the original image
      move_uploaded_file($image['tmp_name'], 'images/' . $filename);

      // Determine the image type and generate the thumbnail
      $image_info = getimagesize('images/' . $filename);
      $mime_type = $image_info['mime'];
      switch ($mime_type) {
        case 'image/jpeg':
          $source = imagecreatefromjpeg('images/' . $filename);
          break;
        case 'image/png':
          $source = imagecreatefrompng('images/' . $filename);
          break;
        case 'image/gif':
          $source = imagecreatefromgif('images/' . $filename);
          break;
        default:
          echo "Error: Unsupported image format.";
          exit;
      }

      if ($source === false) {
        echo "Error: Failed to create image source.";
        exit;
      }

      $original_width = imagesx($source);
      $original_height = imagesy($source);
      $ratio = $original_width / $original_height;
      $thumbnail_width = 300;
      $thumbnail_height = intval(300 / $ratio); // Convert float to integer

      $thumbnail = imagecreatetruecolor($thumbnail_width, $thumbnail_height);

      if ($thumbnail === false) {
        echo "Error: Failed to create thumbnail.";
        exit;
      }

      imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $original_width, $original_height);

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

      // Add the image to the database
      $username = $_SESSION['username'];
      $tags = filter_var($_POST['tags'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
      $tags = explode(",", $tags);
      $tags = array_map('trim', $tags); // Remove extra white space from each tag
      $tags = array_filter($tags); // Remove any empty tags
      $tags = array_values($tags); // Reset array indexes
      $tags = implode(",", $tags); // Join tags by comma
      $date = date('Y-m-d'); // Get the current date in YYYY-MM-DD format
      $stmt = $db->prepare("INSERT INTO images (username, filename, tags, title, imgdesc, link, date) VALUES (:username, :filename, :tags, :title, :imgdesc, :link, :date)");
      $stmt->bindValue(':username', $username);
      $stmt->bindValue(':filename', $filename);
      $stmt->bindValue(':tags', $tags);
      $stmt->bindValue(':title', filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));
      $stmt->bindValue(':imgdesc', filter_var($_POST['imgdesc'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));
      $stmt->bindValue(':link', filter_var($_POST['link'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));
      $stmt->bindValue(':date', $date);
      $stmt->execute();
    } else {
      echo "Error uploading image.";
    }
  }

  header("Location: index.php");
  exit;
}
?>