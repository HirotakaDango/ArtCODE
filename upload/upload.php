<?php
require_once('../auth.php');

// Connect to the SQLite database
$db = new SQLite3('../database.sqlite');

// Create the "images" table if it doesn't exist, adding the "original_filename" column
$db->exec("
  CREATE TABLE IF NOT EXISTS images (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL,
    filename TEXT NOT NULL,
    original_filename TEXT NOT NULL,
    tags TEXT,
    title TEXT,
    imgdesc TEXT,
    link TEXT,
    date DATETIME,
    type TEXT,
    episode_name TEXT,
    artwork_type TEXT,
    `group` TEXT,
    categories TEXT,
    language TEXT,
    parodies TEXT,
    characters TEXT
  )
");

// Create the "image_child" table if it doesn't exist, adding the "original_filename" column
$db->exec("
  CREATE TABLE IF NOT EXISTS image_child (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    filename TEXT NOT NULL,
    original_filename TEXT NOT NULL,
    image_id INTEGER NOT NULL,
    email TEXT NOT NULL,
    FOREIGN KEY (image_id) REFERENCES images (id)
  )
");

function generateUniqueImageId() {
  return bin2hex(random_bytes(6)); // Generates a 12-character hex string
}

// Check if any images were uploaded
if (isset($_FILES['image'])) {
  ob_start(); // Start output buffering to prevent header errors

  $images = $_FILES['image'];
  $email = $_SESSION['email'];

  // Get user ID from email
  $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
  $stmt->bindValue(':email', $email);
  $result = $stmt->execute();
  $user = $result->fetchArray();
  
  if (!$user || !isset($user['id'])) {
    die("Error: Unable to find user ID");
  }
  
  $user_id = $user['id'];

  // Generate unique ID for this upload batch
  $uniqueId = generateUniqueImageId();

  // Base directory for uploads
  $uploadDir = '../images/';
  $thumbnailDir = '../thumbnails/';

  // Create directories if they don't exist
  if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
  }
  if (!is_dir($thumbnailDir)) {
    mkdir($thumbnailDir, 0755, true);
  }

  // Process main image
  $ext = pathinfo($images['name'][0], PATHINFO_EXTENSION);
  $originalFilename = basename($images['name'][0]);

  // First, insert into database to get image_id
  $tags = filter_var($_POST['tags'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $tags = implode(",", array_values(array_filter(array_map('trim', explode(",", $tags)))));

  $parodies = filter_var($_POST['parodies'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $parodies = implode(",", array_values(array_filter(array_map('trim', explode(",", $parodies)))));

  $characters = filter_var($_POST['characters'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $characters = implode(",", array_values(array_filter(array_map('trim', explode(",", $characters)))));

  // Insert main image into database
  $stmt = $db->prepare("INSERT INTO images (email, filename, original_filename, tags, title, imgdesc, link, date, type, episode_name, artwork_type, `group`, categories, language, parodies, characters) VALUES (:email, :filename, :original_filename, :tags, :title, :imgdesc, :link, :date, :type, :episode_name, :artwork_type, :group, :categories, :language, :parodies, :characters)");

  // Initial filename placeholder
  $initial_filename = "uid_" . $user_id . "/data/imageid-0/imageassets_" . $uniqueId . "/" . $uniqueId . "_i0." . $ext;
  
  $date = date('Y-m-d'); // Get the current date in YYYY-MM-DD format

  $stmt->bindValue(':email', $email);
  $stmt->bindValue(':filename', $initial_filename);
  $stmt->bindValue(':original_filename', $originalFilename);
  $stmt->bindValue(':tags', $tags);
  $stmt->bindValue(':title', filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));
  $stmt->bindValue(':imgdesc', nl2br(filter_var($_POST['imgdesc'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW)));
  $stmt->bindValue(':link', filter_var($_POST['link'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));
  $stmt->bindValue(':date', $date);
  $stmt->bindValue(':type', filter_var($_POST['type'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));
  $stmt->bindValue(':episode_name', filter_var($_POST['episode_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));
  $stmt->bindValue(':artwork_type', filter_var($_POST['artwork_type'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));
  $stmt->bindValue(':group', filter_var($_POST['group'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));
  $stmt->bindValue(':categories', filter_var($_POST['categories'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));
  $stmt->bindValue(':language', filter_var($_POST['language'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));
  $stmt->bindValue(':parodies', $parodies);
  $stmt->bindValue(':characters', $characters);

  $stmt->execute();
  
  // Get the ID of the inserted image
  $image_id = $db->lastInsertRowID();

  // Now create the final filename with the correct image_id
  $filename = "uid_" . $user_id . "/data/imageid-" . $image_id . "/imageassets_" . $uniqueId . "/" . $uniqueId . "_i0." . $ext;
  
  // Update the filename with the actual image_id
  $stmt = $db->prepare("UPDATE images SET filename = :filename WHERE id = :id");
  $stmt->bindValue(':filename', $filename);
  $stmt->bindValue(':id', $image_id);
  $stmt->execute();

  // Ensure the directory structure exists
  $uploadPath = dirname($uploadDir . $filename);
  if (!is_dir($uploadPath)) {
    mkdir($uploadPath, 0755, true);
  }
  $thumbnailPath = dirname($thumbnailDir . $filename);
  if (!is_dir($thumbnailPath)) {
    mkdir($thumbnailPath, 0755, true);
  }

  // Save the main image
  move_uploaded_file($images['tmp_name'][0], $uploadDir . $filename);

  // Process the main image and create thumbnail
  $image_info = getimagesize($uploadDir . $filename);
  $mime_type = $image_info['mime'];

  // Create source image based on mime type
  switch ($mime_type) {
    case 'image/jpeg':
      $source = imagecreatefromjpeg($uploadDir . $filename);
      break;
    case 'image/png':
      $source = imagecreatefrompng($uploadDir . $filename);
      break;
    case 'image/gif':
      $source = imagecreatefromgif($uploadDir . $filename);
      break;
    case 'image/webp':
      $source = imagecreatefromwebp($uploadDir . $filename);
      break;
    case 'image/avif':
      $source = imagecreatefromavif($uploadDir . $filename);
      break;
    case 'image/bmp':
      $source = imagecreatefrombmp($uploadDir . $filename);
      break;
    case 'image/wbmp':
      $source = imagecreatefromwbmp($uploadDir . $filename);
      break;
    default:
      echo "Error: Unsupported image format.";
      exit;
  }

  if ($source === false) {
    echo "Error: Failed to create image source.";
    exit;
  }

  // Create thumbnail
  $original_width = imagesx($source);
  $original_height = imagesy($source);
  $ratio = $original_width / $original_height;
  $thumbnail_width = 300;
  $thumbnail_height = intval(300 / $ratio);

  $thumbnail = imagecreatetruecolor($thumbnail_width, $thumbnail_height);

  if ($thumbnail === false) {
    echo "Error: Failed to create thumbnail.";
    exit;
  }

  imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $original_width, $original_height);

  // Save thumbnail based on extension
  switch ($ext) {
    case 'jpg':
    case 'jpeg':
      imagejpeg($thumbnail, $thumbnailDir . $filename);
      break;
    case 'png':
      imagepng($thumbnail, $thumbnailDir . $filename);
      break;
    case 'gif':
      imagegif($thumbnail, $thumbnailDir . $filename);
      break;
    case 'webp':
      imagewebp($thumbnail, $thumbnailDir . $filename);
      break;
    case 'avif':
      imageavif($thumbnail, $thumbnailDir . $filename);
      break;
    case 'bmp':
      imagebmp($thumbnail, $thumbnailDir . $filename);
      break;
    case 'wbmp':
      imagewbmp($thumbnail, $thumbnailDir . $filename);
      break;
    default:
      echo "Error: Unsupported image format.";
      exit;
  }

  // Process additional images
  for ($i = 1; $i < count($images['name']); $i++) {
    $image = array(
      'name' => $images['name'][$i],
      'type' => $images['type'][$i],
      'tmp_name' => $images['tmp_name'][$i],
      'error' => $images['error'][$i],
      'size' => $images['size'][$i]
    );

    if ($image['error'] == 0) {
      $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
      $child_filename = "uid_" . $user_id . "/data/imageid-" . $image_id . "/imageassets_" . $uniqueId . "/" . $uniqueId . "_i" . $i . "." . $ext;
      $child_originalFilename = basename($image['name']);

      // Create directories if needed
      $uploadPath = dirname($uploadDir . $child_filename);
      if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
      }
      $thumbnailPath = dirname($thumbnailDir . $child_filename);
      if (!is_dir($thumbnailPath)) {
        mkdir($thumbnailPath, 0755, true);
      }

      // Save child image and create thumbnail
      move_uploaded_file($image['tmp_name'], $uploadDir . $child_filename);

      // Process child image thumbnail (same process as main image)
      $image_info = getimagesize($uploadDir . $child_filename);
      $mime_type = $image_info['mime'];

      switch ($mime_type) {
        case 'image/jpeg':
          $source = imagecreatefromjpeg($uploadDir . $child_filename);
          break;
        case 'image/png':
          $source = imagecreatefrompng($uploadDir . $child_filename);
          break;
        case 'image/gif':
          $source = imagecreatefromgif($uploadDir . $child_filename);
          break;
        case 'image/webp':
          $source = imagecreatefromwebp($uploadDir . $child_filename);
          break;
        case 'image/avif':
          $source = imagecreatefromavif($uploadDir . $child_filename);
          break;
        case 'image/bmp':
          $source = imagecreatefrombmp($uploadDir . $child_filename);
          break;
        case 'image/wbmp':
          $source = imagecreatefromwbmp($uploadDir . $child_filename);
          break;
        default:
          echo "Error: Unsupported image format.";
          continue;
      }

      if ($source === false) {
        echo "Error: Failed to create image source.";
        continue;
      }

      $original_width = imagesx($source);
      $original_height = imagesy($source);
      $ratio = $original_width / $original_height;
      $thumbnail_width = 300;
      $thumbnail_height = intval(300 / $ratio);

      $thumbnail = imagecreatetruecolor($thumbnail_width, $thumbnail_height);

      if ($thumbnail === false) {
        echo "Error: Failed to create thumbnail.";
        continue;
      }

      imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $original_width, $original_height);

      switch ($ext) {
        case 'jpg':
        case 'jpeg':
          imagejpeg($thumbnail, $thumbnailDir . $child_filename);
          break;
        case 'png':
          imagepng($thumbnail, $thumbnailDir . $child_filename);
          break;
        case 'gif':
          imagegif($thumbnail, $thumbnailDir . $child_filename);
          break;
        case 'webp':
          imagewebp($thumbnail, $thumbnailDir . $child_filename);
          break;
        case 'avif':
          imageavif($thumbnail, $thumbnailDir . $child_filename);
          break;
        case 'bmp':
          imagebmp($thumbnail, $thumbnailDir . $child_filename);
          break;
        case 'wbmp':
          imagewbmp($thumbnail, $thumbnailDir . $child_filename);
          break;
      }

      // Insert child image into database
      $stmt = $db->prepare("INSERT INTO image_child (filename, original_filename, image_id, email) VALUES (:filename, :original_filename, :image_id, :email)");
      $stmt->bindValue(':filename', $child_filename);
      $stmt->bindValue(':original_filename', $child_originalFilename);
      $stmt->bindValue(':image_id', $image_id);
      $stmt->bindValue(':email', $email);
      $stmt->execute();
    }
  }

  header("Location: index.php");
  exit;
}
?>