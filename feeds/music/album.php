<?php
require_once('../../auth.php');
$db = new SQLite3('../../database.sqlite');
$email = $_SESSION['email'];

// Get the album parameter from the URL
$album = isset($_GET['album']) ? $_GET['album'] : null;

// Fetch music records with user information and filter by album if provided
$query = "SELECT music.id, music.file, music.email, music.cover, music.album, music.title, users.id AS userid, users.artist 
          FROM music 
          LEFT JOIN users ON music.email = users.email";

// If album parameter is provided, filter by album
if (!empty($album)) {
  $query .= " WHERE music.album = :album";
}

$query .= " ORDER BY music.id DESC";

$stmt = $db->prepare($query);

// Bind album parameter if provided
if (!empty($album)) {
  $stmt->bindValue(':album', $album, SQLITE3_TEXT);
}

$result = $stmt->execute();

// Fetch all rows as an associative array
$rows = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  $rows[] = $row;
}

// Check if there are any rows
if (!empty($rows)) {
  // Get the first row
  $firstRow = $rows[0];

  // Extract the image file path from the first row
  $imagePath = $firstRow['cover'];

  // Extract the user ID from the first row
  $userid = $firstRow['userid'];
  
  // Extract the album from the first row
  $album = $firstRow['album'];
}

// Calculate the total number of tracks in the same album
$countQuery = "SELECT COUNT(*) as count FROM music WHERE album = :album";
$countStmt = $db->prepare($countQuery);
$countStmt->bindValue(':album', $album, SQLITE3_TEXT);
$countResult = $countStmt->execute();
$albumTrackCount = $countResult->fetchArray(SQLITE3_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo (!empty($rows) ? htmlspecialchars($rows[0]['album']) : 'Untitled Album'); ?></title>
    <link rel="icon" type="image/png" href="covers/<?php echo $imagePath; ?>">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container-fluid mt-3">
      <?php include('header.php'); ?>
      <div class="row p-4 p-md-5 mb-3">
        <div class="col-md-3 order-md-1 mb-3 p-md-0 pe-md-4">
          <div class="position-relative">
            <div class="ratio ratio-1x1">
              <a data-bs-toggle="modal" data-bs-target="#originalImage"><img src="covers/<?php echo $imagePath; ?>" class="object-fit-cover img-fluid rounded shadow" alt="..."></a>
            </div>
            <button type="button" class="btn btn-dark opacity-75 position-absolute bottom-0 end-0 m-2 fw-medium" data-bs-toggle="modal" data-bs-target="#shareLink"><small><i class="bi bi-share-fill"></i> share</small></button>
          </div>
        </div>
        <div class="col-md-7 order-md-2">
          <h2 class="featurette-heading fw-normal fw-bold">Album: <?php echo (!empty($rows) ? htmlspecialchars($rows[0]['album']) : 'Untitled Album'); ?></span></h2>
          <p class="fw-medium mt-3">Artist : <a class="text-decoration-none text-white" href="artist.php?id=<?php echo $userid; ?>"><?php echo isset($rows[0]['artist']) ? htmlentities($rows[0]['artist']) : ''; ?></a></p>
          <p class="fw-medium mt-3">Total Tracks in Album: <?php echo $albumTrackCount; ?> songs</p>
        </div>
      </div>
    </div>
    <hr>
    <div class="container-fluid mt-3">
      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 row-cols-xl-8 g-1">
        <?php foreach ($rows as $row): ?>
          <?php include('music_info.php'); ?>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="modal fade" id="shareLink" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="card rounded-4 p-4">
            <p class="text-start fw-bold">share to:</p>
            <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
              <!-- Twitter -->
              <a class="btn rounded-start-4" href="https://twitter.com/intent/tweet?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-twitter"></i>
              </a>
                                
              <!-- Line -->
              <a class="btn" href="https://social-plugins.line.me/lineit/share?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-line"></i>
              </a>
                                
              <!-- Email -->
              <a class="btn" href="mailto:?body=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>">
                <i class="bi bi-envelope-fill"></i>
              </a>
                                
              <!-- Reddit -->
              <a class="btn" href="https://www.reddit.com/submit?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-reddit"></i>
              </a>
                                
              <!-- Instagram -->
              <a class="btn" href="https://www.instagram.com/?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-instagram"></i>
              </a>
                                
              <!-- Facebook -->
              <a class="btn rounded-end-4" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-facebook"></i>
              </a>
            </div>
            <div class="btn-group w-100" role="group" aria-label="Share Buttons">
              <!-- WhatsApp -->
              <a class="btn rounded-start-4" href="https://wa.me/?text=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-whatsapp"></i>
              </a>
    
              <!-- Pinterest -->
              <a class="btn" href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-pinterest"></i>
              </a>
    
              <!-- LinkedIn -->
              <a class="btn" href="https://www.linkedin.com/shareArticle?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-linkedin"></i>
              </a>
    
              <!-- Messenger -->
              <a class="btn" href="https://www.facebook.com/dialog/send?link=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-messenger"></i>
              </a>
    
              <!-- Telegram -->
              <a class="btn" href="https://telegram.me/share/url?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-telegram"></i>
              </a>
    
              <!-- Snapchat -->
              <a class="btn rounded-end-4" href="https://www.snapchat.com/share?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/album.php?album=' . $album); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-snapchat"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="originalImage" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="modal-body position-relative">
            <img class="object-fit-contain h-100 w-100 rounded" src="covers/<?php echo $imagePath; ?>">
            <button type="button" class="btn border-0 position-absolute end-0 top-0 m-2" data-bs-dismiss="modal"><i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i></button>
            <a class="btn btn-primary fw-bold w-100 mt-2" href="covers/<?php echo $imagePath; ?>" download>Download Cover Image</a>
          </div>
        </div>
      </div>
    </div>
    <style>
      .text-shadow {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);
      }
    </style>
    <div class="mt-5"></div>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
