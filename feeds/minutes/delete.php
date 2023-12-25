<?php
require_once('../../auth.php');
$db = new SQLite3('../../database.sqlite');
$email = $_SESSION['email'];

// Get video ID from the query parameters
$id = $_GET['id'] ?? '';

// Fetch video record with user information using JOIN
$query = "SELECT videos.id, videos.video, videos.email, videos.thumb, videos.title, videos.description, videos.date, videos.view_count, users.id as userid, users.artist
          FROM videos
          JOIN users ON videos.email = users.email
          WHERE videos.id = :id";
$stmt = $db->prepare($query);
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);

// Redirect to the home page if the record is not found
if (!$row) {
  header('Location: index.php');
  exit;
}

// Check if the logged-in user is the owner of the video record
if ($row['email'] !== $email) {
  header('Location: index.php');
  exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  // Delete the video file if it exists
  if (!empty($row['video'])) {
    $videoFilePath = $row['video']; // Adjust the path to "videos" in the current directory

    // Check if the video file exists before attempting to delete
    if (file_exists($videoFilePath)) {
      unlink($videoFilePath);
    }
  }

  // Delete the thumbnail if it exists
  if (!empty($row['thumb']) && $row['thumb'] !== 'default_cover.jpg') {
    $thumbFilePath = 'thumbnails/' . $row['thumb']; // Adjust the path to "thumbnails" in the current directory

    // Check if the thumbnail file exists before attempting to delete
    if (file_exists($thumbFilePath)) {
      unlink($thumbFilePath);
    }
  }

  // Delete the video record from the database
  $deleteQuery = "DELETE FROM videos WHERE id = :id";
  $deleteStmt = $db->prepare($deleteQuery);
  $deleteStmt->bindValue(':id', $id, SQLITE3_INTEGER);
  
  // Check if the deletion query is successful before redirecting
  if ($deleteStmt->execute()) {
    header('Location: profile.php');
    exit;
  } else {
    // Handle deletion failure (you may want to log an error or show a user-friendly message)
    echo "Deletion failed. Please try again.";
    exit;
  }
}
?>
