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
$query = "SELECT music.id, music.file, music.email, music.cover, music.album, music.title, users.id as userid, users.artist
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

// Get the email of the selected user
$user_email = $row['email'];

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
function formatBytes($bytes, $precision = 2) {
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

// Fetch all music records for the specified album
$queryAll = "SELECT music.id, music.file, music.email, music.cover, music.album, music.title, users.id as userid, users.artist
             FROM music
             JOIN users ON music.email = users.email
             WHERE music.album = :album
             ORDER BY music.id ASC";
$stmtAll = $db->prepare($queryAll);
$stmtAll->bindParam(':album', $album, PDO::PARAM_STR);
$stmtAll->execute();
$allRows = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

// Fetch next and previous music records
$queryNext = "SELECT music.id, music.file, music.email, music.cover, music.album, music.title, users.id as userid, users.artist
              FROM music
              JOIN users ON music.email = users.email
              WHERE music.album = :album AND music.id > :id
              ORDER BY music.id ASC
              LIMIT 1";
$stmtNext = $db->prepare($queryNext);
$stmtNext->bindParam(':album', $album, PDO::PARAM_STR);
$stmtNext->bindParam(':id', $id, PDO::PARAM_INT);
$stmtNext->execute();
$nextRow = $stmtNext->fetch(PDO::FETCH_ASSOC);

if (!$nextRow) {
  // If no next row, fetch the first music record
  $queryFirst = "SELECT music.id, music.file, music.email, music.cover, music.album, music.title, users.id as userid, users.artist
                 FROM music
                 JOIN users ON music.email = users.email
                 WHERE music.album = :album
                 ORDER BY music.id ASC
                 LIMIT 1";
  $stmtFirst = $db->prepare($queryFirst);
  $stmtFirst->bindParam(':album', $album, PDO::PARAM_STR);
  $stmtFirst->execute();
  $nextRow = $stmtFirst->fetch(PDO::FETCH_ASSOC);
}

$queryPrev = "SELECT music.id, music.file, music.email, music.cover, music.album, music.title, users.id as userid, users.artist
              FROM music
              JOIN users ON music.email = users.email
              WHERE music.album = :album AND music.id < :id
              ORDER BY music.id DESC
              LIMIT 1";
$stmtPrev = $db->prepare($queryPrev);
$stmtPrev->bindParam(':album', $album, PDO::PARAM_STR);
$stmtPrev->bindParam(':id', $id, PDO::PARAM_INT);
$stmtPrev->execute();
$prevRow = $stmtPrev->fetch(PDO::FETCH_ASSOC);

if (!$prevRow) {
  // If no previous row, fetch the latest music record
  $queryLatest = "SELECT music.id, music.file, music.email, music.cover, music.album, music.title, users.id as userid, users.artist
                  FROM music
                  JOIN users ON music.email = users.email
                  WHERE music.album = :album
                  ORDER BY music.id DESC
                  LIMIT 1";
  $stmtLatest = $db->prepare($queryLatest);
  $stmtLatest->bindParam(':album', $album, PDO::PARAM_STR);
  $stmtLatest->execute();
  $prevRow = $stmtLatest->fetch(PDO::FETCH_ASSOC);
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
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $row['title']; ?></title>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="plyr.css">
    <style>
      /* For Webkit-based browsers */
      ::-webkit-scrollbar {
        width: 0;
        height: 0;
        border-radius: 10px;
      }

      ::-webkit-scrollbar-track {
        border-radius: 0;
      }

      ::-webkit-scrollbar-thumb {
        border-radius: 0;
      }
    </style>
  </head>
  <body>
    <div class="container-fluid mt-3">
      <nav aria-label="breadcrumb">
        <div class="d-none d-md-block d-lg-block">
          <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-3" style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);">
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">
                ArtCODE
              </a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/music/">Home</a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none fw-bold" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/music/music.php?album=<?php echo $row['album']; ?>&id=<?php echo $row['id']; ?>"><?php echo $row['title']; ?></a>
            </li>
            <?php if ($user_email === $email): ?>
              <li class="breadcrumb-item">
                <a class="link-body-emphasis text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/music/edit.php?id=<?php echo $row['id']; ?>">Edit <?php echo $row['title']; ?></a>
              </li>
            <?php endif; ?>
            <a class="btn btn-sm border-0 btn-outline-light ms-auto" href="#" data-bs-toggle="modal" data-bs-target="#shareLink"><i class="bi bi-share-fill"></i></a>
          </ol>
        </div>
        <div class="d-md-none d-lg-none">
          <div class="btn-group mb-2 w-100 p-3 bg-body-tertiary gap-2">
            <a class="btn fw-bold w-100 text-start rounded" data-bs-toggle="collapse" href="#collapseModal" role="button" aria-expanded="false" aria-controls="collapseExample">
              <i class="bi bi-list" style="-webkit-text-stroke: 1px;"></i> Menu
            </a>
            <a class="btn fw-bold w-100 rounded" href="#" data-bs-toggle="modal" style="max-width: 50px;" data-bs-target="#shareLink"><i class="bi bi-share-fill"></i></a>
          </div>
          <div class="collapse bg-body-tertiary mb-2 rounded" id="collapseModal">
            <div class="btn-group-vertical w-100">
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">ArtCODE</a>
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/music/">Home</a>
              <a class="btn py-2 rounded text-start fw-bold" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/music/music.php?album=<?php echo $row['album']; ?>&id=<?php echo $row['id']; ?>"><i class="bi bi-chevron-right small" style="-webkit-text-stroke: 2px;"></i> <?php echo $row['title']; ?></a>
              <?php if ($user_email === $email): ?>
                <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/music/edit.php?id=<?php echo $row['id']; ?>">Edit <?php echo $row['title']; ?></a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </nav>
      <div class="row featurette mt-2">
        <div class="col-md-5 order-md-1 mb-5">
          <h5 class="text-center fw-bold display-5" style="overflow-x: auto; white-space: nowrap;"><?php echo $row['title']; ?></h5>
          <p class="text-center fw-bold" style="overflow-x: auto; white-space: nowrap;">
            <a class="text-decoration-none text-white" href="artist.php?id=<?php echo $row['userid']; ?>"><?php echo $row['artist']; ?></a> -
            <a class="text-decoration-none text-white" href="album.php?album=<?php echo $row['album']; ?>"><?php echo $row['album']; ?></a>
          </p>
          <div class="container w-75 d-none d-md-block d-lg-block">
            <div class="position-relative">
              <div class="text-center mb-2 ratio ratio-1x1">
                <img src="covers/<?php echo $coverImage; ?>" alt="Song Image" class="img-fluid object-fit-cover rounded shadow">
              </div>
              <button type="button" class="btn btn-dark opacity-50 position-absolute top-0 start-0 mt-1 ms-1 rounded-1" data-bs-toggle="modal" data-bs-target="#songInfo">
                <i class="bi bi-info-circle-fill"></i>
              </button>
            </div>
          </div>
          <div class="position-relative d-md-none d-lg-none">
            <div class="text-center mb-2 ratio ratio-1x1">
              <img src="covers/<?php echo $coverImage; ?>" alt="Song Image" class="img-fluid object-fit-cover rounded shadow">
            </div>
            <button type="button" class="btn btn-dark opacity-50 position-absolute top-0 start-0 mt-1 ms-1 rounded-1" data-bs-toggle="modal" data-bs-target="#songInfo">
              <i class="bi bi-info-circle-fill"></i>
            </button>
          </div>
          <div class="modal fade" id="songInfo" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel"><?php echo $row['title']; ?></h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="metadata">
                    <p class="fw-bold text-start">Artist: <a class="text-decoration-none text-white" href="artist.php?id=<?php echo $row['userid']; ?>"><?php echo $row['artist']; ?></a></p>
                    <p class="fw-bold text-start">Album: <a class="text-decoration-none text-white" href="album.php?album=<?php echo $row['album']; ?>"><?php echo $row['album']; ?></a></p>
                    <p class="fw-bold text-start">Duration: <?= $duration ?></p>
                    <p class="fw-bold text-start">Bitrate: <?= $bitrate ?></p>
                    <p class="fw-bold text-start">Size: <?= $size ?></p>
                    <p class="fw-bold text-start">Audio Type: <?= $audioType ?></p>
                    <a class="btn btn-primary fw-bold w-100" href="<?php echo $row['file']; ?>" download>Download Song</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="w-100 bg-dark fixed-bottom">
            <div class="d-md-none d-lg-none mb-5">
              <div class="d-flex justify-content-center btn-group">
                <a href="music.php?album=<?php echo urlencode($prevRow['album']); ?>&id=<?php echo $prevRow['id']; ?>" class="btn float-end text-white"><i class="bi bi-skip-start-fill display-1"></i></a>
                <button class="text-decoration-none btn text-white d-md-none d-lg-none" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="bi bi-music-note-list display-1"></i></button>
                <a href="music.php?album=<?php echo urlencode($nextRow['album']); ?>&id=<?php echo $nextRow['id']; ?>" class="btn float-end text-white"><i class="bi bi-skip-end-fill display-1"></i></a>
              </div>
            </div> 
            <div class="d-flex justify-content-between align-items-center border-2 border-top">
              <div class="w-100">
                <audio id="player" controls>
                  <source src="<?php echo $musicFile; ?>" type="audio/mpeg">
                  Your browser does not support the audio element.
                </audio>
              </div>
              <?php
                $stmt = $db->prepare("SELECT COUNT(*) FROM favorites_music WHERE email = :email AND music_id = :music_id");
                $stmt->bindParam(':email', $_SESSION['email']);
                $stmt->bindParam(':music_id', $row['id']);
                $stmt->execute();
                $is_favorited = $stmt->fetchColumn();

                if ($is_favorited) {
              ?>
                <form method="POST">
                  <input type="hidden" name="music_id" value="<?php echo $row['id']; ?>">
                  <button type="submit" class="btn mt-1" name="unfavorite" style="color: #4A5464;">
                    <i class="bi bi-heart-fill fs-5"></i>
                  </button>
                </form>
              <?php } else { ?>
                <form method="POST">
                  <input type="hidden" name="music_id" value="<?php echo $row['id']; ?>">
                  <button type="submit" class="btn mt-1" name="favorite" style="color: #4A5464;">
                    <i class="bi bi-heart fs-5"></i>
                  </button>
                </form>
              <?php } ?>
              <div class="d-none d-md-block d-lg-block">
                <div class="btn-group">
                  <a href="music.php?album=<?php echo urlencode($prevRow['album']); ?>&id=<?php echo $prevRow['id']; ?>" class="btn float-end fw-bold mt-1" style="color: #4A5464;"><i class="bi bi-skip-start-fill fs-3"></i></a>
                  <a href="music.php?album=<?php echo urlencode($nextRow['album']); ?>&id=<?php echo $nextRow['id']; ?>" class="btn float-end fw-bold mt-1" style="color: #4A5464;"><i class="bi bi-skip-end-fill fs-3"></i></a>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-7 order-md-2">
          <div class="modal fade d-md-none d-lg-none" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-fullscreen modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fw-bold fs-5" id="exampleModalLabel"><i class="bi bi-music-note-list"></i> song list from <?php echo $row['album']; ?></h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <?php foreach ($allRows as $song): ?>
                    <div class="d-flex justify-content-between align-items-center border-bottom">
                      <a class="text-decoration-none music text-start w-100 text-white btn fw-bold" href="music.php?album=<?php echo urlencode($song['album']); ?>&id=<?php echo $song['id']; ?>">
                        <?php echo $song['title']; ?><br>
                        <small class="text-muted"><?php echo $song['artist']; ?></small>
                      </a>
                      <div class="dropdown dropdown-menu-end">
                        <button class="text-decoration-none text-white btn fw-bold" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu">
                          <li><button class="dropdown-item" onclick="sharePageS('<?php echo $song['id']; ?>', '<?php echo $song['title']; ?>')"><i class="bi bi-share-fill"></i> share</button></li>
                          <li><a class="dropdown-item" href="artist.php?name=<?php echo $song['userid']; ?>"><i class="bi bi-person-fill"></i> show artist</a></li>
                          <li><a class="dropdown-item" href="album.php?album=<?php echo $song['album']; ?>"><i class="bi bi-disc-fill"></i> show album</a></li>
                        </ul>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div> 
          <div class="d-none d-md-block d-lg-block">
            <div class="overflow-y-auto" id="autoHeightDiv" style="max-height: 100%;">
              <h3 class="text-start fw-bold"><i class="bi bi-music-note-list"></i> song list from <?php echo $row['album']; ?></h3>
              <?php foreach ($allRows as $song): ?>
                <div class="d-flex justify-content-between align-items-center border-bottom">
                  <a class="text-decoration-none music text-start w-100 text-white btn fw-bold" href="music.php?album=<?php echo urlencode($song['album']); ?>&id=<?php echo $song['id']; ?>">
                    <?php echo $song['title']; ?><br>
                    <small class="text-muted"><?php echo $song['artist']; ?></small>
                  </a>
                  <div class="dropdown dropdown-menu-end">
                    <button class="text-decoration-none text-white btn fw-bold" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical"></i></button>
                    <ul class="dropdown-menu">
                      <li><button class="dropdown-item" onclick="sharePageS('<?php echo $song['id']; ?>', '<?php echo $song['title']; ?>')"><i class="bi bi-share-fill"></i> share</button></li>
                      <li><a class="dropdown-item" href="artist.php?name=<?php echo $song['userid']; ?>"><i class="bi bi-person-fill"></i> show artist</a></li>
                      <li><a class="dropdown-item" href="album.php?album=<?php echo $song['album']; ?>"><i class="bi bi-disc-fill"></i> show album</a></li>
                    </ul>
                  </div>
                </div>
              <?php endforeach; ?>
              <br><br><br><br><br>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="shareLink" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="card rounded-4 p-4">
            <p class="text-start fw-bold">share to:</p>
            <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
              <!-- Twitter -->
              <a class="btn rounded-start-4" href="https://twitter.com/intent/tweet?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?id=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-twitter"></i>
              </a>
                                
              <!-- Line -->
              <a class="btn" href="https://social-plugins.line.me/lineit/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?id=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-line"></i>
              </a>
                                
              <!-- Email -->
              <a class="btn" href="mailto:?body=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?id=' . $row['id']; ?>">
                <i class="bi bi-envelope-fill"></i>
              </a>
                                
              <!-- Reddit -->
              <a class="btn" href="https://www.reddit.com/submit?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?id=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-reddit"></i>
              </a>
                                
              <!-- Instagram -->
              <a class="btn" href="https://www.instagram.com/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?id=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-instagram"></i>
              </a>
                                
              <!-- Facebook -->
              <a class="btn rounded-end-4" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?id=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-facebook"></i>
              </a>
            </div>
            <div class="btn-group w-100" role="group" aria-label="Share Buttons">
              <!-- WhatsApp -->
              <a class="btn rounded-start-4" href="https://wa.me/?text=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?id=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-whatsapp"></i>
              </a>
    
              <!-- Pinterest -->
              <a class="btn" href="https://pinterest.com/pin/create/button/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?id=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-pinterest"></i>
              </a>
    
              <!-- LinkedIn -->
              <a class="btn" href="https://www.linkedin.com/shareArticle?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?id=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-linkedin"></i>
              </a>
    
              <!-- Messenger -->
              <a class="btn" href="https://www.facebook.com/dialog/send?link=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?id=' . $row['id']; ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-messenger"></i>
              </a>
    
              <!-- Telegram -->
              <a class="btn" href="https://telegram.me/share/url?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?id=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-telegram"></i>
              </a>
    
              <!-- Snapchat -->
              <a class="btn rounded-end-4" href="https://www.snapchat.com/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?id=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-snapchat"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <script src="https://cdn.plyr.io/3.6.7/plyr.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const player = new Plyr('#player');

        // Autoplay the player when the page loads
        player.play();

        player.on('ended', function(event) {
          // Redirect to the next song URL
          window.location.href = "music.php?album=<?php echo urlencode($nextRow['album']); ?>&id=<?php echo $nextRow['id']; ?>";
        });

        // Function to navigate to the next music page
        function navigateToNextMusic(url) {
          window.location.href = url;
        }

        // Event listener for "Next" button
        const nextButton = document.querySelector('.btn[href*="Next"]');
        if (nextButton) {
          nextButton.addEventListener('click', (event) => {
            event.preventDefault(); // Prevent the default navigation

            // Pause Plyr player
            player.pause();

            const nextMusicUrl = nextButton.href;
            navigateToNextMusic(nextMusicUrl);
          });
        }
      });
    </script>
    <script>
      function sharePage() {
        if (navigator.share) {
          navigator.share({
            title: document.title,
            url: window.location.href
          }).then(() => {
            console.log('Page shared successfully.');
          }).catch((error) => {
            console.error('Error sharing page:', error);
          });
        } else {
          console.log('Web Share API not supported.');
        }
      }
    </script>
    <script>
      function sharePageS(musicId, songName) {
        if (navigator.share) {
          const shareUrl = window.location.origin + '/music.php?id=' + musicId;
          navigator.share({
            title: songName,
            url: shareUrl
          }).then(() => {
            console.log('Page shared successfully.');
          }).catch((error) => {
            console.error('Error sharing page:', error);
          });
        } else {
          console.log('Web Share API not supported.');
        }
      }
    </script>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>