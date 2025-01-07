<?php
session_start();

if (!isset($_SESSION['email'])) {
  header("Location: /preview/home/");
  exit();
}

$db = new PDO('sqlite:../database.sqlite');

// Function to get user ID from email
function getUserId($db, $email) {
  $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
  $stmt->bindValue(':email', $email);
  $stmt->execute();
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  return $user ? $user['id'] : null;
}

function generateUniqueId() {
  return bin2hex(random_bytes(6)); // 12 character hex string
}

function createGradientImage($width, $height) {
  $image = imagecreatetruecolor($width, $height);
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

function saveImageAndThumbnail($image, $path, $thumbnailPath) {
  // Ensure directory exists
  $dir = dirname($path);
  if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
  }
  $thumbnailDir = dirname($thumbnailPath);
  if (!is_dir($thumbnailDir)) {
    mkdir($thumbnailDir, 0755, true);
  }

  // Save original image
  imagepng($image, $path);
  
  // Create thumbnail
  $thumbnailWidth = 400;
  $thumbnailHeight = round($thumbnailWidth * imagesy($image) / imagesx($image));
  $thumbnail = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);
  $originalWidth = imagesx($image);
  $originalHeight = imagesy($image);
  imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $thumbnailWidth, $thumbnailHeight, $originalWidth, $originalHeight);
  imagepng($thumbnail, $thumbnailPath);
  
  imagedestroy($thumbnail);
  imagedestroy($image);
}

function generateChildImages($mainImageId, $userId, $uniqueId, $childCount = 10) {
  global $db;

  $width = 2500;
  $height = 1400;
  
  for ($i = 1; $i <= $childCount; $i++) {
    $image = createGradientImage($width, $height);
    
    // Create paths using new structure
    $filename = "uid_" . $userId . "/data/imageid-" . $mainImageId . "/imageassets_" . $uniqueId . "/" . $uniqueId . "_i" . $i . ".png";
    $originalPath = "../images/" . $filename;
    $thumbnailPath = "../thumbnails/" . $filename;

    saveImageAndThumbnail($image, $originalPath, $thumbnailPath);

    // Insert into database
    $stmt = $db->prepare('INSERT INTO image_child (filename, image_id, email, original_filename) VALUES (:filename, :image_id, :email, :original_filename)');
    $stmt->bindValue(':filename', $filename);
    $stmt->bindValue(':image_id', $mainImageId);
    $stmt->bindValue(':email', $_SESSION['email']);
    $stmt->bindValue(':original_filename', $uniqueId . "_i" . $i . ".png");
    $stmt->execute();
  }
}

// Main execution
$userId = getUserId($db, $_SESSION['email']);
if (!$userId) {
  die("Error: User not found");
}

// Check if there are any images
$query = 'SELECT COUNT(*) FROM images';
$statement = $db->query($query);
$imageCount = $statement->fetchColumn();

if ($imageCount == 0) {
  $width = 2500;
  $height = 1400;

  // Generate illustration images
  for ($i = 0; $i < 12; $i++) {
    $uniqueId = generateUniqueId();
    $image = createGradientImage($width, $height);
    
    // Insert into database first to get the image ID
    $stmt = $db->prepare('INSERT INTO images (filename, email, tags, title, imgdesc, date, type, artwork_type, original_filename) VALUES (:filename, :email, :tags, :title, :imgdesc, :date, :type, :artwork_type, :original_filename)');
    $stmt->bindValue(':filename', 'placeholder'); // Temporary placeholder
    $stmt->bindValue(':email', $_SESSION['email']);
    $stmt->bindValue(':tags', 'gradient, wallpaper, background');
    $stmt->bindValue(':title', 'Gradient Image ' . ($i + 1));
    $stmt->bindValue(':imgdesc', 'Generated gradient image');
    $stmt->bindValue(':date', date('Y-m-d H:i:s'));
    $stmt->bindValue(':type', 'safe');
    $stmt->bindValue(':artwork_type', 'illustration');
    $stmt->bindValue(':original_filename', $uniqueId . "_i0.png");
    $stmt->execute();
    
    $mainImageId = $db->lastInsertId();
    
    // Create the actual filename with the correct image ID
    $filename = "uid_" . $userId . "/data/imageid-" . $mainImageId . "/imageassets_" . $uniqueId . "/" . $uniqueId . "_i0.png";
    $originalPath = "../images/" . $filename;
    $thumbnailPath = "../thumbnails/" . $filename;
    
    // Update the filename in the database
    $stmt = $db->prepare('UPDATE images SET filename = :filename WHERE id = :id');
    $stmt->bindValue(':filename', $filename);
    $stmt->bindValue(':id', $mainImageId);
    $stmt->execute();
    
    saveImageAndThumbnail($image, $originalPath, $thumbnailPath);
    
    // Generate child images
    generateChildImages($mainImageId, $userId, $uniqueId, 4);
  }

  // Generate manga images
  for ($i = 0; $i < 12; $i++) {
    $uniqueId = generateUniqueId();
    $image = createGradientImage($width, $height);
    
    // Insert into database first to get the image ID
    $stmt = $db->prepare('INSERT INTO images (filename, email, tags, title, imgdesc, date, type, artwork_type, episode_name, original_filename) VALUES (:filename, :email, :tags, :title, :imgdesc, :date, :type, :artwork_type, :episode_name, :original_filename)');
    $stmt->bindValue(':filename', 'placeholder');
    $stmt->bindValue(':email', $_SESSION['email']);
    $stmt->bindValue(':tags', 'gradient, wallpaper, background');
    $stmt->bindValue(':title', 'Gradient Image ' . ($i + 25));
    $stmt->bindValue(':imgdesc', 'Generated gradient image');
    $stmt->bindValue(':date', date('Y-m-d H:i:s'));
    $stmt->bindValue(':type', 'safe');
    $stmt->bindValue(':artwork_type', 'manga');
    $stmt->bindValue(':episode_name', 'Gradient Image ' . ($i + 25));
    $stmt->bindValue(':original_filename', $uniqueId . "_i0.png");
    $stmt->execute();
    
    $mainImageId = $db->lastInsertId();
    
    // Create the actual filename with the correct image ID
    $filename = "uid_" . $userId . "/data/imageid-" . $mainImageId . "/imageassets_" . $uniqueId . "/" . $uniqueId . "_i0.png";
    $originalPath = "../images/" . $filename;
    $thumbnailPath = "../thumbnails/" . $filename;
    
    // Update the filename in the database
    $stmt = $db->prepare('UPDATE images SET filename = :filename WHERE id = :id');
    $stmt->bindValue(':filename', $filename);
    $stmt->bindValue(':id', $mainImageId);
    $stmt->execute();
    
    saveImageAndThumbnail($image, $originalPath, $thumbnailPath);
    
    // Generate child images
    generateChildImages($mainImageId, $userId, $uniqueId, 4);
  }

  echo "48 gradient images with 4 child images each have been generated and uploaded.";
}

header("Location: /install/generate_all_profile_pictures.php");
exit();

$db = null;
?>