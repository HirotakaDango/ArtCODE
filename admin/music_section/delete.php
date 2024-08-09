<?php
// admin/music_section/delete.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();

// Retrieve the email from the session
$email = $_SESSION['admin']['email'];

// Connect to SQLite database
$db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/database.sqlite');

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
  $mode = $mode ?: 'grid';
  $by = $by ?: 'newest';

  header('Location: /admin/music_section/?mode=' . $mode . '&by=' . $by);
  exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  // Delete the image cover if it exists and is not default_cover.jpg
  if (!empty($row['cover']) && $row['cover'] !== 'default_cover.jpg') {
    $coverPath = $_SERVER['DOCUMENT_ROOT'] . '/feeds/music/covers/' . $row['cover'];
    if (file_exists($coverPath)) {
      unlink($coverPath);
    }
  }

  // Delete the music file if it exists
  if (!empty($row['file'])) {
    $filePath = $_SERVER['DOCUMENT_ROOT'] . '/feeds/music/' . $row['file'];
    if (file_exists($filePath)) {
      unlink($filePath);
    }
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

  // Redirect to the music section page
  $mode = $mode ?: 'grid';
  $by = $by ?: 'newest';

  header('Location: /admin/music_section/?mode=' . $mode . '&by=' . $by);
  exit;
}

// Close the database connection
$db->close();
?>