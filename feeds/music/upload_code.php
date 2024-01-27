<?php
require_once('auth.php');

// Connect to SQLite database
$db = new SQLite3('../../database.sqlite');

$email = $_SESSION['email'];

// Create music table if not exists
$query = "CREATE TABLE IF NOT EXISTS music (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  file TEXT,
  email TEXT,
  cover TEXT,
  album TEXT,
  title TEXT,
  lyrics TEXT,
  description TEXT
)";
$db->exec($query);

// Function to resize image to default size and maintain 1x1 aspect ratio
function resizeImage($sourceFile, $targetFile, $width, $height) {
  list($sourceWidth, $sourceHeight, $sourceType) = getimagesize($sourceFile);

  $sourceAspectRatio = $sourceWidth / $sourceHeight;
  $targetAspectRatio = $width / $height;

  if ($sourceAspectRatio > $targetAspectRatio) {
    $targetHeight = $height;
    $targetWidth = $height * $sourceAspectRatio;
  } else {
    $targetWidth = $width;
    $targetHeight = $width / $sourceAspectRatio;
  }

  $sourceImage = null;

  switch ($sourceType) {
    case IMAGETYPE_JPEG:
      $sourceImage = imagecreatefromjpeg($sourceFile);
      break;
    case IMAGETYPE_PNG:
      $sourceImage = imagecreatefrompng($sourceFile);
      break;
    case IMAGETYPE_GIF:
      $sourceImage = imagecreatefromgif($sourceFile);
      break;
    case IMAGETYPE_WEBP:
      $sourceImage = imagecreatefromwebp($sourceFile);
      break;
    default:
      // Unsupported image type
      return false;
  }

  $targetImage = imagecreatetruecolor($width, $height);

  // Resize image to fit the square canvas using object fit cover
  $offsetX = 0;
  $offsetY = 0;

  if ($sourceAspectRatio > $targetAspectRatio) {
    $offsetX = ($targetWidth - $width) / 2;
  } elseif ($sourceAspectRatio < $targetAspectRatio) {
    $offsetY = ($targetHeight - $height) / 2;
  }

  imagecopyresampled($targetImage, $sourceImage, -$offsetX, -$offsetY, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

  switch ($sourceType) {
    case IMAGETYPE_JPEG:
      imagejpeg($targetImage, $targetFile);
      break;
    case IMAGETYPE_PNG:
      imagepng($targetImage, $targetFile);
      break;
    case IMAGETYPE_GIF:
      imagegif($targetImage, $targetFile);
      break;
    case IMAGETYPE_WEBP:
      imagewebp($targetImage, $targetFile);
      break;
  }

  imagedestroy($sourceImage);
  imagedestroy($targetImage);

  return true;
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Check if the form was submitted
  if (
    isset($_FILES['image']) && !empty($_FILES['image']['name']) &&
    isset($_FILES['musicFile']) && !empty($_FILES['musicFile']['name']) &&
    isset($_POST['album']) && !empty($_POST['album']) &&
    isset($_POST['title']) && !empty($_POST['title'])
  ) {
    $uploadDir = 'uploads/';
    $coverDir = 'covers/';

    // Create directories if not exist
    if (!file_exists($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }
    if (!file_exists($coverDir)) {
      mkdir($coverDir, 0777, true);
    }

    // Process uploaded image
    $originalImageName = basename($_FILES['image']['name']);
    $imageExtension = pathinfo($originalImageName, PATHINFO_EXTENSION);

    // Check if the file type is JPG, JPEG, or PNG
    if (in_array(strtolower($imageExtension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
      $uniqueImageName = uniqid() . '.' . $imageExtension;
      $imageFile = $coverDir . $uniqueImageName;
      $coverFile = 'cover_' . $uniqueImageName; // Adjusted to use only the file name

      // Move uploaded image to destination
      if (move_uploaded_file($_FILES['image']['tmp_name'], $imageFile)) {
        // Resize image to default size and maintain 1x1 aspect ratio
        resizeImage($imageFile, $coverDir . $coverFile, 1262, 1262);
      } else {
        // Output a JSON response indicating upload failure
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to upload the image.']);
        exit;
      }
    } else {
      // Output a JSON response indicating invalid file type
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, and PNG are allowed.']);
      exit;
    }

    // Process uploaded music file
    $originalMusicName = basename($_FILES['musicFile']['name']);
    $musicExtension = pathinfo($originalMusicName, PATHINFO_EXTENSION);

    if (strtolower($musicExtension) === 'mp3') {
      $uniqueMusicName = uniqid() . '.' . $musicExtension;
      $musicFile = $uploadDir . $uniqueMusicName;

      // Move uploaded music file to destination
      if (!move_uploaded_file($_FILES['musicFile']['tmp_name'], $musicFile)) {
        // Handle the case where moving the music file fails
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to upload the music file.']);
        exit;
      }
    } else {
      // Output a JSON response indicating invalid music file type
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Invalid music file type. Only MP3 is allowed.']);
      exit;
    }

    // Sanitize input data before using in SQL query
    $sanitizedAlbum = filter_var($_POST['album'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
    $sanitizedTitle = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
    $sanitizedLyrics = nl2br(filter_var($_POST['lyrics'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $sanitizedDescription = nl2br(filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    // Insert record into the database
    $stmt = $db->prepare("INSERT INTO music (file, email, cover, album, title, lyrics, description) VALUES (:file, :email, :cover, :album, :title, :lyrics, :description)");
    $stmt->bindValue(':file', $musicFile, SQLITE3_TEXT); // Use the uploaded music file
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':cover', $coverFile, SQLITE3_TEXT);
    $stmt->bindValue(':album', $sanitizedAlbum, SQLITE3_TEXT);
    $stmt->bindValue(':title', $sanitizedTitle, SQLITE3_TEXT);
    $stmt->bindValue(':lyrics', $sanitizedLyrics, SQLITE3_TEXT);
    $stmt->bindValue(':description', $sanitizedDescription, SQLITE3_TEXT);
    $stmt->execute();

    // Output a JSON response
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;

  } else {
    // If any required input data is missing, do not proceed with upload and insertion
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required input data.']);
    exit;
  }
}
?>