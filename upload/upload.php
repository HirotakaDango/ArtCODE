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

// Check if any images were uploaded
if (isset($_FILES['image'])) {

  ob_start(); // Start output buffering to prevent header errors

  $images = $_FILES['image'];

  // Determine today's date for folder structure
  $dateFolder = date('Y/m/d');
  $uploadDir = '../images/' . $dateFolder . '/';
  $thumbnailDir = '../thumbnails/' . $dateFolder . '/';

  // Create directories if they don't exist
  if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
  }
  if (!is_dir($thumbnailDir)) {
    mkdir($thumbnailDir, 0755, true);
  }

  // Generate a unique file name for the main image
  $ext = pathinfo($images['name'][0], PATHINFO_EXTENSION);
  $filename = $dateFolder . '/' . uniqid() . '.' . $ext;
  $originalFilename = basename($images['name'][0]);

  // Save the main image
  move_uploaded_file($images['tmp_name'][0], $uploadDir . basename($filename));

  // Determine the image type and generate the thumbnail
  $image_info = getimagesize($uploadDir . basename($filename));
  $mime_type = $image_info['mime'];
  switch ($mime_type) {
    case 'image/jpeg':
      $source = imagecreatefromjpeg($uploadDir . basename($filename));
      break;
    case 'image/png':
      $source = imagecreatefrompng($uploadDir . basename($filename));
      break;
    case 'image/gif':
      $source = imagecreatefromgif($uploadDir . basename($filename));
      break;
    case 'image/webp':
      $source = imagecreatefromwebp($uploadDir . basename($filename));
      break;
    case 'image/avif':
      $source = imagecreatefromavif($uploadDir . basename($filename));
      break;
    case 'image/bmp':
      $source = imagecreatefrombmp($uploadDir . basename($filename));
      break;
    case 'image/wbmp':
      $source = imagecreatefromwbmp($uploadDir . basename($filename));
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
      imagejpeg($thumbnail, $thumbnailDir . basename($filename));
      break;
    case 'png':
      imagepng($thumbnail, $thumbnailDir . basename($filename));
      break;
    case 'gif':
      imagegif($thumbnail, $thumbnailDir . basename($filename));
      break;
    case 'webp':
      imagewebp($thumbnail, $thumbnailDir . basename($filename));
      break;
    case 'avif':
      imageavif($thumbnail, $thumbnailDir . basename($filename));
      break;
    case 'bmp':
      imagebmp($thumbnail, $thumbnailDir . basename($filename));
      break;
    case 'wbmp': 
      imagewbmp($thumbnail, $thumbnailDir . basename($filename));
      break;
    default:
      echo "Error: Unsupported image format.";
      exit;
  }

  // Add the main image to the "images" table
  $email = $_SESSION['email'];
  $tags = filter_var($_POST['tags'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $tags = explode(",", $tags);
  $tags = array_map('trim', $tags); // Remove extra white space from each tag
  $tags = array_filter($tags); // Remove any empty tags
  $tags = array_values($tags); // Reset array indexes
  $tags = implode(",", $tags); // Join tags by comma

  $parodies = filter_var($_POST['parodies'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $parodies = explode(",", $parodies);
  $parodies = array_map('trim', $parodies); // Remove extra white space from each tag
  $parodies = array_filter($parodies); // Remove any empty parodies
  $parodies = array_values($parodies); // Reset array indexes
  $parodies = implode(",", $parodies); // Join parodies by comma

  $characters = filter_var($_POST['characters'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $characters = explode(",", $characters);
  $characters = array_map('trim', $characters); // Remove extra white space from each tag
  $characters = array_filter($characters); // Remove any empty characters
  $characters = array_values($characters); // Reset array indexes
  $characters = implode(",", $characters); // Join characters by comma

  $date = date('Y-m-d'); // Get the current date in YYYY-MM-DD format

  $stmt = $db->prepare("INSERT INTO images (email, filename, original_filename, tags, title, imgdesc, link, date, type, episode_name, artwork_type, `group`, categories, language, parodies, characters) VALUES (:email, :filename, :original_filename, :tags, :title, :imgdesc, :link, :date, :type, :episode_name, :artwork_type, :group, :categories, :language, :parodies, :characters)");
  $stmt->bindValue(':email', $email);
  $stmt->bindValue(':filename', $filename);
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
  $stmt->bindValue(':parodies', filter_var($_POST['parodies'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));
  $stmt->bindValue(':characters', filter_var($_POST['characters'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));
  $stmt->execute();

  // Retrieve the ID of the inserted image
  $image_id = $db->lastInsertRowID();

  // Loop through each uploaded image (except the first one)
  for ($i = 1; $i < count($images['name']); $i++) {
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
      $child_filename = $dateFolder . '/' . uniqid() . '.' . $ext;
      $child_originalFilename = basename($image['name']);

      // Save the child image
      move_uploaded_file($image['tmp_name'], $uploadDir . basename($child_filename));

      // Generate thumbnail for child image
      $image_info = getimagesize($uploadDir . basename($child_filename));
      $mime_type = $image_info['mime'];
      switch ($mime_type) {
        case 'image/jpeg':
          $source = imagecreatefromjpeg($uploadDir . basename($child_filename));
          break;
        case 'image/png':
          $source = imagecreatefrompng($uploadDir . basename($child_filename));
          break;
        case 'image/gif':
          $source = imagecreatefromgif($uploadDir . basename($child_filename));
          break;
        case 'image/webp':
          $source = imagecreatefromwebp($uploadDir . basename($child_filename));
          break;
        case 'image/avif':
          $source = imagecreatefromavif($uploadDir . basename($child_filename));
          break;
        case 'image/bmp':
          $source = imagecreatefrombmp($uploadDir . basename($child_filename));
          break;
        case 'image/wbmp':
          $source = imagecreatefromwbmp($uploadDir . basename($child_filename));
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
          imagejpeg($thumbnail, $thumbnailDir . basename($child_filename));
          break;
        case 'png':
          imagepng($thumbnail, $thumbnailDir . basename($child_filename));
          break;
        case 'gif':
          imagegif($thumbnail, $thumbnailDir . basename($child_filename));
          break;
        case 'webp':
          imagewebp($thumbnail, $thumbnailDir . basename($child_filename));
          break;
        case 'avif':
          imageavif($thumbnail, $thumbnailDir . basename($child_filename));
          break;
        case 'bmp':
          imagebmp($thumbnail, $thumbnailDir . basename($child_filename));
          break;
        case 'wbmp': 
          imagewbmp($thumbnail, $thumbnailDir . basename($child_filename));
          break;
        default:
          echo "Error: Unsupported image format.";
          exit;
      }

      // Add the child image to the "image_child" table, associating it with the main image's ID
      $stmt = $db->prepare("INSERT INTO image_child (filename, original_filename, image_id, email) VALUES (:filename, :original_filename, :image_id, :email)");
      $stmt->bindValue(':filename', $child_filename);
      $stmt->bindValue(':original_filename', $child_originalFilename);
      $stmt->bindValue(':image_id', $image_id);
      $stmt->bindValue(':email', $email);
      $stmt->execute();
    } else {
      echo "Error uploading image.";
    }
  }

  header("Location: index.php");
  exit;
}
?>