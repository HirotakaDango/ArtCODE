<?php
require_once('../../auth.php');
require_once 'getID3/getid3/getid3.php';

try {
  $db = new PDO('sqlite:../../database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
  exit();
}

$email = $_SESSION['email'];

// Get album and id from the query parameters
$album = $_GET['album'] ?? '';
$id = $_GET['id'] ?? '';

// Fetch music record with user information using JOIN
$query = "SELECT music.id, music.file, music.email, music.cover, music.album, music.title, music.description, music.lyrics, users.id as userid, users.artist
          FROM music
          JOIN users ON music.email = users.email
          WHERE music.album = :album AND music.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':album', $album, PDO::PARAM_STR);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect to the home page if the record is not found
if (!$row) {
  header('Location: index.php');
  exit;
}

// Get the email and artist ID of the selected user
$user_email = $row['email'];
$artist_id = $row['userid'];

// Music file and cover image paths
$musicFile = $row['file'];
$coverImage = $row['cover'];

if (!file_exists($musicFile)) {
  echo "File not found: $musicFile";
  exit;
}

// Use getID3 to analyze the music file
$getID3 = new getID3();
$fileInfo = $getID3->analyze($musicFile);
getid3_lib::CopyTagsToComments($fileInfo);

// Function to format bytes
function formatBytes($bytes, $precision = 2)
{
  $units = ['B', 'KB', 'MB', 'GB', 'TB'];
  $bytes = max($bytes, 0);
  $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
  $pow = min($pow, count($units) - 1);
  $bytes /= (1 << (10 * $pow));
  return round($bytes, $precision) . ' ' . $units[$pow];
}

// Extract information
$duration = !empty($fileInfo['playtime_string']) ? $fileInfo['playtime_string'] : 'Unknown';
$bitrate = !empty($fileInfo['audio']['bitrate']) ? round($fileInfo['audio']['bitrate'] / 1000) . 'kbps' : 'Unknown';
$size = !empty($fileInfo['filesize']) ? formatBytes($fileInfo['filesize']) : 'Unknown';
$audioType = !empty($fileInfo['fileformat']) ? $fileInfo['fileformat'] : 'Unknown';
$sampleRate = !empty($fileInfo['audio']['sample_rate']) ? $fileInfo['audio']['sample_rate'] . 'Hz' : 'Unknown';

// Fetch all music records for the specified artist
$queryAll = "SELECT music.id, music.file, music.email, music.cover, music.album, music.title, users.id as userid, users.artist
             FROM music
             JOIN users ON music.email = users.email
             WHERE users.id = :artist_id
             ORDER BY music.album ASC, music.id ASC";
$stmtAll = $db->prepare($queryAll);
$stmtAll->bindParam(':artist_id', $artist_id, PDO::PARAM_INT);
$stmtAll->execute();
$allRows = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

// Check if there is only one song for the artist and set a flag for looping
$loopPlaylist = count($allRows) === 1;

// Fetch next music record for the specified artist
$queryNext = "SELECT music.id, music.file, music.email, music.cover, music.album, music.title, users.id as userid, users.artist
              FROM music
              JOIN users ON music.email = users.email
              WHERE (music.album = :album AND music.id > :id)
                 OR (music.album = :album AND music.id = (SELECT MIN(id) FROM music WHERE album > :album AND email = :email))
                 OR (music.album > :album AND music.email = :email)
              ORDER BY music.album ASC, music.id ASC
              LIMIT 1";
$stmtNext = $db->prepare($queryNext);
$stmtNext->bindParam(':album', $album, PDO::PARAM_STR);
$stmtNext->bindParam(':id', $id, PDO::PARAM_INT);
$stmtNext->bindParam(':email', $user_email, PDO::PARAM_STR);
$stmtNext->execute();
$nextRow = $stmtNext->fetch(PDO::FETCH_ASSOC);

if (!$nextRow) {
  // If no next row, fetch the first music record for the artist
  $queryFirstNextArtist = "SELECT music.id, music.file, music.email, music.cover, music.album, music.title, users.id as userid, users.artist
                          FROM music
                          JOIN users ON music.email = users.email
                          WHERE users.id = :artist_id
                          ORDER BY music.album ASC, music.id ASC
                          LIMIT 1";
  $stmtFirstNextArtist = $db->prepare($queryFirstNextArtist);
  $stmtFirstNextArtist->bindParam(':artist_id', $artist_id, PDO::PARAM_INT);
  $stmtFirstNextArtist->execute();
  $nextRow = $stmtFirstNextArtist->fetch(PDO::FETCH_ASSOC);
}

// Fetch previous music record for the specified artist
$queryPrev = "SELECT music.id, music.file, music.email, music.cover, music.album, music.title, users.id as userid, users.artist
              FROM music
              JOIN users ON music.email = users.email
              WHERE (music.album = :album AND music.id < :id) OR (music.album < :album)
              ORDER BY music.album DESC, music.id DESC
              LIMIT 1";
$stmtPrev = $db->prepare($queryPrev);
$stmtPrev->bindParam(':album', $album, PDO::PARAM_STR);
$stmtPrev->bindParam(':id', $id, PDO::PARAM_INT);
$stmtPrev->execute();
$prevRow = $stmtPrev->fetch(PDO::FETCH_ASSOC);

if (!$prevRow) {
  // If no previous row, fetch the last music record for the artist
  $queryLastPrevArtist = "SELECT music.id, music.file, music.email, music.cover, music.album, music.title, users.id as userid, users.artist
                         FROM music
                         JOIN users ON music.email = users.email
                         WHERE users.id = :artist_id
                         ORDER BY music.album DESC, music.id DESC
                         LIMIT 1";
  $stmtLastPrevArtist = $db->prepare($queryLastPrevArtist);
  $stmtLastPrevArtist->bindParam(':artist_id', $artist_id, PDO::PARAM_INT);
  $stmtLastPrevArtist->execute();
  $prevRow = $stmtLastPrevArtist->fetch(PDO::FETCH_ASSOC);
}

// If looping is enabled, set the next and previous to the current song
if ($loopPlaylist) {
  $nextRow = $row;
  $prevRow = $row;
}

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $music_id = $_POST['music_id'];

  // Check if the novel has already been favorited by the current user
  $stmt = $db->prepare("SELECT COUNT(*) FROM favorites_music WHERE email = :email AND music_id = :music_id");
  $stmt->bindParam(':email', $_SESSION['email']);
  $stmt->bindParam(':music_id', $music_id);
  $stmt->execute();
  $existing_fav = $stmt->fetchColumn();

  if ($existing_fav == 0) {
    $stmt = $db->prepare("INSERT INTO favorites_music (email, music_id) VALUES (:email, :music_id)");
    $stmt->bindParam(':email', $_SESSION['email']);
    $stmt->bindParam(':music_id', $music_id);
    $stmt->execute();
  }

  // Redirect to the same page with the appropriate sorting parameter
  header('Location: music.php?album=' . $row['album'] . '&id=' . $row['id']);
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $music_id = $_POST['music_id'];
  $stmt = $db->prepare("DELETE FROM favorites_music WHERE email = :email AND music_id = :music_id");
  $stmt->bindParam(':email', $_SESSION['email']);
  $stmt->bindParam(':music_id', $music_id);
  $stmt->execute();

  // Redirect to the same page with the appropriate sorting parameter
  header('Location: music.php?album=' . $row['album'] . '&id=' . $row['id']);
  exit();
}

// Construct the URL
$redirect_url = 'play.php?album=' . urlencode($row['album']) . '&id=' . urlencode($row['id']);

// Perform the redirect
header('Location: ' . $redirect_url);
exit(); // Make sure to exit after a header location redirect
?>