<?php
require_once('auth.php');
$db = new SQLite3('../../database.sqlite');
$email = $_SESSION['email'];

// Get music ID from the query parameters
$id = $_GET['id'] ?? '';
$by = $_GET['by'] ?? '';
$mode = $_GET['mode'] ?? '';

// Fetch music record with user information using JOIN
$query = "SELECT music.id, music.file, music.email, music.cover, music.album, music.title, users.id as userid, users.artist
          FROM music
          JOIN users ON music.email = users.email
          WHERE music.id = :id";
$stmt = $db->prepare($query);
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);

// Redirect to the home page if the record is not found
if (!$row) {
  header('Location: index.php');
  exit;
}

// Check if the logged-in user is the owner of the music record
if ($row['email'] !== $email) {
  header('Location: index.php');
  exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  // Delete the image cover if it exists and is not default_cover.jpg
  if (!empty($row['cover']) && $row['cover'] !== 'default_cover.jpg') {
    unlink('covers/' . $row['cover']); // Adjust the path to "covers" in the current directory
  }

  // Delete the music file if it exists
  if (!empty($row['file'])) {
    unlink($row['file']); // Adjust the path to "uploads" in the current directory
  }

  // Delete the music record from the database
  $deleteQuery = "DELETE FROM music WHERE id = :id";
  $deleteStmt = $db->prepare($deleteQuery);
  $deleteStmt->bindValue(':id', $id, SQLITE3_INTEGER);
  $deleteStmt->execute();

  // Remove all entries related to this music from favorites_music table
  $removeFavoriteQuery = "DELETE FROM favorites_music WHERE music_id = :music_id";
  $removeFavoriteStmt = $db->prepare($removeFavoriteQuery);
  $removeFavoriteStmt->bindValue(':music_id', $id, SQLITE3_INTEGER);
  $removeFavoriteStmt->execute();

  // Redirect to the home page after the update
  $mode = isset($_GET['mode']) ? $_GET['mode'] : 'grid';
  $by = isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists');

  header('Location: ../music/profile.php?mode=' . $mode . '&by=' . $by;
}
?>