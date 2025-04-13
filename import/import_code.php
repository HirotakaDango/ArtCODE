<?php
require_once('../auth.php');

// Function to create a thumbnail
function createThumbnail($sourcePath, $destPath, $thumbWidth = 300) {
  list($width, $height) = getimagesize($sourcePath);
  $thumbHeight = (int)(($thumbWidth / $width) * $height);
  $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);

  $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
  // Create source image from file for all supported formats
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
    case 'webp':
      $sourceImage = imagecreatefromwebp($sourcePath);
      break;
    case 'avif':
      if (function_exists('imagecreatefromavif')) {
        $sourceImage = imagecreatefromavif($sourcePath);
      } else {
        return false;
      }
      break;
    case 'bmp':
      $sourceImage = imagecreatefrombmp($sourcePath);
      break;
    case 'wbmp':
      $sourceImage = imagecreatefromwbmp($sourcePath);
      break;
    default:
      return false;
  }

  imagecopyresampled($thumb, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);

  // Save thumbnail based on file type
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
    case 'webp':
      if (function_exists('imagewebp')) {
        imagewebp($thumb, $destPath);
      }
      break;
    case 'avif':
      if (function_exists('imageavif')) {
        imageavif($thumb, $destPath);
      }
      break;
    case 'bmp':
      if (function_exists('imagebmp')) {
        imagebmp($thumb, $destPath);
      }
      break;
    case 'wbmp':
      if (function_exists('imagewbmp')) {
        imagewbmp($thumb, $destPath);
      }
      break;
  }

  imagedestroy($thumb);
  imagedestroy($sourceImage);
  return true;
}

function generateUniqueImageId() {
  return bin2hex(random_bytes(6));
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
  
  // Get user ID from email
  $email = $_SESSION['email'];
  $db = new PDO('sqlite:../database.sqlite');
  
  $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
  $stmt->bindParam(':email', $email);
  $stmt->execute();
  $user = $stmt->fetch();
  
  if (!$user || !isset($user['id'])) {
    $response['message'] = "Error: Unable to find user ID";
    echo json_encode($response);
    exit;
  }
  
  $user_id = $user['id'];

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
        $currentDate = date('Y-m-d H:i:s'); // Get current timestamp

        // Array to store mapping of old image ID to new image info (newId, assetName, and child counter)
        $idMapping = [];

        // Import images data (main images)
        foreach ($imagesData as $image) {
          $oldId = $image['id'];
          // Generate one unique asset name for this image; it will be used in both folder and file names.
          $assetName = generateUniqueImageId();
          $ext = strtolower(pathinfo($image['filename'], PATHINFO_EXTENSION));
          
          // Generate new image ID and initialize child counter
          $newId = getNextAvailableId($db, 'images');
          $idMapping[$oldId] = [
            'newId'     => $newId,
            'assetName' => $assetName,
            'child_index' => 1  // start numbering children at 1
          ];
          
          // Create new filename using the structure:
          // images/uid_{user_id}/data/imageid-{newId}/imageassets_{assetName}/{assetName}_i0.{ext}
          $newFilename = "uid_" . $user_id . "/data/imageid-" . $newId 
                       . "/imageassets_" . $assetName . "/" . $assetName . "_i0." . $ext;

          // Create directories for original and thumbnail images
          $uploadDir = '../images/';
          $thumbnailDir = '../thumbnails/';
          
          if (!is_dir(dirname($uploadDir . $newFilename))) {
            mkdir(dirname($uploadDir . $newFilename), 0755, true);
          }
          if (!is_dir(dirname($thumbnailDir . $newFilename))) {
            mkdir(dirname($thumbnailDir . $newFilename), 0755, true);
          }

          $stmt = $db->prepare("INSERT INTO images (id, email, filename, original_filename, tags, title, imgdesc, link, type, episode_name, artwork_type, `group`, categories, language, parodies, characters, date) VALUES (:id, :email, :filename, :original_filename, :tags, :title, :imgdesc, :link, :type, :episode_name, :artwork_type, :group, :categories, :language, :parodies, :characters, :date)");
          
          $stmt->bindParam(':id', $newId);
          $stmt->bindParam(':email', $image['email']);
          $stmt->bindParam(':filename', $newFilename);
          $stmt->bindParam(':original_filename', $image['original_filename']);
          $stmt->bindParam(':tags', $image['tags']);
          $stmt->bindParam(':title', $image['title']);
          $stmt->bindParam(':imgdesc', $image['imgdesc']);
          $stmt->bindParam(':link', $image['link']);
          $stmt->bindParam(':type', $image['type']);
          $stmt->bindParam(':episode_name', $image['episode_name']);
          $stmt->bindParam(':artwork_type', $image['artwork_type']);
          $stmt->bindParam(':group', $image['group']);
          $stmt->bindParam(':categories', $image['categories']);
          $stmt->bindParam(':language', $image['language']);
          $stmt->bindParam(':parodies', $image['parodies']);
          $stmt->bindParam(':characters', $image['characters']);
          $stmt->bindParam(':date', $currentDate);
          $stmt->execute();

          // Move and process original image
          rename($extractPath . $image['filename'], $uploadDir . $newFilename);
          createThumbnail($uploadDir . $newFilename, $thumbnailDir . $newFilename);
        }

        // Import image_child data (child images)
        foreach ($imageChildData as $child) {
          $parentOldId = $child['image_id'];
          // Retrieve parent's new ID and assetName
          if (!isset($idMapping[$parentOldId])) {
            continue; // skip if parent not found
          }
          $parentInfo = $idMapping[$parentOldId];
          $ext = strtolower(pathinfo($child['filename'], PATHINFO_EXTENSION));
          
          // Use the parent's child counter to get the index and then increment it
          $childIndex = $parentInfo['child_index'];
          $idMapping[$parentOldId]['child_index']++;

          // Create new filename for child image:
          // images/uid_{user_id}/data/imageid-{newId}/imageassets_{assetName}/{assetName}_i{childIndex}.{ext}
          $newFilename = "uid_" . $user_id . "/data/imageid-" . $parentInfo['newId']
                       . "/imageassets_" . $parentInfo['assetName'] . "/" . $parentInfo['assetName'] . "_i" . $childIndex . "." . $ext;

          // Create directories for original and thumbnail images
          if (!is_dir(dirname($uploadDir . $newFilename))) {
            mkdir(dirname($uploadDir . $newFilename), 0755, true);
          }
          if (!is_dir(dirname($thumbnailDir . $newFilename))) {
            mkdir(dirname($thumbnailDir . $newFilename), 0755, true);
          }

          $stmt = $db->prepare("INSERT INTO image_child (filename, original_filename, image_id, email) VALUES (:filename, :original_filename, :image_id, :email)");
          
          $stmt->bindParam(':filename', $newFilename);
          $stmt->bindParam(':original_filename', $child['original_filename']);
          $stmt->bindParam(':image_id', $parentInfo['newId']);
          $stmt->bindParam(':email', $child['email']);
          $stmt->execute();

          // Move and process child image
          rename($extractPath . $child['filename'], $uploadDir . $newFilename);
          createThumbnail($uploadDir . $newFilename, $thumbnailDir . $newFilename);
        }

        // Clean up extracted files and ZIP
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