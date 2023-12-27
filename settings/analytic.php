<?php
require_once('../auth.php');

// Connect to the SQLite database
$db = new SQLite3('../database.sqlite');

// Get the logged-in user's email
$email = $_SESSION['email'];

// Count the number of image filenames for the logged-in user from the 'images' table
$stmt = $db->prepare('SELECT COUNT(*) FROM images WHERE email = :email');
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();
$count_images = $result->fetchArray()[0];

// Count the number of image filenames for the logged-in user from the 'image_child' table
$stmt = $db->prepare('SELECT COUNT(*) FROM image_child WHERE email = :email');
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();
$count_image_child = $result->fetchArray()[0];

// Total count of images from both tables
$total_count = $count_images + $count_image_child;

// Count the number of unique tags for the logged-in user from the 'images' table
$stmt = $db->prepare('SELECT tags FROM images WHERE email = :email AND tags != "free image"');
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();

$unique_tags = array();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  $tags = explode(',', $row['tags']);
  foreach ($tags as $tag) {
    $trimmed_tag = trim($tag);
    if (!in_array($trimmed_tag, $unique_tags)) {
      array_push($unique_tags, $trimmed_tag);
    }
  }
}

$tag_count = count($unique_tags);

// Loop through each image from the 'images' table and get the size
$total_size_images = 0;
$stmt = $db->prepare('SELECT filename FROM images WHERE email = :email');
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  $filename = $row['filename'];
  $filepath = "../images/$filename";
  if (file_exists($filepath)) {
    $filesize = filesize($filepath);
    $total_size_images += $filesize;
  }
}

// Loop through each image from the 'image_child' table and get the size
$total_size_image_child = 0;
$stmt = $db->prepare('SELECT filename FROM image_child WHERE email = :email');
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  $filename = $row['filename'];
  $filepath = "../images/$filename";
  if (file_exists($filepath)) {
    $filesize = filesize($filepath);
    $total_size_image_child += $filesize;
  }
}

// Count the number of videos for the logged-in user from the 'videos' table
$stmt = $db->prepare('SELECT COUNT(*) FROM videos WHERE email = :email');
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();
$count_videos = $result->fetchArray()[0];

// Count the number of music files for the logged-in user from the 'music' table
$stmt = $db->prepare('SELECT COUNT(*) FROM music WHERE email = :email');
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();
$count_music = $result->fetchArray()[0];

// Loop through each video from the 'videos' table and get the size
$total_size_videos = 0;
$stmt = $db->prepare('SELECT video FROM videos WHERE email = :email');
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  $video_filename = $row['video'];
  $video_filepath = "../feeds/minutes/$video_filename";
  if (file_exists($video_filepath)) {
    $video_filesize = filesize($video_filepath);
    $total_size_videos += $video_filesize;
  }
}

// Loop through each music file from the 'music' table and get the size
$total_size_music = 0;
$stmt = $db->prepare('SELECT file FROM music WHERE email = :email');
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  $music_filename = $row['file'];
  $music_filepath = "../feeds/music/$music_filename";
  if (file_exists($music_filepath)) {
    $music_filesize = filesize($music_filepath);
    $total_size_music += $music_filesize;
  }
}

// Total size of images, videos, and music in MB
$total_size_mb = round(($total_size_images + $total_size_image_child + $total_size_videos + $total_size_music) / (1024 * 1024), 2);

// Close the database connection
$db->close();
?>

    <?php include('setheader.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <div class="mt-3 mb-3">
      <h5 class="card-title text-center my-3 text-dark fw-bold">User's Analytical Data</h5>
      <div class="row">
        <div class="col-md-6">
          <div class="card border-0">
            <div class="d-flex justify-content-center">
              <canvas id="myChart" ></canvas>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="d-flex align-items-center justify-content-center">
            <div class="container mt-4 pt-3 p-md-5 mt-md-5">
              <div class="mb-3 row">
                <label for="imageCount" class="col-4 col-form-label text-nowrap fw-medium">Number of Images:</label>
                <div class="col-8">
                  <input type="text" class="form-control-plaintext fw-bold" id="imageCount" value="<?php echo $total_count; ?>" readonly>
                </div>
              </div>
              <div class="mb-3 row">
                <label for="tagCount" class="col-4 col-form-label text-nowrap fw-medium">Number of Tags:</label>
                <div class="col-8">
                  <input type="text" class="form-control-plaintext fw-bold" id="tagCount" value="<?php echo $tag_count; ?>" readonly>
                </div>
              </div>
              <div class="mb-3 row">
                <label for="musicCount" class="col-4 col-form-label text-nowrap fw-medium">Number of Songs:</label>
                <div class="col-8">
                  <input type="text" class="form-control-plaintext fw-bold" id="musicCount" value="<?php echo $count_music; ?>" readonly>
                </div>
              </div>
              <div class="mb-3 row">
                <label for="videoCount" class="col-4 col-form-label text-nowrap fw-medium">Number of Videos:</label>
                <div class="col-8">
                  <input type="text" class="form-control-plaintext fw-bold" id="videoCount" value="<?php echo $count_videos; ?>" readonly>
                </div>
              </div>
              <div class="mb-3 row">
                <label for="totalSize" class="col-4 col-form-label text-nowrap fw-medium">Total Size:</label>
                <div class="col-8">
                  <input type="text" class="form-control-plaintext fw-bold" id="totalSize" value="<?php echo $total_size_mb; ?> MB" readonly>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script>
      var ctx = document.getElementById('myChart').getContext('2d');
      ctx.canvas.classList.add('w-100', 'h-100');
      var myChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: [
            '<?php echo $total_count; ?> Images',
            '<?php echo $count_videos; ?> Videos',
            '<?php echo $count_music; ?> Music',
            '<?php echo $tag_count; ?> Tags',
            '<?php echo $total_size_mb; ?> Size (MB)'
          ],
          datasets: [{
            data: [
              <?php echo $total_count; ?>,
              <?php echo $count_videos; ?>,
              <?php echo $count_music; ?>,
              <?php echo $tag_count; ?>,
              <?php echo $total_size_mb; ?>
            ],
            backgroundColor: [
              'rgba(255, 99, 132, 0.5)',
              'rgba(54, 162, 235, 0.5)',
              'rgba(255, 206, 86, 0.5)',
              'rgba(75, 192, 192, 0.5)',
              'rgba(153, 102, 255, 0.5)'
            ],
            borderColor: [
              'rgba(255, 99, 132, 1)',
              'rgba(54, 162, 235, 1)',
              'rgba(255, 206, 86, 1)',
              'rgba(75, 192, 192, 1)',
              'rgba(153, 102, 255, 1)'
            ],
            borderWidth: 3
          }]
        },
        options: {
          responsive: false,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'bottom',
              labels: {
                font: {
                  size: 12
                }
              }
            }
          }
        }
      });
    </script>
    <?php include('end.php'); ?>