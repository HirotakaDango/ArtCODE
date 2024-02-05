<?php
// Initialize variables
$websiteUrl = '';

// SQLite database connection
$db = new SQLite3('music.sqlite'); // Replace with your actual database file

// Create settings table if it doesn't exist
$createTableQuery = "CREATE TABLE IF NOT EXISTS settings (id INTEGER PRIMARY KEY, website_url TEXT)";
$db->exec($createTableQuery);

// Retrieve website URL from the database
$stmt = $db->prepare("SELECT website_url FROM settings WHERE id = 1");
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);
$websiteUrl = $row ? $row['website_url'] : '';

// Get the song ID and album from the query parameters
$songId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$songAlbum = isset($_GET['album']) ? intval($_GET['album']) : 0;

// Fetch the song details from the API
$sourceApiUrl = $websiteUrl . '/feeds/music/api_music.php';
$json = @file_get_contents($sourceApiUrl);
$data = json_decode($json, true);

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
    <script>
      const player = document.getElementById('player');
      let currentTrackId = <?= $songId ?>;
      let isSeeking = false;

      navigator.mediaSession.setActionHandler('previoustrack', function() {
        currentTrackId = <?= $prevRow ? $prevRow['id'] : 0 ?>;
        const previousTrackUrl = 'play.php?album=<?= $prevRow ? urlencode($prevRow['album']) : '' ?>&id=' + currentTrackId;
        window.location.href = previousTrackUrl;
      });

      navigator.mediaSession.setActionHandler('nexttrack', function() {
        currentTrackId = <?= $nextRow ? $nextRow['id'] : 0 ?>;
        const nextTrackUrl = 'play.php?album=<?= $nextRow ? urlencode($nextRow['album']) : '' ?>&id=' + currentTrackId;
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
      
      @media (max-width: 767px) {
        .fs-custom {
          font-size: 3.5em;
        }
      }
      
      @media (min-width: 768px) {
        .fs-custom {
          font-size: 3em;
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
        background: url('<?php echo $websiteUrl . '/feeds/music/' . $selectedSong['cover']; ?>') center/cover no-repeat fixed;
        filter: blur(10px);
        border-radius: 2em;
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
    <div class="container-fluid d-flex vh-100 justify-content-center align-items-center custom-bg">
      <a class="position-absolute start-0 top-0 btn border-0 link-body-emphasis" href="index.php"><i class="bi bi-chevron-left fs-4 text-stroke"></i></a>
      <div class="bg-body-tertiary bg-opacity-25 rounded-5 w-100" style="max-width: 325px;">
        <div class="position-relative text-shadow p-3">
          <div class="position-relative">
            <div class="text-center mb-2 ratio ratio-1x1">
              <a data-bs-toggle="modal" data-bs-target="#originalImage"><img src="<?php echo $websiteUrl . '/feeds/music/' . $selectedSong['cover']; ?>" alt="Song Image" class="h-100 w-100 object-fit-cover rounded-4 shadow"></a>
            </div>
            <h2 class="text-start text-white fw-bold" style="overflow-x: auto; white-space: nowrap;"><?php echo $selectedSong['title']; ?></h2>
            <h6 class="text-start text-white fw-bold mb-2" style="overflow-x: auto; white-space: nowrap;"><?php echo $selectedSong['artist']; ?> - <?php echo $selectedSong['album']; ?></h6>
            <a class="text-decoration-none link-light small fw-medium" href="#" data-bs-toggle="modal" data-bs-target="#lyricsModal">Lyrics</a>
          </div>
          <div id="music-player" class="w-100 mt-2">
            <div class="d-flex fw-medium text-white">
              <span class="me-auto small" id="duration"></span>
              <span class="ms-auto small" id="duration-left"></span>
            </div>
            <audio id="player" class="d-none" controls>
              <source src="<?php echo $websiteUrl . '/feeds/music/' . $selectedSong['file']; ?>" type="audio/mpeg">
              Your browser does not support the audio element.
            </audio>
            <input type="range" class="w-100 form-range" id="duration-slider" value="0">
          </div>
          <div class="btn-group w-100 align-items-center">
            <a class="btn border-0 link-body-emphasis w-25 text-white text-shadow" href="play.php?play.php?album=<?php echo urlencode($prevRow['album']); ?>&id=<?php echo $prevRow['id']; ?>">
              <i class="bi bi-skip-start-fill fs-custom-3"></i>
            </a>
            <button class="btn border-0 link-body-emphasis w-25 text-white text-shadow" id="playPauseButton" onclick="togglePlayPause()">
              <i class="bi bi-play-circle-fill fs-custom"></i>
            </button>
            <a class="btn border-0 link-body-emphasis w-25 text-white text-shadow" href="play.php?play.php?album=<?php echo urlencode($nextRow['album']); ?>&id=<?php echo $nextRow['id']; ?>">
              <i class="bi bi-skip-end-fill fs-custom-3"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="lyricsModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
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
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const audioPlayer = document.getElementById('player');
        const nextButton = document.querySelector('.btn[href*="Next"]');

        // Autoplay the player when the page loads
        audioPlayer.play();

        audioPlayer.addEventListener('ended', function(event) {
          // Redirect to the next song URL
          window.location.href = "play.php?album=<?php echo urlencode($nextRow['album']); ?>&id=<?php echo $nextRow['id']; ?>";
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
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js" integrity="sha384-Rx+T1VzGupg4BHQYs2gCW9It+akI2MM/mndMCy36UVfodzcJcF0GGLxZIzObiEfa" crossorigin="anonymous"></script>
  </body>
</html>