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
    <div class="container-fluid">
      <a class="m-1 p-3 position-absolute start-0 top-0 btn border-0 link-body-emphasis text-shadow" href="index.php"><i class="bi bi-chevron-down fs-4 text-stroke"></i></a>
      <div class="row">
        <div class="col-md-6 d-flex justify-content-center align-items-center custom-bg vh-100">
          <div class="bg-transparent rounded-5 w-100" style="max-width: 325px;">
            <div class="position-relative text-shadow p-2 p-md-0">
              <div class="position-relative">
                <div class="text-center mb-3 ratio ratio-1x1">
                  <a data-bs-toggle="modal" data-bs-target="#originalImage"><img src="<?php echo $websiteUrl . '/feeds/music/' . $selectedSong['cover']; ?>" alt="Song Image" class="h-100 w-100 object-fit-cover rounded-4 shadow"></a>
                </div>
                <div class="w-100 d-flex justify-content-start align-items-center">
                  <div class="overflow-auto text-nowrap" style="width: 87%; filter: blur(0px);">
                    <h2 class="text-start text-white fw-bold z-3"><?php echo $selectedSong['title']; ?></h2>
                  </div>
                  <div class="text-shadow pb-2" style="width: 13%;">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#shareLink" class="btn border-0 link-body-emphasis ms-1 ps-3"><i class="bi bi-share-fill"></i></a>
                  </div>
                </div>
                <h6 class="text-start text-white fw-bold mb-3 overflow-auto text-nowrap"><?php echo $selectedSong['artist']; ?> - <?php echo $selectedSong['album']; ?></h6>
                <div class="d-flex justify-content-start align-items-center">
                  <a class="text-decoration-none link-light fw-medium me-auto small" href="#" data-bs-toggle="modal" data-bs-target="#lyricsModal">Lyrics</a>
                  <a class="text-decoration-none link-light fw-medium ms-auto d-md-none d-lg-none" href="#playList">
                    <i class="bi bi-music-note-list fs-4"></i>
                  </a>
                </div>
              </div>
              <div id="music-player" class="w-100 mt-3">
                <div class="d-flex justify-content-start align-items-center fw-medium text-white gap-2">
                  <span class="me-auto small" id="duration"></span>
                  <input type="range" class="w-100 form-range mx-auto" id="duration-slider" value="0">
                  <span class="ms-auto small" id="duration-left"></span>
                </div>
                <audio id="player" class="d-none" controls>
                  <source src="<?php echo $websiteUrl . '/feeds/music/' . $selectedSong['file']; ?>" type="audio/mpeg">
                  Your browser does not support the audio element.
                </audio>
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
        <div class="col-md-6 d-flex justify-content-center align-items-center mt-5 mt-md-0">
          <div class="p-md-3 vh-100 w-100 overflow-y-auto py-2" id="playList">
            <h3 class="text-start text-white text-shadow fw-bold pt-3 mb-3"><i class="bi bi-music-note-list"></i> all song lists</h3>
            <div class="overflow-y-auto" id="autoHeightDiv" style="max-height: 100%;">
              <?php
                $sourceApiUrl = $websiteUrl . '/feeds/music/api_music.php'; // Construct API URL based on user input

                try {
                  $json = @file_get_contents($sourceApiUrl);
                  if ($json === false) {
                    throw new Exception("<h5 class='text-center'>Error fetching data from API</h5>");
                  }

                  $data = json_decode($json, true);

                  if (!is_array($data) || empty($data)) {
                    throw new Exception("<h5 class='text-center'>No data found</h5>");
                  }
                ?>
                  <div class="song-list">
                    <?php foreach ($data as $song): ?>
                      <div id="song_<?php echo $song['id']; ?>" class="link-body-emphasis d-flex justify-content-between align-items-center rounded-4 bg-dark bg-opacity-10 my-2 text-shadow <?php echo ($song['id'] == $selectedSong['id']) ? 'rounded-4 bg-body-tertiary border border-opacity-25 border-light' : ''; ?>">
                        <div class="card-body p-1">
                          <a class="link-body-emphasis text-decoration-none music text-start w-100 text-white btn fw-bold border-0" href="play.php?album=<?php echo urlencode($song['album']); ?>&id=<?php echo $song['id']; ?>">
                            <?php echo $song['title']; ?><br>
                            <small class="small"><?php echo $song['artist']; ?> - <?php echo $song['album']; ?></small>
                          </a>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php
                } catch (Exception $e) {
                  echo "<h5 class='text-center mt-3 fw-bold'>Error or nothing found: </h5>" . $e->getMessage();
                }
              ?>
              <br><br><br><br><br><br><br><br><br><br>
            </div>
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
    <div class="modal fade" id="shareLink" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="card rounded-4 p-4">
            <p class="text-start fw-bold">share</p>
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
    <script>
      // Add this function to scroll to the current song and add active class
      function scrollToCurrentSong() {
        var currentSongId = "<?php echo $selectedSong['id']; ?>";
        var element = document.getElementById("song_" + currentSongId);
        var autoHeightDiv = document.getElementById('autoHeightDiv');

        if (element && autoHeightDiv) {
          // Remove the active class from all song elements
          var allSongElements = autoHeightDiv.querySelectorAll('.rounded-4');
          allSongElements.forEach(function(songElement) {
            songElement.classList.remove('bg-body-tertiary', 'border', 'border-opacity-25', 'border-light');
          });

          // Add the active class to the current song element
          element.classList.add('rounded-4', 'bg-body-tertiary', 'border', 'border-opacity-25', 'border-light');

          // Calculate the scroll position based on the element's position within the container
          var scrollTop = element.offsetTop - autoHeightDiv.offsetTop;

          // Scroll only the container to the calculated position
          autoHeightDiv.scrollTop = scrollTop;
        }
      }

      // Call the function when the page is loaded
      window.addEventListener('load', scrollToCurrentSong);
    </script>
    <script>
      // Get a reference to the element
      const autoHeightDiv = document.getElementById('autoHeightDiv');
    
      // Set the element's height to match the screen's height
      autoHeightDiv.style.height = window.innerHeight + 'px';
    
      // Listen for window resize events to update the height dynamically
      window.addEventListener('resize', () => {
        autoHeightDiv.style.height = window.innerHeight + 'px';
      });
    </script>
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