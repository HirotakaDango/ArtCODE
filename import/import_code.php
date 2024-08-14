<?php
require_once('../auth.php');

// Function to create a thumbnail
function createThumbnail($sourcePath, $destPath, $thumbWidth = 300) {
  list($width, $height) = getimagesize($sourcePath);
  $thumbHeight = (int) (($thumbWidth / $width) * $height);
  $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);

  $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
  switch ($extension) {
    case 'jpg':
    case 'jpeg':
      $sourceImage = imagecreatefromjpeg($sourcePath);
      break;
    case 'png':
      $sourceImage = imagecreatefrompng($sourcePath);
      break;
    case 'gif':
      $sourceImage = imagecreatefromgif($sourcePath);
      break;
    default:
      return false;
  }

  imagecopyresampled($thumb, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);

  switch ($extension) {
    case 'jpg':
    case 'jpeg':
      imagejpeg($thumb, $destPath);
      break;
    case 'png':
      imagepng($thumb, $destPath);
      break;
    case 'gif':
      imagegif($thumb, $destPath);
      break;
  }

  imagedestroy($thumb);
  imagedestroy($sourceImage);
  return true;
}

// Function to get the next available ID
function getNextAvailableId($db, $table) {
  $stmt = $db->prepare("SELECT MAX(id) AS max_id FROM $table");
  $stmt->execute();
  $result = $stmt->fetch();
  return $result['max_id'] + 1;
}

// Function to handle file naming conflicts
function handleFileConflict($db, $table, $filename, $extractPath) {
  $extension = pathinfo($filename, PATHINFO_EXTENSION);
  $newFilename = $filename;

  while (true) {
    $stmt = $db->prepare("SELECT filename FROM $table WHERE filename = :filename");
    $stmt->bindParam(':filename', $newFilename);
    $stmt->execute();
    if (!$stmt->fetch()) {
      break;
    }
    $newFilename = uniqid() . '.' . $extension;
  }

  if ($newFilename !== $filename) {
    rename($extractPath . $filename, $extractPath . $newFilename);
  }

  return $newFilename;
}

// Main import process
$response = ['status' => 'error', 'message' => 'An unexpected error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['zipfile'])) {
  $zipfile = $_FILES['zipfile'];

  if ($zipfile['type'] !== 'application/zip' && $zipfile['type'] !== 'application/x-zip-compressed') {
    $response['message'] = "Only ZIP files are allowed.";
    echo json_encode($response);
    exit;
  }

  $uploadPath = '../images/';
  $zipPath = $uploadPath . basename($zipfile['name']);
  move_uploaded_file($zipfile['tmp_name'], $zipPath);

  $zip = new ZipArchive();
  if ($zip->open($zipPath) === TRUE) {
    $extractPath = $uploadPath . 'extracted/';
    mkdir($extractPath, 0777, true);
    $zip->extractTo($extractPath);
    $zip->close();

    $jsonPath = $extractPath . 'images_data.json';
    if (file_exists($jsonPath)) {
      $jsonData = file_get_contents($jsonPath);
      $data = json_decode($jsonData, true);

      if (isset($data['images']) && isset($data['image_child'])) {
        $imagesData = $data['images'];
        $imageChildData = $data['image_child'];

        $db = new PDO('sqlite:../database.sqlite');

        // Array to store old and new IDs
        $idMapping = [];

        // Import images data
        foreach ($imagesData as $image) {
          $oldId = $image['id'];
          $filename = handleFileConflict($db, 'images', $image['filename'], $extractPath);

          // Generate a new date-based folder
          $dateFolder = date('Y/m/d');
          $uploadDir = '../images/' . $dateFolder . '/';
          if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
          }

          // Update filename to include only one date folder
          $newFilename = $dateFolder . '/' . basename($filename);

          $stmt = $db->prepare("SELECT id FROM images WHERE id = :id");
          $stmt->bindParam(':id', $oldId);
          $stmt->execute();
          if ($stmt->fetch()) {
            $newId = getNextAvailableId($db, 'images');
            $idMapping[$oldId] = $newId;
          } else {
            $newId = $oldId;
          }

          $stmt = $db->prepare("REPLACE INTO images (id, filename, original_filename, email, tags, title, imgdesc, link, date, view_count, type, episode_name, artwork_type, `group`, categories, language, parodies, characters) VALUES (:id, :filename, :original_filename, :email, :tags, :title, :imgdesc, :link, :date, :view_count, :type, :episode_name, :artwork_type, :group, :categories, :language, :parodies, :characters)");
          $stmt->bindParam(':id', $newId);
          $stmt->bindParam(':filename', $newFilename);
          $stmt->bindParam(':original_filename', $image['original_filename']);
          $stmt->bindParam(':email', $image['email']);
          $stmt->bindParam(':tags', $image['tags']);
          $stmt->bindParam(':title', $image['title']);
          $stmt->bindParam(':imgdesc', $image['imgdesc']);
          $stmt->bindParam(':link', $image['link']);
          $stmt->bindParam(':date', $image['date']);
          $stmt->bindParam(':view_count', $image['view_count']);
          $stmt->bindParam(':type', $image['type']);
          $stmt->bindParam(':episode_name', $image['episode_name']);
          $stmt->bindParam(':artwork_type', $image['artwork_type']);
          $stmt->bindParam(':group', $image['group']);
          $stmt->bindParam(':categories', $image['categories']);
          $stmt->bindParam(':language', $image['language']);
          $stmt->bindParam(':parodies', $image['parodies']);
          $stmt->bindParam(':characters', $image['characters']);
          $stmt->execute();

          // Move original image to images folder
          rename($extractPath . $filename, $uploadDir . basename($newFilename));

          // Generate thumbnail for the main image
          $thumbPath = '../thumbnails/' . $dateFolder . '/' . basename($newFilename);
          if (!is_dir(dirname($thumbPath))) {
            mkdir(dirname($thumbPath), 0755, true);
          }
          createThumbnail($uploadDir . basename($newFilename), $thumbPath);
        }

        // Import image_child data
        foreach ($imageChildData as $child) {
          $oldId = $child['id'];
          $filename = handleFileConflict($db, 'image_child', $child['filename'], $extractPath);

          // Generate a new date-based folder for child images
          $dateFolder = date('Y/m/d');
          $uploadDir = '../images/' . $dateFolder . '/';
          if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
          }

          // Update filename to include only one date folder
          $newFilename = $dateFolder . '/' . basename($filename);

          $stmt = $db->prepare("SELECT id FROM image_child WHERE id = :id");
          $stmt->bindParam(':id', $oldId);
          $stmt->execute();
          if ($stmt->fetch()) {
            $newId = getNextAvailableId($db, 'image_child');
          } else {
            $newId = $oldId;
          }

          // Check if the image_id needs to be updated
          $imageId = $child['image_id'];
          if (isset($idMapping[$imageId])) {
            $imageId = $idMapping[$imageId];
          }

          $stmt = $db->prepare("REPLACE INTO image_child (id, filename, original_filename, image_id, email) VALUES (:id, :filename, :original_filename, :image_id, :email)");
          $stmt->bindParam(':id', $newId);
          $stmt->bindParam(':filename', $newFilename);
          $stmt->bindParam(':original_filename', $child['original_filename']);
          $stmt->bindParam(':image_id', $imageId);
          $stmt->bindParam(':email', $child['email']);
          $stmt->execute();

          // Move child image to images folder
          rename($extractPath . $filename, $uploadDir . basename($newFilename));

          // Generate thumbnail for the child image
          $thumbPath = '../thumbnails/' . $dateFolder . '/' . basename($newFilename);
          if (!is_dir(dirname($thumbPath))) {
            mkdir(dirname($thumbPath), 0755, true);
          }
          createThumbnail($uploadDir . basename($newFilename), $thumbPath);
        }

        // Clean up
        unlink($zipPath);
        array_map('unlink', glob("$extractPath/*"));
        rmdir($extractPath);

        $response['status'] = 'success';
        $response['message'] = 'Import completed successfully.';
      } else {
        $response['message'] = 'Invalid JSON data.';
      }
    } else {
      $response['message'] = 'JSON file not found.';
    }
  } else {
    $response['message'] = 'Failed to open ZIP file.';
  }
}

echo json_encode($response);
?>