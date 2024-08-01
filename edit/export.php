<?php
require_once('../auth.php');

// Connect to the database using PDO
$db = new PDO('sqlite:../database.sqlite');

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  // Redirect to index.php if not logged in
  header("Location: ../index.php");
  exit;
}

// Check if an artworkid is provided
if (!isset($_GET['artworkid']) || empty($_GET['artworkid'])) {
  die("No artwork ID specified.");
}

$artworkId = $_GET['artworkid'];

// Retrieve the email of the logged-in user
$email = $_SESSION['email'];

// Check if the logged-in user is the owner of the artwork
$stmt = $db->prepare("SELECT * FROM images WHERE id = :artworkid AND email = :email");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->bindParam(':email', $email);
$stmt->execute();
$image = $stmt->fetch();

// If the image does not exist or does not belong to the logged-in user, show a forbidden error
if (!$image) {
  echo '<meta charset="UTF-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <img src="../icon/403-Error-Forbidden.svg" style="height: 100%; width: 100%;">
       ';
  exit();
}

// Get all child images associated with the current image from the "image_child" table
$stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :image_id");
$stmt->bindParam(':image_id', $artworkId);
$stmt->execute();
$child_images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count the total number of images from "images" table for the specific artworkid
$stmt = $db->prepare("SELECT COUNT(*) as total_images FROM images WHERE id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$total_images = $stmt->fetch()['total_images'];

// Count the total number of images from "image_child" table for the specific artworkid
$stmt = $db->prepare("SELECT COUNT(*) as total_child_images FROM image_child WHERE image_id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$total_child_images = $stmt->fetch()['total_child_images'];

// Calculate the combined total
$total_all_images = $total_images + $total_child_images;

// Function to calculate the size of an image in MB
function getImageSizeInMB($filename) {
  return round(filesize('../images/' . $filename) / (1024 * 1024), 2);
}

// Get the total size of images from 'images' table
$stmt = $db->prepare("SELECT * FROM images WHERE id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the total size of images from 'image_child' table
$stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :artworkid");
$stmt->bindParam(':artworkid', $artworkId);
$stmt->execute();
$image_childs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to format the date
function formatDate($date) {
  return date('Y/F/l jS', strtotime($date));
}

$images_total_size = 0;
foreach ($images as $image) {
  $images_total_size += getImageSizeInMB($image['filename']);
}

$image_child_total_size = 0;
foreach ($image_childs as $image_child) {
  $image_child_total_size += getImageSizeInMB($image_child['filename']);
}

$total_size = $images_total_size + $image_child_total_size;
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download <?php echo $image['title']; ?></title>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
      #progress-container {
        display: none;
      }
      #downloadButton {
        display: block;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <a class="btn border-0 position-absolute top-0 start-0 m-2" href="/edit/?id=<?php echo $image['id']; ?>"><i class="bi bi-chevron-left" style="-webkit-text-stroke: 2px;"></i></a>
      <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="card border-0 bg-body-tertiary p-4 rounded-4 w-100" style="max-width: 70%;">
          <h5>Download Batch</h5>
          <div class="row mb-2 mt-4">
            <label for="" class="col-3 col-form-label">Title</label>
            <div class="col-9">
              <input type="text" class="form-control-plaintext" id="" value="<?php echo $image['title']; ?>" readonly>
            </div>
          </div>
          <div class="row mb-2">
            <label for="" class="col-3 col-form-label">ID</label>
            <div class="col-9">
              <input type="text" class="form-control-plaintext" id="" value="<?php echo htmlspecialchars($image['id']); ?>" readonly>
            </div>
          </div>
          <div class="row mb-2">
            <label for="" class="col-3 col-form-label">Date</label>
            <div class="col-9">
              <input type="text" class="form-control-plaintext" id="" value="<?php echo date('Y/m/d', strtotime($image['date'])); ?>" readonly>
            </div>
          </div>
          <div class="row mb-2">
            <label for="" class="col-3 col-form-label">Items</label>
            <div class="col-9">
              <input type="text" class="form-control-plaintext" id="" value="<?php echo htmlspecialchars($total_all_images); ?>" readonly>
            </div>
          </div>
          <div class="row mb-4">
            <label for="" class="col-3 col-form-label">Size</label>
            <div class="col-9">
              <input type="text" class="form-control-plaintext" id="" value="<?php echo htmlspecialchars($total_size); ?> MB" readonly>
            </div>
          </div>
  
          <!-- Button for downloading images -->
          <a id="downloadButton" class="btn btn-primary fw-bold rounded-4 w-100" href="#" onclick="downloadWithProgressBar(<?php echo htmlspecialchars($artworkId); ?>)">
            <i class="bi bi-download text-stroke"></i> Download All Images
          </a>
  
          <!-- Progress bar container -->
          <div id="progressBarContainer_<?php echo htmlspecialchars($artworkId); ?>" class="progress fw-bold mt-2 rounded-4" style="height: 30px; display: none;">
            <div id="progressBar_<?php echo htmlspecialchars($artworkId); ?>" class="progress-bar progress-bar-animated fw-bold" style="width: 0; height: 30px;">0%</div>
          </div>
          <!-- Time left remaining container -->
          <div id="timeLeftContainer_<?php echo htmlspecialchars($artworkId); ?>" class="mt-4" style="display: none;">
            <p id="timeLeft_<?php echo htmlspecialchars($artworkId); ?>">Time left: 0s</p>
            <p id="speed_<?php echo htmlspecialchars($artworkId); ?>">Speed: 0 MB/s</p>
          </div>
        </div>
      </div>
    </div>
  
    <script>
      function downloadWithProgressBar(artworkId) {
        var progressBar = document.getElementById('progressBar_' + artworkId);
        var progressBarContainer = document.getElementById('progressBarContainer_' + artworkId);
        var timeLeftContainer = document.getElementById('timeLeftContainer_' + artworkId);
        var timeLeftElement = document.getElementById('timeLeft_' + artworkId);
        var speedElement = document.getElementById('speed_' + artworkId);
        var downloadButton = document.getElementById('downloadButton');
  
        // Hide the download button and show progress container
        downloadButton.style.display = 'none';
        progressBarContainer.style.display = 'block';
        timeLeftContainer.style.display = 'block';
  
        // Create a new XMLHttpRequest object
        var xhr = new XMLHttpRequest();
  
        // Variables to estimate time remaining
        var startTime = Date.now();
        var previousLoaded = 0;
  
        // Function to format bytes to MB
        function formatBytes(bytes) {
          return (bytes / (1024 * 1024)).toFixed(2);
        }
  
        // Function to update the progress bar
        function updateProgress(event) {
          if (event.lengthComputable) {
            var percentComplete = (event.loaded / event.total) * 100;
            var now = Date.now();
            var timeElapsed = (now - startTime) / 1000; // Time elapsed in seconds
            var loadedSinceLast = event.loaded - previousLoaded;
            var downloadSpeed = loadedSinceLast / timeElapsed; // Speed in bytes/second
  
            // Estimate the remaining time
            var remainingBytes = event.total - event.loaded;
            var estimatedTimeLeft = remainingBytes / downloadSpeed; // Time left in seconds
  
            progressBar.style.width = percentComplete + '%';
            progressBar.innerHTML = percentComplete.toFixed(2) + '%';
            timeLeftElement.innerHTML = 'Time left: ' + Math.ceil(estimatedTimeLeft) + 's';
            speedElement.innerHTML = 'Speed: ' + formatBytes(downloadSpeed) + ' MB/s';
  
            // Update for the next progress event
            previousLoaded = event.loaded;
            startTime = Date.now();
          }
        }
  
        // Set up the XMLHttpRequest object
        xhr.open('GET', 'download_batch.php?artworkid=' + artworkId, true);
  
        // Set the responseType to 'blob' to handle binary data
        xhr.responseType = 'blob';
  
        // Track progress with the updateProgress function
        xhr.addEventListener('progress', updateProgress);
  
        // On successful download completion
        xhr.onload = function () {
          progressBar.innerHTML = '100%';
          timeLeftElement.innerHTML = 'Time left: 0s';
          speedElement.innerHTML = 'Speed: 0 MB/s';
  
          // Delay hiding the progress bar and showing the button again
          setTimeout(function () {
            progressBarContainer.style.display = 'none';
            timeLeftContainer.style.display = 'none';
            downloadButton.style.display = 'block';
          }, 1000);
  
          // Create a download link for the downloaded file
          var downloadLink = document.createElement('a');
          downloadLink.href = URL.createObjectURL(xhr.response);
          downloadLink.download = 'image_id_' + artworkId + '.zip'; // Updated filename without title
          downloadLink.style.display = 'none';
          document.body.appendChild(downloadLink);
          downloadLink.click(); // Trigger the click event to download the file
          document.body.removeChild(downloadLink); // Remove the link from the document
        };
  
        // Send the XMLHttpRequest to start the download
        xhr.send();
      }
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>