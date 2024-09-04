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

// Function to generate and save child images
function generateChildImages($mainImageId, $childCount = 10) {
  global $db;

  $width = 2500;
  $height = 1400;

  // Define directory for child images
  $dateFolder = date('Y/m/d');
  $childImageDir = '../images/' . $dateFolder . '/';
  $thumbnailDir = '../thumbnails/' . $dateFolder . '/';

  if (!is_dir($childImageDir)) {
    mkdir($childImageDir, 0755, true);
  }
  if (!is_dir($thumbnailDir)) {
    mkdir($thumbnailDir, 0755, true);
  }

  for ($i = 0; $i < $childCount; $i++) {
    $image = createGradientImage($width, $height);

    // Define paths
    $fileName = uniqid('child_img_') . '.png';
    $originalPath = $childImageDir . $fileName;
    $thumbnailPath = $thumbnailDir . $fileName;

    // Save image and thumbnail
    saveImageAndThumbnail($image, $originalPath, $thumbnailPath);

    // Insert metadata into the database
    $stmt = $db->prepare('INSERT INTO image_child (filename, image_id, email, original_filename) VALUES (:filename, :image_id, :email, :original_filename)');
    $stmt->bindValue(':filename', $dateFolder . '/' . $fileName, PDO::PARAM_STR);
    $stmt->bindValue(':image_id', $mainImageId, PDO::PARAM_INT);
    $stmt->bindValue(':email', $_SESSION['email'], PDO::PARAM_STR);
    $stmt->bindValue(':original_filename', $fileName, PDO::PARAM_STR);
    $stmt->execute();
  }
}

// Check if there are any images in the database
$query = 'SELECT COUNT(*) FROM images';
$statement = $db->query($query);
$imageCount = $statement->fetchColumn();

if ($imageCount == 0) {
  // Define the image dimensions
  $width = 2500;
  $height = 1400;

  // Generate 12 images for illustration
  for ($i = 0; $i < 12; $i++) {
    // Generate a new gradient image
    $image = createGradientImage($width, $height);

    // Define paths
    $dateFolder = date('Y/m/d');
    $imageDir = '../images/' . $dateFolder . '/';
    $thumbnailDir = '../thumbnails/' . $dateFolder . '/';

    if (!is_dir($imageDir)) {
      mkdir($imageDir, 0755, true);
    }
    if (!is_dir($thumbnailDir)) {
      mkdir($thumbnailDir, 0755, true);
    }

    $fileName = uniqid('img_') . '.png';
    $originalPath = $imageDir . $fileName;
    $thumbnailPath = $thumbnailDir . $fileName;

    // Save image and thumbnail
    saveImageAndThumbnail($image, $originalPath, $thumbnailPath);

    // Insert metadata into the database
    $stmt = $db->prepare('INSERT INTO images (filename, email, tags, title, imgdesc, date, type, artwork_type, original_filename) VALUES (:filename, :email, :tags, :title, :imgdesc, :date, :type, :artwork_type, :original_filename)');
    $stmt->bindValue(':filename', $dateFolder . '/' . $fileName, PDO::PARAM_STR);
    $stmt->bindValue(':email', $_SESSION['email'], PDO::PARAM_STR);
    $stmt->bindValue(':tags', 'gradient, wallpaper, background', PDO::PARAM_STR);
    $stmt->bindValue(':title', 'Gradient Image ' . ($i + 1), PDO::PARAM_STR);
    $stmt->bindValue(':imgdesc', 'Generated gradient image', PDO::PARAM_STR);
    $stmt->bindValue(':date', date('Y-m-d H:i:s'), PDO::PARAM_STR);
    $stmt->bindValue(':type', 'safe', PDO::PARAM_STR);
    $stmt->bindValue(':artwork_type', 'illustration', PDO::PARAM_STR);
    $stmt->bindValue(':original_filename', $fileName, PDO::PARAM_STR);
    $stmt->execute();

    // Get the ID of the inserted image
    $mainImageId = $db->lastInsertId();

    // Generate and save child images
    generateChildImages($mainImageId, 4);
  }

  // Generate 12 images for manga
  for ($i = 0; $i < 12; $i++) {
    // Generate a new gradient image
    $image = createGradientImage($width, $height);

    // Define paths
    $dateFolder = date('Y/m/d');
    $imageDir = '../images/' . $dateFolder . '/';
    $thumbnailDir = '../thumbnails/' . $dateFolder . '/';

    if (!is_dir($imageDir)) {
      mkdir($imageDir, 0755, true);
    }
    if (!is_dir($thumbnailDir)) {
      mkdir($thumbnailDir, 0755, true);
    }

    $fileName = uniqid('img_') . '.png';
    $originalPath = $imageDir . $fileName;
    $thumbnailPath = $thumbnailDir . $fileName;

    // Save image and thumbnail
    saveImageAndThumbnail($image, $originalPath, $thumbnailPath);

    // Insert metadata into the database
    $stmt = $db->prepare('INSERT INTO images (filename, email, tags, title, imgdesc, date, type, artwork_type, episode_name, original_filename) VALUES (:filename, :email, :tags, :title, :imgdesc, :date, :type, :artwork_type, :episode_name, :original_filename)');
    $stmt->bindValue(':filename', $dateFolder . '/' . $fileName, PDO::PARAM_STR);
    $stmt->bindValue(':email', $_SESSION['email'], PDO::PARAM_STR);
    $stmt->bindValue(':tags', 'gradient, wallpaper, background', PDO::PARAM_STR);
    $stmt->bindValue(':title', 'Gradient Image ' . ($i + 25), PDO::PARAM_STR);
    $stmt->bindValue(':imgdesc', 'Generated gradient image', PDO::PARAM_STR);
    $stmt->bindValue(':date', date('Y-m-d H:i:s'), PDO::PARAM_STR);
    $stmt->bindValue(':type', 'safe', PDO::PARAM_STR);
    $stmt->bindValue(':artwork_type', 'manga', PDO::PARAM_STR);
    $stmt->bindValue(':episode_name', 'Gradient Image ' . ($i + 25), PDO::PARAM_STR);
    $stmt->bindValue(':original_filename', $fileName, PDO::PARAM_STR);
    $stmt->execute();

    // Get the ID of the inserted image
    $mainImageId = $db->lastInsertId();

    // Generate and save child images
    generateChildImages($mainImageId, 4);
  }

  echo "48 gradient images with 10 child images each have been generated and uploaded.";
}

// Redirect to home
header("Location: /install/generate_all_profile_pictures.php");
exit();

$db = null; // Close the PDO connection
?>