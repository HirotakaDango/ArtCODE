<?php
require_once('../auth.php');

// Connect to the database using PDO
$db = new PDO('sqlite:../database.sqlite');

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  // Redirect to index.php if not logged in
  header("Location: ../index.php");
  exit;
}

// Check if an artworkid is provided
if (!isset($_GET['artworkid']) || empty($_GET['artworkid'])) {
  die("No artwork ID specified.");
}

$artworkId = $_GET['artworkid'];

// Retrieve the email of the logged-in user
$email = $_SESSION['email'];

// Check if the logged-in user is the owner of the artwork
$stmt = $db->prepare("SELECT * FROM images WHERE id = :artworkid AND email = :email");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->bindParam(':email', $email);
$stmt->execute();
$image = $stmt->fetch();

// If the image does not exist or does not belong to the logged-in user, show a forbidden error
if (!$image) {
  echo '<meta charset="UTF-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <img src="../icon/403-Error-Forbidden.svg" style="height: 100%; width: 100%;">
       ';
  exit();
}

// Function to get all image filenames associated with the current ID
function getAllImageFilenames($db, $artworkId) {
  $stmt = $db->prepare("SELECT filename FROM images WHERE id = :artworkid");
  $stmt->bindParam(':artworkid', $artworkId);
  $stmt->execute();
  $images = $stmt->fetchAll(PDO::FETCH_COLUMN);

  $stmt = $db->prepare("SELECT filename FROM image_child WHERE image_id = :artworkid");
  $stmt->bindParam(':artworkid', $artworkId);
  $stmt->execute();
  $child_images = $stmt->fetchAll(PDO::FETCH_COLUMN);

  // Merge the filenames from both tables
  $all_images = array_merge($images, $child_images);

  return $all_images;
}

// Get all image filenames associated with the current ID
$all_images = getAllImageFilenames($db, $artworkId);

// Create a ZIP file and add the images to it
$zip = new ZipArchive();

// Get the ID, title, and artist from the images and users tables using JOIN
$stmt = $db->prepare("SELECT i.id, i.title, REPLACE(u.artist, ' ', '_') AS artist FROM images i JOIN users u ON i.email = u.email WHERE i.id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$image_info = $stmt->fetch();

$id = $image_info['id'];
$title = $image_info['title'];
$artist = $image_info['artist'];

// Create a ZIP file name based on the title
$zip_filename = $title . '_image_id_' . $id . '_by_' . $artist . '.zip';

if ($zip->open($zip_filename, ZipArchive::CREATE) === true) {
  // Add images to the ZIP file
  foreach ($all_images as $image_filename) {
    $image_path = "../images/{$image_filename}";
    if (file_exists($image_path)) {
      $zip->addFile($image_path, $image_filename);
    }
  }

  // Create JSON data of images and image_child tables
  $stmt = $db->prepare("SELECT * FROM images WHERE id = :artworkid");
  $stmt->bindParam(':artworkid', $artworkId);
  $stmt->execute();
  $images_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :artworkid");
  $stmt->bindParam(':artworkid', $artworkId);
  $stmt->execute();
  $image_child_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $json_data = json_encode([
    'images' => $images_data,
    'image_child' => $image_child_data
  ], JSON_PRETTY_PRINT);

  // Add the JSON data to the ZIP file
  $zip->addFromString('images_data.json', $json_data);

  $zip->close();

  // Send the ZIP file for download
  header('Content-Type: application/zip');
  header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
  header('Content-Length: ' . filesize($zip_filename));
  readfile($zip_filename);

  // Delete the temporary ZIP file
  unlink($zip_filename);
  exit;
} else {
  echo "Failed to create the ZIP file.";
}
?>