<?php
// Initialize variables
$websiteUrl = '';

// SQLite database connection
$db = new SQLite3('music.sqlite'); // Replace with your actual database file

// Create settings table if it doesn't exist
$createTableQuery = "CREATE TABLE IF NOT EXISTS settings (id INTEGER PRIMARY KEY, website_url TEXT)";
$db->exec($createTableQuery);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $websiteUrl = $_POST['website_url'];

  // Save website URL to the database
  $stmt = $db->prepare("INSERT OR REPLACE INTO settings (id, website_url) VALUES (1, :website_url)");
  $stmt->bindValue(':website_url', $websiteUrl, SQLITE3_TEXT);
  $stmt->execute();

  // Redirect to the same page to prevent form resubmission
  header('Location: ' . $_SERVER['REQUEST_URI']);
  exit();
}

// Retrieve website URL from the database
$stmt = $db->prepare("SELECT website_url FROM settings WHERE id = 1");
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);
$websiteUrl = $row ? $row['website_url'] : '';

// Fetch the song details from the API
$sourceApiUrl = $websiteUrl . '/feeds/music/api_music.php';
$json = @file_get_contents($sourceApiUrl);
$data = json_decode($json, true);

// Check if there's no ID parameter in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
  // Redirect to the first song if there's no ID parameter
  if (!empty($data)) {
    $firstSong = reset($data);
    header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $firstSong['id']);
    exit();
  } else {
    // Handle the case where there are no songs available
    echo "<h5 class='text-center mt-3 fw-bold'>Error: No songs available</h5>";
    exit();
  }
}

// Get the song ID and album from the query parameters
$songId = intval($_GET['id']);
$songAlbum = isset($_GET['album']) ? intval($_GET['album']) : 0;

// Find the song with the specified ID
$selectedSong = null;
foreach ($data as $song) {
  if ($song['id'] === $songId) {
    $selectedSong = $song;
    break;
  }
}

// Check if the song was found
if (!$selectedSong) {
  // Song not found, handle the error (you can redirect or display an error message)
  echo "<h5 class='text-center mt-3 fw-bold'>Error: Song not found</h5>";
  exit;
}

// Find the index of the selected song in the data array
$selectedSongIndex = array_search($selectedSong, $data);

// Calculate the index for the previous and next songs
$prevIndex = max($selectedSongIndex - 1, 0);
$nextIndex = min($selectedSongIndex + 1, count($data) - 1);

// Get the details of the previous and next songs
$prevRow = $data[$prevIndex];
$nextRow = $data[$nextIndex];
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $selectedSong['title']; ?></title>
    <link rel="icon" type="image/png" href="<?php echo $websiteUrl . '/feeds/music/' . $selectedSong['cover']; ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <meta property="og:image" content="<?php echo $websiteUrl . '/feeds/music/' . $selectedSong['cover']; ?>"/>
    <meta property="og:title" content="<?php echo $selectedSong['title']; ?>"/>
    <meta property="og:description" content="<?php echo $selectedSong['album']; ?>"/>
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/users/?id=<?php echo $user['id']; ?>">
    <script>
      const player = document.getElementById('player');
      let currentTrackId = <?= $songId ?>;
      let isSeeking = false;

      navigator.mediaSession.setActionHandler('previoustrack', function() {
        const previousTrackUrl = '?id=<?php echo ($prevIndex == 0) && ($selectedSongIndex == 0) ? $data[count($data) - 1]['id'] : $prevRow['id']; ?>';
        window.location.href = previousTrackUrl;
      });

      navigator.mediaSession.setActionHandler('nexttrack', function() {
        const nextTrackUrl = '?id=<?php echo ($nextIndex == count($data)-1) && ($selectedSongIndex == count($data)-1) ? $data[0]['id'] : $nextRow['id']; ?>';
        window.location.href = nextTrackUrl;
      });

      // Set metadata for the currently playing media
      const setMediaMetadata = () => {
        const coverPath = '<?php echo $websiteUrl . '/feeds/music/' . $selectedSong['cover']; ?>';
        console.log('Cover Path:', coverPath);

        navigator.mediaSession.metadata = new MediaMetadata({
          title: '<?= htmlspecialchars($selectedSong['title']) ?>',
          artist: '<?= htmlspecialchars($selectedSong['artist']) ?>',
          album: '<?= htmlspecialchars($selectedSong['album']) ?>',
          artwork: [
            { src: coverPath, sizes: '1600x1600', type: 'image/png' },
            // Add additional artwork sizes if needed
          ],
        });
      };
    </script>
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
      
      .text-stroke {
        -webkit-text-stroke: 3px;
      }

      .text-shadow {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);
      }
      
      .fs-custom {
        font-size: 2.5em;
      }

      .fs-custom-2 {
        font-size: 2em;
      }

      .fs-custom-3 {
        font-size: 1em;
      }

      .custom-bg::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        background: url('<?php echo $websiteUrl . '/feeds/music/' . $selectedSong['cover']; ?>') center/cover no-repeat fixed;
        filter: blur(10px);
        z-index: -1;
      }
      
      #duration-slider::-webkit-slider-runnable-track {
        background-color: rgba(255, 255, 255, 0.3); /* Set the color to white */
      }

      #duration-slider::-webkit-slider-thumb {
        background-color: white;
      }

      .box-shadow::-webkit-slider-runnable-track {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }

      .box-shadow::-webkit-slider-thumb {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }
    </style>
  </head>
  <body>
    <div class="container d-flex justify-content-center align-items-center vh-100 custom-bg text-shadow">
      <a class="m-2 position-absolute end-0 top-0 btn border-0 link-body-emphasis text-shadow" href="#" data-bs-toggle="modal" data-bs-target="#settingModal"><i class="bi bi-gear-fill fs-5"></i></a>
      <div class="container p-3 rounded-4 shadow border-0 position-relative" style="max-width: 350px;">
        <a class="position-absolute end-0 top-0 btn border-0 link-body-emphasis text-shadow" href="#" data-bs-toggle="modal" data-bs-target="#optionModal"><i class="bi bi-three-dots-vertical"></i></a>
        <div class="row g-2">
          <div class="col-5">
            <div class="text-center ratio ratio-1x1">
              <a class="shadow" data-bs-toggle="modal" data-bs-target="#originalImage"><img src="<?php echo $websiteUrl . '/feeds/music/' . $selectedSong['cover']; ?>" alt="Song Image" class="h-100 w-100 object-fit-cover rounded-3 shadow"></a>
            </div>
          </div>
          <div class="col-7 d-flex justify-content-center align-items-center">
            <div class="text-center overflow-auto text-nowrap mt-2">
              <h6 class="text-white fw-bold overflow-auto text-nowrap small"><?php echo $selectedSong['title']; ?></h6>
              <h6 class="text-white fw-bold overflow-auto text-nowrap small"><small><?php echo $selectedSong['artist']; ?> - <?php echo $selectedSong['album']; ?></small></h6>
              <div class="btn-group w-100 d-flex justify-content-center align-items-center gap-0">
                <!-- (debugging) <?php echo $prevIndex.'-'.count($data).'-'.$selectedSongIndex.'-'.$data[1]['id']."-".$prevRow['id']; ?> -->
                <a class="btn border-0 link-body-emphasis text-white text-shadow text-start me-auto" href="?id=<?php echo ($prevIndex == 0) && ($selectedSongIndex == 0) ? $data[count($data) - 1]['id'] : $prevRow['id']; ?>">
                  <i class="bi bi-skip-start-fill fs-custom-2"></i>
                </a>
                <button class="btn border-0 link-body-emphasis text-white text-shadow text-center mx-auto" id="playPauseButton" onclick="togglePlayPause()">
                  <i class="bi bi-play-circle-fill fs-custom"></i>
                </button>
                <a class="btn border-0 link-body-emphasis text-white text-shadow text-end ms-auto" href="?id=<?php echo ($nextIndex == count($data)-1) && ($selectedSongIndex == count($data)-1) ? $data[0]['id'] : $nextRow['id']; ?>">
                  <i class="bi bi-skip-end-fill fs-custom-2"></i>
                </a>
                <!-- (debugging) <?php echo $nextIndex.'-'.count($data).'-'.$selectedSongIndex.'-'.$data[0]['id']."-".$nextRow['id']; ?> -->
              </div>
            </div>
          </div>
        </div>
        <div class="mt-2">
          <div id="music-player" class="w-100">
            <div class="d-flex justify-content-start align-items-center fw-medium text-white gap-2">
              <span class="me-auto small" id="duration"></span>
              <input type="range" class="w-100 form-range mx-auto box-shadow" id="duration-slider" value="0">
              <span class="ms-auto small" id="duration-left"></span>
            </div>
            <audio id="player" class="d-none" controls>
              <source src="<?php echo $websiteUrl . '/feeds/music/' . $selectedSong['file']; ?>" type="audio/mpeg">
              Your browser does not support the audio element.
            </audio>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="lyricsModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content bg-dark bg-opacity-50 text-shadow rounded-4 border-0 shadow">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Lyrics</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <?php
              if (!empty($selectedSong['lyrics'])) {
                $messageTextLyrics = $selectedSong['lyrics'];
                $messageTextWithoutTagsLyrics = strip_tags($messageTextLyrics);
                $formattedTextWithLineBreaksLyrics = nl2br($messageTextWithoutTagsLyrics);
                echo "<p style=\"white-space: break-spaces; overflow: hidden;\">$formattedTextWithLineBreaksLyrics</p>";
              } else {
                echo "<h6 class='text-center'>Lyrics are empty.</h6>";
              }
            ?>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="originalImage" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="modal-body position-relative">
            <img class="object-fit-contain h-100 w-100 rounded" src="<?php echo $websiteUrl . '/feeds/music/' . $selectedSong['cover']; ?>">
            <button type="button" class="btn border-0 position-absolute end-0 top-0 m-2" data-bs-dismiss="modal"><i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i></button>
            <a class="btn btn-primary fw-bold w-100 mt-2" href="<?php echo $websiteUrl . '/feeds/music/' . $selectedSong['cover']; ?>" download>Download Cover Image</a>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="optionModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content bg-dark bg-opacity-50 text-shadow rounded-4 border-0 shadow">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Options</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <a class="btn border-0 link-body-emphasis fw-bold w-100 mb-2" href="#" data-bs-toggle="modal" data-bs-target="#lyricsModal">
              Lyrics
            </a>
            <a class="btn border-0 link-body-emphasis fw-bold w-100 mb-2" href="<?php echo $websiteUrl . '/feeds/music/' . $selectedSong['file']; ?>" download="<?php echo $websiteUrl . '/feeds/music/' . $selectedSong['file']; ?>">
              Download
            </a>
            <a class="btn border-0 link-body-emphasis fw-bold w-100" href="#" data-bs-toggle="modal" data-bs-target="#shareLink">
              Share
            </a>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="shareLink" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="card rounded-4 p-4">
            <p class="text-start fw-bold">share</p>
            <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
              <!-- Twitter -->
              <a class="btn rounded-start-4" href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-twitter"></i>
              </a>
                                
              <!-- Line -->
              <a class="btn" href="https://social-plugins.line.me/lineit/share?url=<?php echo urlencode('http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-line"></i>
              </a>
                                
              <!-- Email -->
              <a class="btn" href="mailto:?body=<?php echo urlencode('http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>">
                <i class="bi bi-envelope-fill"></i>
              </a>
                                
              <!-- Reddit -->
              <a class="btn" href="https://www.reddit.com/submit?url=<?php echo urlencode('http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-reddit"></i>
              </a>
                                
              <!-- Instagram -->
              <a class="btn" href="https://www.instagram.com/?url=<?php echo urlencode('http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-instagram"></i>
              </a>
                                
              <!-- Facebook -->
              <a class="btn rounded-end-4" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-facebook"></i>
              </a>
            </div>
            <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
              <!-- WhatsApp -->
              <a class="btn rounded-start-4" href="https://wa.me/?text=<?php echo urlencode('http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-whatsapp"></i>
              </a>
    
              <!-- Pinterest -->
              <a class="btn" href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode('http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-pinterest"></i>
              </a>
    
              <!-- LinkedIn -->
              <a class="btn" href="https://www.linkedin.com/shareArticle?url=<?php echo urlencode('http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-linkedin"></i>
              </a>
    
              <!-- Messenger -->
              <a class="btn" href="https://www.facebook.com/dialog/send?link=<?php echo urlencode('http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-messenger"></i>
              </a>
    
              <!-- Telegram -->
              <a class="btn" href="https://telegram.me/share/url?url=<?php echo urlencode('http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-telegram"></i>
              </a>
    
              <!-- Snapchat -->
              <a class="btn rounded-end-4" href="https://www.snapchat.com/share?url=<?php echo urlencode('http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-snapchat"></i>
              </a>
            </div>
            <div class="input-group">
              <input type="text" id="urlInput1" value="<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" class="form-control border-2 fw-bold" readonly>
              <button class="btn btn-secondary opacity-50 fw-bold" onclick="copyToClipboard1()">
                <i class="bi bi-clipboard-fill"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="settingModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
          <div class="modal-body">
            <form class="container-fluid" method="POST">
              <div class="input-group">
                <input class="form-control bg-dark-subtle border-0 focus-ring focus-ring-dark rounded-start-4 fw-medium" type="text" name="website_url" value="<?php echo $websiteUrl; ?>" placeholder="website url">
                <button class="btn bg-dark-subtle border-0 link-body-emphasis fw-medium rounded-end-4">save</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const audioPlayer = document.getElementById('player');
        const nextButton = document.querySelector('.btn[href*="Next"]');

        // Autoplay the player when the page loads
        audioPlayer.play();

        audioPlayer.addEventListener('ended', function(event) {
          // Redirect to the next song URL
          window.location.href = "?id=<?php echo ($nextIndex == count($data)-1) && ($selectedSongIndex == count($data)-1) ? $data[0]['id'] : $nextRow['id']; ?>";
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

      function copyToClipboard1() {
        var urlInput1 = document.getElementById('urlInput1');
        urlInput1.select();
        urlInput1.setSelectionRange(0, 99999); // For mobile devices

        document.execCommand('copy');
      }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js" integrity="sha384-Rx+T1VzGupg4BHQYs2gCW9It+akI2MM/mndMCy36UVfodzcJcF0GGLxZIzObiEfa" crossorigin="anonymous"></script>
  </body>
</html>