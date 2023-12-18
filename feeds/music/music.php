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
                <a data-bs-toggle="modal" data-bs-target="#originalImage"><img src="covers/<?php echo $coverImage; ?>" alt="Song Image" class="img-fluid object-fit-cover rounded shadow"></a>
              </div>
              <button type="button" class="btn btn-dark opacity-50 position-absolute top-0 start-0 mt-1 ms-1 rounded-1" data-bs-toggle="modal" data-bs-target="#songInfo">
                <i class="bi bi-info-circle-fill"></i>
              </button>
            </div>
          </div>
          <div class="position-relative d-md-none d-lg-none">
            <div class="text-center mb-2 ratio ratio-1x1">
              <a data-bs-toggle="modal" data-bs-target="#originalImage"><img src="covers/<?php echo $coverImage; ?>" alt="Song Image" class="img-fluid object-fit-cover rounded shadow"></a>
            </div>
            <button type="button" class="btn btn-dark opacity-50 position-absolute top-0 start-0 mt-1 ms-1 rounded-1" data-bs-toggle="modal" data-bs-target="#songInfo">
              <i class="bi bi-info-circle-fill"></i>
            </button>
          </div>
          <div class="modal fade" id="songInfo" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
              <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-0">
                  <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">Information</h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                  <div class="accordion" id="accordionPanelsStayOpenExample">
                    <div class="accordion-item border-0">
                      <h2 class="accordion-header">
                        <button class="accordion-button fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                          Song's Information
                        </button>
                      </h2>
                      <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show">
                        <div class="accordion-body">
                          <div class="metadata">
                            <div class="mb-2 row">
                              <label for="title" class="col-4 col-form-label text-nowrap fw-medium">Title :</label>
                              <div class="col-8">
                                <p class="form-control-plaintext fw-bold" id="title"><a class="text-decoration-none text-white" href="#"><?php echo $row['title']; ?></a></p>
                              </div>
                            </div>
                            <div class="mb-2 row">
                              <label for="artist" class="col-4 col-form-label text-nowrap fw-medium">Artist :</label>
                              <div class="col-8">
                                <p class="form-control-plaintext fw-bold" id="artist"><a class="text-decoration-none text-white" href="artist.php?id=<?php echo $row['userid']; ?>"><?php echo $row['artist']; ?></a></p>
                              </div>
                            </div>
                            <div class="mb-2 row">
                              <label for="album" class="col-4 col-form-label text-nowrap fw-medium">Album :</label>
                              <div class="col-8">
                                <p class="form-control-plaintext fw-bold" id="album"><a class="text-decoration-none text-white" href="album.php?album=<?php echo $row['album']; ?>"><?php echo $row['album']; ?></a></p>
                              </div>
                            </div>
                            <div class="mb-2 row">
                              <label for="duration" class="col-4 col-form-label text-nowrap fw-medium">Duration :</label>
                              <div class="col-8">
                                <p class="form-control-plaintext fw-bold text-white" id="duration"><?= $duration ?></p>
                              </div>
                            </div>
                            <div class="mb-2 row">
                              <label for="bitrate" class="col-4 col-form-label text-nowrap fw-medium">Bitrate :</label>
                              <div class="col-8">
                                <p class="form-control-plaintext fw-bold text-white" id="bitrate"><?= $bitrate ?></p>
                              </div>
                            </div>
                            <div class="mb-2 row">
                              <label for="size" class="col-4 col-form-label text-nowrap fw-medium">Size :</label>
                              <div class="col-8">
                                <p class="form-control-plaintext fw-bold text-white" id="size"><?= $size ?></p>
                              </div>
                            </div>
                            <div class="mb-3 row">
                              <label for="audioType" class="col-4 col-form-label text-nowrap fw-medium">Audio Type :</label>
                              <div class="col-8">
                                <p class="form-control-plaintext fw-bold text-white" id="audioType"><?= $audioType ?></p>
                              </div>
                            </div>
                            <a class="btn btn-primary fw-bold w-100" href="<?php echo $row['file']; ?>" download>Download Song</a>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="accordion-item border-0">
                      <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
                          Description
                        </button>
                      </h2>
                      <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse">
                        <div class="accordion-body fw-medium">
                          <p style="white-space: break-spaces; overflow: hidden;">
                            <?php
                              $novelText = isset($row['description']) ? $row['description'] : '';

                              if (!empty($novelText)) {
                                $paragraphs = explode("\n", $novelText);

                                foreach ($paragraphs as $index => $paragraph) {
                                  $messageTextWithoutTags = strip_tags($paragraph);
                                  $pattern = '/\bhttps?:\/\/\S+/i';

                                  $formattedText = preg_replace_callback($pattern, function ($matches) {
                                    $url = htmlspecialchars($matches[0]);

                                    // Check if the URL ends with .png, .jpg, .jpeg, or .webp
                                    if (preg_match('/\.(png|jpg|jpeg|webp)$/i', $url)) {
                                      return '<a href="' . $url . '" target="_blank"><img class="img-fluid rounded-4" loading="lazy" src="' . $url . '" alt="Image"></a>';
                                    } elseif (strpos($url, 'youtube.com') !== false) {
                                      // If the URL is from YouTube, embed it as an iframe with a very low-resolution thumbnail
                                      $videoId = getYouTubeVideoId($url);
                                      if ($videoId) {
                                        $thumbnailUrl = 'https://img.youtube.com/vi/' . $videoId . '/default.jpg';
                                        return '<div class="w-100 overflow-hidden position-relative ratio ratio-16x9"><iframe loading="lazy" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" class="rounded-4 position-absolute top-0 bottom-0 start-0 end-0 w-100 h-100 border-0 shadow" src="https://www.youtube.com/embed/' . $videoId . '" frameborder="0" allowfullscreen></iframe></div>';
                                      } else {
                                        return '<a href="' . $url . '">' . $url . '</a>';
                                      }
                                    } else {
                                      return '<a href="' . $url . '">' . $url . '</a>';
                                    }
                                  }, $messageTextWithoutTags);

                                  echo "<p style=\"white-space: break-spaces; overflow: hidden;\">$formattedText</p>";
                                }
                              } else {
                                echo "Sorry, no text...";
                              }

                              if (!function_exists('getYouTubeVideoId')) {
                                function getYouTubeVideoId($url)
                                {
                                  $videoId = '';
                                  $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
                                  if (preg_match($pattern, $url, $matches)) {
                                    $videoId = $matches[1];
                                  }
                                  return $videoId;
                                }
                              }
                            ?>
                          </p>
                        </div>
                      </div>
                    </div>
                    <div class="accordion-item border-0">
                      <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="false" aria-controls="panelsStayOpen-collapseThree">
                          Lyrics
                        </button>
                      </h2>
                      <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse">
                        <div class="accordion-body fw-medium">
                          <p style="white-space: break-spaces; overflow: hidden;">
                            <?php
                              if (!empty($row['lyrics'])) {
                                $messageTextLyrics = $row['lyrics'];
                                $messageTextWithoutTagsLyrics = strip_tags($messageTextLyrics);
                                $formattedTextWithLineBreaksLyrics = nl2br($messageTextWithoutTagsLyrics);
                                echo "<p style=\"white-space: break-spaces; overflow: hidden;\">$formattedTextWithLineBreaksLyrics</p>";
                              } else {
                                echo "Lyrics are empty.";
                              }
                            ?>
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="d-md-none d-lg-none my-auto">
            <div class="d-flex justify-content-center btn-group">
              <?php if ($prevRow): ?>
                <a href="music.php?album=<?php echo urlencode($prevRow['album']); ?>&id=<?php echo $prevRow['id']; ?>" class="btn float-end text-white"><i class="bi bi-skip-start-fill display-1"></i></a>
              <?php endif; ?>
              <button class="text-decoration-none btn text-white d-md-none d-lg-none" data-bs-toggle="modal" data-bs-target="#exampleModal">
                <i class="bi bi-music-note-list display-1"></i>
              </button>
              <?php if ($nextRow): ?>
                <a href="music.php?album=<?php echo urlencode($nextRow['album']); ?>&id=<?php echo $nextRow['id']; ?>" class="btn float-end text-white"><i class="bi bi-skip-end-fill display-1"></i></a>
              <?php endif; ?>
            </div>
          </div> 
          <div class="w-100 bg-dark fixed-bottom">
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
                  <button type="submit" class="btn" name="unfavorite" style="color: #4A5464;">
                    <i class="bi bi-heart-fill fs-5"></i>
                  </button>
                </form>
              <?php } else { ?>
                <form method="POST">
                  <input type="hidden" name="music_id" value="<?php echo $row['id']; ?>">
                  <button type="submit" class="btn" name="favorite" style="color: #4A5464;">
                    <i class="bi bi-heart fs-5"></i>
                  </button>
                </form>
              <?php } ?>
              <div class="d-none d-md-block d-lg-block">
                <div class="btn-group">
                  <?php if ($prevRow): ?>
                    <a href="music.php?album=<?php echo urlencode($prevRow['album']); ?>&id=<?php echo $prevRow['id']; ?>" class="btn float-end fw-bold" style="color: #4A5464;"><i class="bi bi-skip-start-fill fs-3"></i></a>
                  <?php endif; ?>
                  <?php if ($nextRow): ?>
                    <a href="music.php?album=<?php echo urlencode($nextRow['album']); ?>&id=<?php echo $nextRow['id']; ?>" class="btn float-end fw-bold" style="color: #4A5464;"><i class="bi bi-skip-end-fill fs-3"></i></a>
                  <?php endif; ?>
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
                  <h1 class="modal-title fw-bold fs-5" id="exampleModalLabel"><i class="bi bi-music-note-list"></i> song list from <?php echo $row['artist']; ?></h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <?php foreach ($allRows as $song): ?>
                    <div class="d-flex justify-content-between align-items-center border-bottom">
                      <a class="text-decoration-none music text-start w-100 text-white btn fw-bold" href="music.php?album=<?php echo urlencode($song['album']); ?>&id=<?php echo $song['id']; ?>">
                        <?php echo $song['title']; ?><br>
                        <small class="text-muted"><?php echo $song['artist']; ?> - <?php echo $song['album']; ?></small>
                      </a>
                      <div class="dropdown dropdown-menu-end">
                        <button class="text-decoration-none text-white btn fw-bold" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu">
                          <li><button class="dropdown-item" onclick="sharePageS('<?php echo $song['id']; ?>', '<?php echo $song['title']; ?>')"><i class="bi bi-share-fill"></i> share</button></li>
                          <li><a class="dropdown-item" href="artist.php?id=<?php echo $song['userid']; ?>"><i class="bi bi-person-fill"></i> show artist</a></li>
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
              <h3 class="text-start fw-bold"><i class="bi bi-music-note-list"></i> song list from <?php echo $row['artist']; ?></h3>
              <?php foreach ($allRows as $song): ?>
                <div class="d-flex justify-content-between align-items-center border-bottom">
                  <a class="text-decoration-none music text-start w-100 text-white btn fw-bold" href="music.php?album=<?php echo urlencode($song['album']); ?>&id=<?php echo $song['id']; ?>">
                    <?php echo $song['title']; ?><br>
                    <small class="text-muted"><?php echo $song['artist']; ?> - <?php echo $song['album']; ?></small>
                  </a>
                  <div class="dropdown dropdown-menu-end">
                    <button class="text-decoration-none text-white btn fw-bold" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical"></i></button>
                    <ul class="dropdown-menu">
                      <li><button class="dropdown-item" onclick="sharePageS('<?php echo $song['id']; ?>', '<?php echo $song['title']; ?>')"><i class="bi bi-share-fill"></i> share</button></li>
                      <li><a class="dropdown-item" href="artist.php?id=<?php echo $song['userid']; ?>"><i class="bi bi-person-fill"></i> show artist</a></li>
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
              <a class="btn rounded-start-4" href="https://twitter.com/intent/tweet?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?album=' . $row['album'] . '&id=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-twitter"></i>
              </a>
                                
              <!-- Line -->
              <a class="btn" href="https://social-plugins.line.me/lineit/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?album=' . $row['album'] . '&id=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-line"></i>
              </a>
                                
              <!-- Email -->
              <a class="btn" href="mailto:?body=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?album=' . $row['album'] . '&id=' . $row['id']; ?>">
                <i class="bi bi-envelope-fill"></i>
              </a>
                                
              <!-- Reddit -->
              <a class="btn" href="https://www.reddit.com/submit?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?album=' . $row['album'] . '&id=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-reddit"></i>
              </a>
                                
              <!-- Instagram -->
              <a class="btn" href="https://www.instagram.com/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?album=' . $row['album'] . '&id=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-instagram"></i>
              </a>
                                
              <!-- Facebook -->
              <a class="btn rounded-end-4" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?album=' . $row['album'] . '&id=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-facebook"></i>
              </a>
            </div>
            <div class="btn-group w-100" role="group" aria-label="Share Buttons">
              <!-- WhatsApp -->
              <a class="btn rounded-start-4" href="https://wa.me/?text=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?album=' . $row['album'] . '&id=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-whatsapp"></i>
              </a>
    
              <!-- Pinterest -->
              <a class="btn" href="https://pinterest.com/pin/create/button/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?album=' . $row['album'] . '&id=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-pinterest"></i>
              </a>
    
              <!-- LinkedIn -->
              <a class="btn" href="https://www.linkedin.com/shareArticle?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?album=' . $row['album'] . '&id=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-linkedin"></i>
              </a>
    
              <!-- Messenger -->
              <a class="btn" href="https://www.facebook.com/dialog/send?link=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?album=' . $row['album'] . '&id=' . $row['id']; ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-messenger"></i>
              </a>
    
              <!-- Telegram -->
              <a class="btn" href="https://telegram.me/share/url?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?album=' . $row['album'] . '&id=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-telegram"></i>
              </a>
    
              <!-- Snapchat -->
              <a class="btn rounded-end-4" href="https://www.snapchat.com/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/music/music.php?album=' . $row['album'] . '&id=' . $row['id']; ?>" target="_blank" rel="noopener noreferrer">
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
            <img class="object-fit-contain h-100 w-100 rounded" src="covers/<?php echo $coverImage; ?>">
            <button type="button" class="btn border-0 position-absolute end-0 top-0 m-2" data-bs-dismiss="modal"><i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i></button>
            <a class="btn btn-primary fw-bold w-100 mt-2" href="covers/<?php echo $coverImage; ?>" download>Download Cover Image</a>
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