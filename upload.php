<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: session.php");
  exit;
}

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

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
    $thumbnail_height = 300 / $ratio;

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
    $tags = htmlspecialchars($_POST['tags']);
    $tags = explode(",", $tags);
    $tags = array_map('trim', $tags); // Remove extra white space from each tag
    $tags = array_filter($tags); // Remove any empty tags
    $tags = array_values($tags); // Reset array indexes
    $tags = implode(",", $tags); // Join tags by comma
    $stmt = $db->prepare("INSERT INTO images (username, filename, tags, title, imgdesc, link) VALUES (:username, :filename, :tags, :title, :imgdesc, :link)");
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':filename', $filename);
    $stmt->bindValue(':tags', $tags);
    $stmt->bindValue(':title', htmlspecialchars($_POST['title']));
    $stmt->bindValue(':imgdesc', htmlspecialchars($_POST['imgdesc']));
    $stmt->bindValue(':link', htmlspecialchars($_POST['link']));
    $stmt->execute();

    header("Location: index.php");
    exit;
  } else {
    echo "Error uploading image.";
  }
}
?>
