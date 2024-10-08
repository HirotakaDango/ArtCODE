<?php
require_once '../../feeds/music/getID3/getid3/getid3.php';

try {
  $db = new PDO('sqlite:../../database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
  exit();
}

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
  header('Location: /feeds/music/?mode=' . (isset($_GET['mode']) ? $_GET['mode'] : 'grid') . '&by=' . (isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists')));
  exit;
}

// Get the email and artist ID of the selected user
$user_email = $row['email'];
$artist_id = $row['userid'];

// Music file and cover image paths
$musicFile = '../../feeds/music/' . $row['file'];
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
     WHERE music.email = :email AND ((music.album = :album AND music.id > :id) OR music.album > :album)
     ORDER BY music.album ASC, music.id ASC
     LIMIT 1";
$stmtNext = $db->prepare($queryNext);
$stmtNext->bindParam(':id', $id, PDO::PARAM_INT);
$stmtNext->bindParam(':email', $user_email, PDO::PARAM_STR);
$stmtNext->bindParam(':album', $album, PDO::PARAM_STR);
$stmtNext->execute();
$nextRow = $stmtNext->fetch(PDO::FETCH_ASSOC);

if (!$nextRow) {
  // If no next row, fetch the first music record for the artist
  $queryFirstNextArtist = "SELECT music.id, music.file, music.email, music.cover, music.album, music.title, users.id as userid, users.artist
       FROM music
       JOIN users ON music.email = users.email
       WHERE music.email = :email
       ORDER BY music.album ASC, music.id ASC
       LIMIT 1";
  $stmtFirstNextArtist = $db->prepare($queryFirstNextArtist);
  $stmtFirstNextArtist->bindParam(':email', $user_email, PDO::PARAM_STR);
  $stmtFirstNextArtist->execute();
  $nextRow = $stmtFirstNextArtist->fetch(PDO::FETCH_ASSOC);
}

// Fetch previous music record for the specified artist
$queryPrev = "SELECT music.id, music.file, music.email, music.cover, music.album, music.title, users.id as userid, users.artist
     FROM music
     JOIN users ON music.email = users.email
     WHERE music.email = :email AND ((music.album = :album AND music.id < :id) OR music.album < :album)
     ORDER BY music.album DESC, music.id DESC
     LIMIT 1";
$stmtPrev = $db->prepare($queryPrev);
$stmtPrev->bindParam(':id', $id, PDO::PARAM_INT);
$stmtPrev->bindParam(':email', $user_email, PDO::PARAM_STR);
$stmtPrev->bindParam(':album', $album, PDO::PARAM_STR);
$stmtPrev->execute();
$prevRow = $stmtPrev->fetch(PDO::FETCH_ASSOC);

if (!$prevRow) {
  // If no previous row, fetch the last music record for the artist
  $queryLastPrevArtist = "SELECT music.id, music.file, music.email, music.cover, music.album, music.title, users.id as userid, users.artist
       FROM music
       JOIN users ON music.email = users.email
       WHERE music.email = :email
       ORDER BY music.album DESC, music.id DESC
       LIMIT 1";
  $stmtLastPrevArtist = $db->prepare($queryLastPrevArtist);
  $stmtLastPrevArtist->bindParam(':email', $user_email, PDO::PARAM_STR);
  $stmtLastPrevArtist->execute();
  $prevRow = $stmtLastPrevArtist->fetch(PDO::FETCH_ASSOC);
}

// If looping is enabled, set the next and previous to the current song
if ($loopPlaylist) {
  $nextRow = $row;
  $prevRow = $row;
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $row['title']; ?></title>
    <link rel="icon" type="image/png" href="/feeds/music/covers/<?php echo $coverImage; ?>">
    <?php include('../../bootstrapcss.php'); ?>
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

      .text-shadow {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);
      }
      
      @media (max-width: 767px) {
        .fs-custom {
          font-size: 3.5em;
        }
      }
      
      @media (min-width: 768px) {
        .fs-custom {
          font-size: 3em;
        }
        
        .max-md {
          max-width: 335px;
        }
      }

      @media (min-width: 1140px) {
        .max-md {
          max-width: 350px;
        }
      }

      .fs-custom-2 {
        font-size: 1.3em;
      }

      .fs-custom-3 {
        font-size: 2.4em;
      }

      .custom-bg::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        background: url('/feeds/music/covers/<?php echo $coverImage; ?>') center/cover no-repeat fixed;
        filter: blur(10px);
        border-radius: 0em;
        z-index: -1;
      }
      
      #duration-slider::-webkit-slider-runnable-track {
        background-color: rgba(255, 255, 255, 0.3); /* Set the color to white */
      }

      #duration-slider::-webkit-slider-thumb {
        background-color: white;
      }
    </style>
  </head>
  <body>
    <div class="container-fluid custom-bg">
      <a class="z-3 text-shadow position-absolute top-0 start-0 ms-3 mt-2 m-md-1 btn border-0 fw-bold text-start link-body-emphasis" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/music/?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>">
        <i class="bi bi-chevron-down fs-4" style="-webkit-text-stroke: 3px;"></i>
      </a>
      <a class="z-3 text-shadow position-absolute top-0 end-0 me-3 mt-2 m-md-1 btn border-0 fw-bold text-start link-body-emphasis" href="#" data-bs-toggle="modal" data-bs-target="#shareLink">
        <i class="bi bi-share-fill fs-4" style="-webkit-text-stroke: 0.5px;"></i>
      </a>
      <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="bg-transparent rounded-5 w-100 max-md">
          <div class="position-relative text-shadow">
            <div class="position-relative container-fluid p-1">
              <div class="position-relative">
                <div class="text-center mb-2 ratio ratio-1x1">
                  <a data-bs-toggle="modal" data-bs-target="#originalImage"><img src="/feeds/music/covers/<?php echo $coverImage; ?>" alt="Song Image" class="h-100 w-100 object-fit-cover rounded-4 shadow"></a>
                </div>
              </div>
              <h3 class="text-start text-white fw-bold" style="overflow-x: auto; white-space: nowrap;"><?php echo $row['title']; ?></h3>
              <?php include('play_info_desc_simple.php'); ?>
            </div>
            <div class="container-fluid px-2">
              <?php include('info_option_simple.php'); ?>
              <?php include('player_card_artist_simple.php'); ?>
            </div>
            <div class="btn-group w-100 d-flex justify-content-center align-items-center gap-2 my-2 my-md-0">
              <a class="btn border-0 link-body-emphasis w-25 text-white text-shadow text-start me-auto" href="play_artist_repeat_simple.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&album=<?php echo urlencode($row['album']); ?>&id=<?php echo $row['id']; ?>">
                <i class="bi bi-repeat fs-custom-2"></i>
              </a>
              <a class="btn border-0 link-body-emphasis w-25 text-white text-shadow text-start me-auto" href="play_artist_repeat_simple?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&album=<?php echo urlencode($prevRow['album']); ?>&id=<?php echo $prevRow['id']; ?>">
                <i class="bi bi-skip-start-fill fs-custom-3"></i>
              </a>
              <button class="btn border-0 link-body-emphasis w-25 text-white text-shadow text-center mx-auto" id="playPauseButton" onclick="togglePlayPause()">
                <i class="bi bi-play-circle-fill fs-custom"></i>
              </button>
              <a class="btn border-0 link-body-emphasis w-25 text-white text-shadow text-end ms-auto" href="play_artist_repeat_simple?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&album=<?php echo urlencode($nextRow['album']); ?>&id=<?php echo $nextRow['id']; ?>">
                <i class="bi bi-skip-end-fill fs-custom-3"></i>
              </a>
              <a class="btn border-0 link-body-emphasis w-25 text-white text-shadow text-end ms-auto" href="play_artist_shuffle_simple.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&album=<?php echo urlencode($row['album']); ?>&id=<?php echo $row['id']; ?>">
                <i class="bi bi-shuffle fs-custom-2"></i>
              </a>
            </div>
          </div>
        </div>
        <div class="modal fade" id="songInfo" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0">
              <div class="modal-header border-0">
                <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">Information</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body p-0">
                <div class="accordion" id="accordionPanelsStayOpenExample">
                  <div class="accordion-item border-0">
                    <h2 class="accordion-header">
                      <button class="accordion-button fw-medium rounded-0" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                        Song's Information
                      </button>
                    </h2>
                    <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show">
                      <div class="accordion-body">
                        <div class="metadata">
                          <div class="mb-2 row">
                            <label for="title" class="col-4 col-form-label text-nowrap fw-medium">Title</label>
                            <div class="col-8">
                              <p class="form-control-plaintext fw-bold text-white" id="title"><?php echo $row['title']; ?></p>
                            </div>
                          </div>
                          <div class="mb-2 row">
                            <label for="artist" class="col-4 col-form-label text-nowrap fw-medium">Artist</label>
                            <div class="col-8">
                              <p class="form-control-plaintext fw-bold" id="artist"><a class="text-decoration-none text-white" href="artist.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&id=<?php echo $row['userid']; ?>"><?php echo $row['artist']; ?></a></p>
                            </div>
                          </div>
                          <div class="mb-2 row">
                            <label for="album" class="col-4 col-form-label text-nowrap fw-medium">Album</label>
                            <div class="col-8">
                              <p class="form-control-plaintext fw-bold" id="album"><a class="text-decoration-none text-white" href="album.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&album=<?php echo $row['album']; ?>"><?php echo $row['album']; ?></a></p>
                            </div>
                          </div>
                          <div class="mb-2 row">
                            <label for="duration" class="col-4 col-form-label text-nowrap fw-medium">Duration</label>
                            <div class="col-8">
                              <p class="form-control-plaintext fw-bold text-white" id="duration"><?= $duration ?></p>
                            </div>
                          </div>
                          <div class="mb-2 row">
                            <label for="bitrate" class="col-4 col-form-label text-nowrap fw-medium">Bitrate</label>
                            <div class="col-8">
                              <p class="form-control-plaintext fw-bold text-white" id="bitrate"><?= $bitrate ?></p>
                            </div>
                          </div>
                          <div class="mb-2 row">
                            <label for="sampleRate" class="col-4 col-form-label text-nowrap fw-medium">Sample Rate</label>
                            <div class="col-8">
                              <p class="form-control-plaintext fw-bold text-white" id="sampleRate"><?= $sampleRate ?></p>
                            </div>
                          </div>
                          <div class="mb-2 row">
                            <label for="size" class="col-4 col-form-label text-nowrap fw-medium">Size</label>
                            <div class="col-8">
                              <p class="form-control-plaintext fw-bold text-white" id="size"><?= $size ?></p>
                            </div>
                          </div>
                          <div class="mb-3 row">
                            <label for="audioType" class="col-4 col-form-label text-nowrap fw-medium">Audio Type</label>
                            <div class="col-8">
                              <p class="form-control-plaintext fw-bold text-white" id="audioType"><?= $audioType ?></p>
                            </div>
                          </div>
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
                              echo "<h6 class='text-center'>Descriptions are empty.</h6>";
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
                              echo "<h6 class='text-center'>Lyrics are empty.</h6>";
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
      </div>
    </div>
    <?php include('share_option.php'); ?>
    <div class="modal fade" id="originalImage" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="modal-body position-relative">
            <img class="object-fit-contain h-100 w-100 rounded" src="/feeds/music/covers/<?php echo $coverImage; ?>">
            <button type="button" class="btn border-0 position-absolute end-0 top-0 m-2" data-bs-dismiss="modal"><i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i></button>
            <a class="btn btn-primary fw-bold w-100 mt-2" href="/feeds/music/covers/<?php echo $coverImage; ?>" download>Download Cover Image</a>
          </div>
        </div>
      </div>
    </div>
    <script>
      const player = document.getElementById('player');
      let currentTrackId = <?= $id ?>;
      let isSeeking = false;

      navigator.mediaSession.setActionHandler('previoustrack', function() {
        currentTrackId = <?= $prevRow ? $prevRow['id'] : 0 ?>;
        const previousTrackUrl = 'play_artist_repeat_simple?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&album=<?= $prevRow ? urlencode($prevRow['album']) : '' ?>&id=' + currentTrackId;
        window.location.href = previousTrackUrl;
      });

      navigator.mediaSession.setActionHandler('nexttrack', function() {
        currentTrackId = <?= $nextRow ? $nextRow['id'] : 0 ?>;
        const nextTrackUrl = 'play_artist_repeat_simple?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&album=<?= $nextRow ? urlencode($nextRow['album']) : '' ?>&id=' + currentTrackId;
        window.location.href = nextTrackUrl;
      });

      // Set metadata for the currently playing media
      const setMediaMetadata = () => {
        const coverPath = '/feeds/music/covers/<?= htmlspecialchars($row['cover']) ?>';
        console.log('Cover Path:', coverPath);

        navigator.mediaSession.metadata = new MediaMetadata({
          title: '<?= htmlspecialchars($row['title']) ?>',
          artist: '<?= htmlspecialchars($row['artist']) ?>',
          album: '<?= htmlspecialchars($row['album']) ?>',
          artwork: [
            { src: coverPath, sizes: '1600x1600', type: 'image/png' },
            // Add additional artwork sizes if needed
          ],
        });
      };

      // Call the function to set metadata when the page loads
      setMediaMetadata();

      // Event listener for seeking
      player.addEventListener('timeupdate', function() {
        if (!isSeeking) {
          // Update the current playback position for the media session
          navigator.mediaSession.setPositionState({
            duration: player.duration,
            playbackRate: player.playbackRate,
            position: player.currentTime,
          });
        }
      });

      // Event listener for slider input
      const slider = document.getElementById('music-slider');
      slider.addEventListener('input', function() {
        isSeeking = true;
        // Update the playback position when the slider is moved
        const newPosition = (slider.value / 100) * player.duration;
        player.currentTime = newPosition;
      });

      // Event listener for slider release
      slider.addEventListener('mouseup', function() {
        isSeeking = false;
        // Resume playback after seeking
        if (!player.paused) {
          player.play();
        }
      });

      // Notification button to show the custom notification
      document.getElementById('show-notification-btn').addEventListener('click', function() {
        showCustomNotification();
      });

      // Function to show a custom notification
      const showCustomNotification = () => {
        const options = {
          body: 'Now playing: ' + '<?= htmlspecialchars($row['title']) ?>',
          icon: '/feeds/music/covers/<?= htmlspecialchars($row['cover']) ?>',
          actions: [
            { action: 'prev', title: 'Previous' },
            { action: 'play', title: 'Play' },
            { action: 'next', title: 'Next' },
          ],
        };

        const notification = new Notification('Music Player', options);

        notification.addEventListener('notificationclick', function(event) {
          const action = event.action;
          handleNotificationAction(action);
        });
      };

      // Function to handle notification actions
      const handleNotificationAction = (action) => {
        switch (action) {
          case 'prev':
            // Handle previous track action
            break;
          case 'play':
            // Handle play/pause action
            break;
          case 'next':
            // Handle next track action
            break;
          default:
            // Handle default action
            break;
        }
      };
    </script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const audioPlayer = document.getElementById('player');
        const nextButton = document.querySelector('.btn[href*="Next"]');

        // Autoplay the player when the page loads
        audioPlayer.play();

        audioPlayer.addEventListener('ended', function(event) {
          // Redirect to the next song URL
          window.location.href = "play_artist_repeat_simple?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&album=<?php echo urlencode($row['album']); ?>&id=<?php echo $row['id']; ?>";
        });

        // Event listener for "Next" button
        if (nextButton) {
          nextButton.addEventListener('click', (event) => {
            event.preventDefault(); // Prevent the default navigation

            // Pause audio player
            audioPlayer.pause();

            const nextMusicUrl = nextButton.href;
            navigateToNextMusic(nextMusicUrl);
          });
        }

        // Function to navigate to the next music page
        function navigateToNextMusic(url) {
          window.location.href = url;
        }
      });

      const audioPlayer = document.getElementById('player');
      const durationSlider = document.getElementById('duration-slider');
      const durationLabel = document.getElementById('duration');
      const durationLeftLabel = document.getElementById('duration-left');
      const playPauseButton = document.getElementById('playPauseButton');

      function togglePlayPause() {
        if (audioPlayer.paused) {
          audioPlayer.play();
          playPauseButton.innerHTML = '<i class="bi bi-pause-circle-fill fs-custom"></i>';
        } else {
          audioPlayer.pause();
          playPauseButton.innerHTML = '<i class="bi bi-play-circle-fill fs-custom"></i>';
        }
      }

      audioPlayer.addEventListener('play', () => {
        playPauseButton.innerHTML = '<i class="bi bi-pause-circle-fill fs-custom"></i>';
      });

      audioPlayer.addEventListener('pause', () => {
        playPauseButton.innerHTML = '<i class="bi bi-play-circle-fill fs-custom"></i>';
      });

      function updateDurationLabels() {
        durationLabel.textContent = formatTime(audioPlayer.currentTime);
        durationLeftLabel.textContent = formatTime(audioPlayer.duration - audioPlayer.currentTime);
      }

      function formatTime(timeInSeconds) {
        const minutes = Math.floor(timeInSeconds / 60);
        const seconds = Math.floor(timeInSeconds % 60);
        return `${minutes}:${String(seconds).padStart(2, '0')}`;
      }

      function togglePlayPause() {
        if (audioPlayer.paused) {
          audioPlayer.play();
        } else {
          audioPlayer.pause();
        }
      }

      function setDefaultDurationLabels() {
        durationLabel.textContent = "0:00";
        durationLeftLabel.textContent = "0:00";
      }

      setDefaultDurationLabels(); // Set default values

      function getLocalStorageKey() {
        const album = "<?php echo urlencode($nextRow['album']); ?>";
        const id = "<?php echo $nextRow['id']; ?>";
        return `savedPlaytime_${album}_${id}`;
      }

      // Function to store the current playtime in localStorage
      function savePlaytime() {
        localStorage.setItem(getLocalStorageKey(), audioPlayer.currentTime);
      }

      // Function to retrieve and set the saved playtime
      function setSavedPlaytime() {
        const savedPlaytime = localStorage.getItem(getLocalStorageKey());
        if (savedPlaytime !== null) {
          audioPlayer.currentTime = parseFloat(savedPlaytime);
          updateDurationLabels();
        }
      }

      // Function to check if the song has ended and reset playtime
      function checkSongEnded() {
        if (audioPlayer.currentTime === audioPlayer.duration) {
          audioPlayer.currentTime = 0; // Reset playtime to the beginning
          savePlaytime(); // Save the updated playtime
          updateDurationLabels(); // Update duration labels
        }
      }

      // Add event listener to update playtime and save it to localStorage
      audioPlayer.addEventListener('timeupdate', () => {
        checkSongEnded(); // Check if the song has ended
        savePlaytime(); // Save the current playtime
        durationSlider.value = (audioPlayer.currentTime / audioPlayer.duration) * 100;
        updateDurationLabels();
      });

      // Add event listener to set the saved playtime when the page loads
      window.addEventListener('load', setSavedPlaytime);

      audioPlayer.addEventListener('loadedmetadata', () => {
        setDefaultDurationLabels(); // Reset default values
        durationLabel.textContent = formatTime(audioPlayer.duration);
      });

      durationSlider.addEventListener('input', () => {
        const seekTime = (durationSlider.value / 100) * audioPlayer.duration;
        audioPlayer.currentTime = seekTime;
        updateDurationLabels();
      });

      // Get references to the player and controls
      const speedControl = document.getElementById('speed');
      const volumeControl = document.getElementById('volume');

      // Add event listeners to update playback speed and volume
      speedControl.addEventListener('change', () => {
        player.playbackRate = parseFloat(speedControl.value);
      });

      // Retrieve the volume value from localStorage (if available)
      const savedVolume = localStorage.getItem('savedVolume') || 1;

      // Set the initial volume using the saved value
      player.volume = parseFloat(savedVolume);
      volumeControl.value = savedVolume;

      // Add event listener to update volume and save it to localStorage
      volumeControl.addEventListener('input', () => {
        const newVolume = parseFloat(volumeControl.value);
        player.volume = newVolume;

        // Save the new volume value to localStorage
        localStorage.setItem('savedVolume', newVolume.toString());
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

      function copyToClipboard1() {
        var urlInput1 = document.getElementById('urlInput1');
        urlInput1.select();
        urlInput1.setSelectionRange(0, 99999); // For mobile devices

        document.execCommand('copy');
      }

      function sharePageS(musicId, songName) {
        if (navigator.share) {
          const shareUrl = `${window.location.origin}/play_artist_repeat_simple?id=${musicId}&mode=<?php echo $mode; ?>&by=<?php echo $by; ?>&album=<?php echo $album; ?>&id=<?php echo $id; ?>`;
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