<?php
require_once('../auth.php');

// Connect to the SQLite database
$db = new SQLite3('../database.sqlite');

// Create the "images" table if it doesn't exist, including the "original_filename" column
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

// Create the "image_child" table if it doesn't exist, including the "original_filename" column
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

  // Base directories for uploads and thumbnails
  $uploadDir = '../images/';
  $thumbnailDir = '../thumbnails/';

  // Create directories if they don't exist
  if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
  }
  if (!is_dir($thumbnailDir)) {
    mkdir($thumbnailDir, 0755, true);
  }

  // Process main image (first file)
  $ext = pathinfo($images['name'][0], PATHINFO_EXTENSION);
  $originalFilename = basename($images['name'][0]);

  // Prepare tags, parodies, and characters
  $tags = filter_var($_POST['tags'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $tags = implode(",", array_values(array_filter(array_map('trim', explode(",", $tags)))));

  $parodies = filter_var($_POST['parodies'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $parodies = implode(",", array_values(array_filter(array_map('trim', explode(",", $parodies)))));

  $characters = filter_var($_POST['characters'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $characters = implode(",", array_values(array_filter(array_map('trim', explode(",", $characters)))));

  // Insert main image into the database with an initial placeholder filename
  $stmt = $db->prepare("
    INSERT INTO images 
      (email, filename, original_filename, tags, title, imgdesc, link, date, type, episode_name, artwork_type, `group`, categories, language, parodies, characters) 
    VALUES 
      (:email, :filename, :original_filename, :tags, :title, :imgdesc, :link, :date, :type, :episode_name, :artwork_type, :group, :categories, :language, :parodies, :characters)
  ");

  $initial_filename = "uid_" . $user_id . "/data/imageid-0/imageassets_" . $uniqueId . "/" . $uniqueId . "_i0." . $ext;
  $date = date('Y-m-d');

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
  
  // Get the inserted image's ID
  $image_id = $db->lastInsertRowID();

  // Now update the filename with the actual image_id
  $filename = "uid_" . $user_id . "/data/imageid-" . $image_id . "/imageassets_" . $uniqueId . "/" . $uniqueId . "_i0." . $ext;
  $stmt = $db->prepare("UPDATE images SET filename = :filename WHERE id = :id");
  $stmt->bindValue(':filename', $filename);
  $stmt->bindValue(':id', $image_id);
  $stmt->execute();

  // Ensure directory structure exists for main image
  $uploadPath = dirname($uploadDir . $filename);
  if (!is_dir($uploadPath)) {
    mkdir($uploadPath, 0755, true);
  }
  $thumbPath = dirname($thumbnailDir . $filename);
  if (!is_dir($thumbPath)) {
    mkdir($thumbPath, 0755, true);
  }

  // Save the main image file
  if (!move_uploaded_file($images['tmp_name'][0], $uploadDir . $filename)) {
    echo "Error: Failed to move main image.";
    exit;
  }

  // Process the main image to create a thumbnail
  $image_info = getimagesize($uploadDir . $filename);
  $mime_type = $image_info['mime'];

  // Create source image using the restored switch
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
      $source = function_exists('imagecreatefromavif') ? imagecreatefromavif($uploadDir . $filename) : null;
      break;
    case 'image/bmp':
      $source = imagecreatefrombmp($uploadDir . $filename);
      break;
    case 'image/wbmp':
      $source = imagecreatefromwbmp($uploadDir . $filename);
      break;
    default:
      $source = null;
  }

  if (!$source) {
    echo "Error: Failed to create image source.";
    exit;
  }

  // Create thumbnail image resource
  $original_width = imagesx($source);
  $original_height = imagesy($source);
  $ratio = $original_width / $original_height;
  $thumbnail_width = 300;
  $thumbnail_height = intval(300 / $ratio);

  $thumbnail = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
  if (!$thumbnail) {
    echo "Error: Failed to create thumbnail.";
    exit;
  }

  imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $original_width, $original_height);

  // Save main thumbnail using match expression (PHP 8)
  $result = match (strtolower($ext)) {
    'jpg', 'jpeg' => imagejpeg($thumbnail, $thumbnailDir . $filename),
    'png'  => imagepng($thumbnail, $thumbnailDir . $filename),
    'gif'  => imagegif($thumbnail, $thumbnailDir . $filename),
    'webp' => imagewebp($thumbnail, $thumbnailDir . $filename),
    'avif' => (function_exists('imageavif') ? imageavif($thumbnail, $thumbnailDir . $filename) : false),
    'bmp'  => imagebmp($thumbnail, $thumbnailDir . $filename),
    'wbmp' => imagewbmp($thumbnail, $thumbnailDir . $filename),
    default => false,
  };

  if ($result === false) {
    echo "Error: Failed to save main image thumbnail.";
    exit;
  }

  // Process additional (child) images if any
  for ($i = 1; $i < count($images['name']); $i++) {
    $image = [
      'name'     => $images['name'][$i],
      'type'     => $images['type'][$i],
      'tmp_name' => $images['tmp_name'][$i],
      'error'    => $images['error'][$i],
      'size'     => $images['size'][$i]
    ];

    if ($image['error'] === 0) {
      $childExt = pathinfo($image['name'], PATHINFO_EXTENSION);
      $child_filename = "uid_" . $user_id . "/data/imageid-" . $image_id . "/imageassets_" . $uniqueId . "/" . $uniqueId . "_i" . $i . "." . $childExt;
      $child_originalFilename = basename($image['name']);

      // Ensure directory structure exists for child image
      $childUploadPath = dirname($uploadDir . $child_filename);
      if (!is_dir($childUploadPath)) {
        mkdir($childUploadPath, 0755, true);
      }
      $childThumbPath = dirname($thumbnailDir . $child_filename);
      if (!is_dir($childThumbPath)) {
        mkdir($childThumbPath, 0755, true);
      }

      // Save the child image file
      if (!move_uploaded_file($image['tmp_name'], $uploadDir . $child_filename)) {
        echo "Error: Failed to move child image $i.";
        continue;
      }

      // Get image information and mime type for the child image
      $child_info = getimagesize($uploadDir . $child_filename);
      $child_mime = $child_info['mime'];

      // Create source image for child image using the restored switch
      switch ($child_mime) {
        case 'image/jpeg':
          $childSource = imagecreatefromjpeg($uploadDir . $child_filename);
          break;
        case 'image/png':
          $childSource = imagecreatefrompng($uploadDir . $child_filename);
          break;
        case 'image/gif':
          $childSource = imagecreatefromgif($uploadDir . $child_filename);
          break;
        case 'image/webp':
          $childSource = imagecreatefromwebp($uploadDir . $child_filename);
          break;
        case 'image/avif':
          $childSource = function_exists('imagecreatefromavif') ? imagecreatefromavif($uploadDir . $child_filename) : null;
          break;
        case 'image/bmp':
          $childSource = imagecreatefrombmp($uploadDir . $child_filename);
          break;
        case 'image/wbmp':
          $childSource = imagecreatefromwbmp($uploadDir . $child_filename);
          break;
        default:
          echo "Error: Unsupported child image format for image $i.";
          continue 2; // Skip to next image in the loop
      }

      if (!$childSource) {
        echo "Error: Failed to create source for child image $i.";
        continue;
      }

      // Create thumbnail for child image
      $child_original_width = imagesx($childSource);
      $child_original_height = imagesy($childSource);
      $child_ratio = $child_original_width / $child_original_height;
      $child_thumb_width = 300;
      $child_thumb_height = intval(300 / $child_ratio);

      $childThumbnail = imagecreatetruecolor($child_thumb_width, $child_thumb_height);
      if (!$childThumbnail) {
        echo "Error: Failed to create thumbnail for child image $i.";
        continue;
      }

      imagecopyresampled($childThumbnail, $childSource, 0, 0, 0, 0, $child_thumb_width, $child_thumb_height, $child_original_width, $child_original_height);

      // Save child thumbnail using a switch (you can also use match if preferred)
      switch (strtolower($childExt)) {
        case 'jpg':
        case 'jpeg':
          if (!imagejpeg($childThumbnail, $thumbnailDir . $child_filename)) {
            echo "Error: Failed to save child thumbnail $i.";
            continue 2;
          }
          break;
        case 'png':
          if (!imagepng($childThumbnail, $thumbnailDir . $child_filename)) {
            echo "Error: Failed to save child thumbnail $i.";
            continue 2;
          }
          break;
        case 'gif':
          if (!imagegif($childThumbnail, $thumbnailDir . $child_filename)) {
            echo "Error: Failed to save child thumbnail $i.";
            continue 2;
          }
          break;
        case 'webp':
          if (!imagewebp($childThumbnail, $thumbnailDir . $child_filename)) {
            echo "Error: Failed to save child thumbnail $i.";
            continue 2;
          }
          break;
        case 'avif':
          if (function_exists('imageavif')) {
            if (!imageavif($childThumbnail, $thumbnailDir . $child_filename)) {
              echo "Error: Failed to save child thumbnail $i.";
              continue 2;
            }
          } else {
            echo "Error: AVIF not supported for child image $i.";
            continue 2;
          }
          break;
        case 'bmp':
          if (!imagebmp($childThumbnail, $thumbnailDir . $child_filename)) {
            echo "Error: Failed to save child thumbnail $i.";
            continue 2;
          }
          break;
        case 'wbmp':
          if (!imagewbmp($childThumbnail, $thumbnailDir . $child_filename)) {
            echo "Error: Failed to save child thumbnail $i.";
            continue 2;
          }
          break;
        default:
          echo "Error: Unsupported file extension for child image $i.";
          continue 2;
      }

      // Insert child image information into the database
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