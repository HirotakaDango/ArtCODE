<?php
require_once('auth.php');
$db = new PDO('sqlite:../../database.sqlite');

// Get the album parameter from the URL
$album = isset($_GET['album']) ? $_GET['album'] : null;

// Get the user id parameter from the URL
$userid = isset($_GET['userid']) ? $_GET['userid'] : null;

if (empty($album) || empty($userid)) {
  // Handle missing parameters
  echo "Album and user id parameters are required.";
  exit();
}

// Fetch music records filtered by album and user id, joining with users table
$query = "SELECT music.file 
          FROM music 
          INNER JOIN users ON music.email = users.email 
          WHERE music.album = :album AND users.id = :userid";
$stmt = $db->prepare($query);
$stmt->bindParam(':album', $album, PDO::PARAM_STR);
$stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
$stmt->execute();
$songs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($songs)) {
  // No songs found for the given criteria
  echo "No songs found for the provided album and user id.";
  exit();
}

// Create a new zip archive
$zip = new ZipArchive();
$zipFileName = $album . '.zip';

if ($zip->open($zipFileName, ZipArchive::CREATE) !== TRUE) {
  // Failed to create the zip file
  echo "Failed to create zip file.";
  exit();
}

foreach ($songs as $song) {
  $filePath = $song['file'];
  if (file_exists($filePath)) {
    // Add file to the zip archive
    $zip->addFile($filePath, basename($filePath));
  }
}

$zip->close();

// Set headers for file download
header("Content-type: application/zip");
header("Content-Disposition: attachment; filename=$zipFileName");
header("Pragma: no-cache");
header("Expires: 0");
readfile($zipFileName);

// Delete the zip file after download
unlink($zipFileName);
?>
