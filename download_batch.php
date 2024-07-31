<?php
require_once('auth.php');

// Connect to the database using PDO
$db = new PDO('sqlite:database.sqlite');

// Get the filename from the query string
$artworkId = $_GET['artworkid'];

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
  foreach ($all_images as $image_filename) {
    $image_path = "images/{$image_filename}";
    if (file_exists($image_path)) {
      $zip->addFile($image_path, $image_filename);
    }
  }
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