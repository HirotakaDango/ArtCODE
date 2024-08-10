<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
  header("Location: /preview/home/");
  exit();
}

// Connect to SQLite database
$db = new PDO('sqlite:../database.sqlite');

// Function to create a gradient image
function createGradientImage($width, $height) {
  $image = imagecreatetruecolor($width, $height);

  // Generate two random colors
  $color1 = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
  $color2 = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));

  for ($y = 0; $y < $height; $y++) {
    $r1 = ($color1 >> 16) & 0xFF;
    $g1 = ($color1 >> 8) & 0xFF;
    $b1 = $color1 & 0xFF;

    $r2 = ($color2 >> 16) & 0xFF;
    $g2 = ($color2 >> 8) & 0xFF;
    $b2 = $color2 & 0xFF;

    $r = $r1 + ($r2 - $r1) * ($y / $height);
    $g = $g1 + ($g2 - $g1) * ($y / $height);
    $b = $b1 + ($b2 - $b1) * ($y / $height);

    $color = imagecolorallocate($image, $r, $g, $b);
    imageline($image, 0, $y, $width, $y, $color);
  }

  return $image;
}

// Function to save an image and create a thumbnail
function saveImageAndThumbnail($image, $originalPath, $thumbnailPath) {
  imagepng($image, $originalPath);
  imagedestroy($image);

  // Create a thumbnail
  $thumbnailWidth = 400;
  $thumbnailHeight = round($thumbnailWidth * imagesy($image) / imagesx($image));
  $thumbnail = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);
  $originalWidth = imagesx($image);
  $originalHeight = imagesy($image);
  imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $thumbnailWidth, $thumbnailHeight, $originalWidth, $originalHeight);
  imagepng($thumbnail, $thumbnailPath);
  imagedestroy($thumbnail);
}

// Check if there are any images in the database
$query = 'SELECT COUNT(*) FROM images';
$statement = $db->query($query);
$imageCount = $statement->fetchColumn();

if ($imageCount == 0) {
  // Define the image dimensions
  $width = 2500;
  $height = 1400;

  // Generate 24 images for illustration
  for ($i = 0; $i < 24; $i++) {
    // Generate a new gradient image
    $image = createGradientImage($width, $height);

    // Define paths
    $fileName = uniqid('img_') . '.png';
    $originalPath = '../images/' . $fileName;
    $thumbnailPath = '../thumbnails/' . $fileName;

    // Save image and thumbnail
    saveImageAndThumbnail($image, $originalPath, $thumbnailPath);

    // Insert metadata into the database
    $stmt = $db->prepare('INSERT INTO images (filename, email, tags, title, imgdesc, date, type, artwork_type) VALUES (:filename, :email, :tags, :title, :imgdesc, :date, :type, :artwork_type)');
    $stmt->bindValue(':filename', $fileName, PDO::PARAM_STR);
    $stmt->bindValue(':email', $_SESSION['email'], PDO::PARAM_STR);
    $stmt->bindValue(':tags', 'gradient, wallpaper, background', PDO::PARAM_STR);
    $stmt->bindValue(':title', 'Gradient Image ' . ($i + 1), PDO::PARAM_STR);
    $stmt->bindValue(':imgdesc', 'Generated gradient image', PDO::PARAM_STR);
    $stmt->bindValue(':date', date('Y-m-d H:i:s'), PDO::PARAM_STR);
    $stmt->bindValue(':type', 'safe', PDO::PARAM_STR);
    $stmt->bindValue(':artwork_type', 'illustration', PDO::PARAM_STR);
    $stmt->execute();

    // Insert into image_child table
    $imageId = $db->lastInsertId(); // Get the ID of the inserted image
    $stmt = $db->prepare('INSERT INTO image_child (filename, image_id, email) VALUES (:filename, :image_id, :email)');
    $stmt->bindValue(':filename', $fileName, PDO::PARAM_STR);
    $stmt->bindValue(':image_id', $imageId, PDO::PARAM_INT);
    $stmt->bindValue(':email', $_SESSION['email'], PDO::PARAM_STR);
    $stmt->execute();
  }

  // Generate 24 images for manga
  for ($i = 0; $i < 24; $i++) {
    // Generate a new gradient image
    $image = createGradientImage($width, $height);

    // Define paths
    $fileName = uniqid('img_') . '.png';
    $originalPath = '../images/' . $fileName;
    $thumbnailPath = '../thumbnails/' . $fileName;

    // Save image and thumbnail
    saveImageAndThumbnail($image, $originalPath, $thumbnailPath);

    // Insert metadata into the database
    $stmt = $db->prepare('INSERT INTO images (filename, email, tags, title, imgdesc, date, type, artwork_type, episode_name) VALUES (:filename, :email, :tags, :title, :imgdesc, :date, :type, :artwork_type, :episode_name)');
    $stmt->bindValue(':filename', $fileName, PDO::PARAM_STR);
    $stmt->bindValue(':email', $_SESSION['email'], PDO::PARAM_STR);
    $stmt->bindValue(':tags', 'gradient, wallpaper, background', PDO::PARAM_STR);
    $stmt->bindValue(':title', 'Gradient Image ' . ($i + 25), PDO::PARAM_STR);
    $stmt->bindValue(':imgdesc', 'Generated gradient image', PDO::PARAM_STR);
    $stmt->bindValue(':date', date('Y-m-d H:i:s'), PDO::PARAM_STR);
    $stmt->bindValue(':type', 'safe', PDO::PARAM_STR);
    $stmt->bindValue(':artwork_type', 'manga', PDO::PARAM_STR);
    $stmt->bindValue(':episode_name', 'Gradient Image ' . ($i + 25), PDO::PARAM_STR);
    $stmt->execute();

    // Insert into image_child table
    $imageId = $db->lastInsertId(); // Get the ID of the inserted image
    $stmt = $db->prepare('INSERT INTO image_child (filename, image_id, email) VALUES (:filename, :image_id, :email)');
    $stmt->bindValue(':filename', $fileName, PDO::PARAM_STR);
    $stmt->bindValue(':image_id', $imageId, PDO::PARAM_INT);
    $stmt->bindValue(':email', $_SESSION['email'], PDO::PARAM_STR);
    $stmt->execute();
  }

  echo "48 gradient images have been generated and uploaded.";
}

// Redirect to home
header("Location: /install/generate_all_profile_pictures.php");
exit();

$db = null; // Close the PDO connection
?>