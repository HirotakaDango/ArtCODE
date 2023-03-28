<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
  header('Location: session.php');
  exit();
}

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Get the logged in user's username
$username = $_SESSION['username'];

// Count the number of image filenames for the logged in user
$stmt = $db->prepare('SELECT COUNT(*) FROM images WHERE username = :username');
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$result = $stmt->execute();
$count = $result->fetchArray()[0];

// Count the number of unique tags for the logged in user
$stmt = $db->prepare('SELECT tags FROM images WHERE username = :username AND tags != "free image"');
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
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

// Loop through each image and get the size
$total_size = 0;
$stmt = $db->prepare('SELECT filename FROM images WHERE username = :username');
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$result = $stmt->execute();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  $filename = $row['filename'];
  $filepath = "images/$filename";
  $filesize = filesize($filepath);
  $total_size += $filesize;
}

// Get the total size in MB
$total_size_mb = round($total_size / (1024 * 1024), 2);

// Close the database connection
$db->close();
?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <?php include('setheader.php'); ?>
    <div class="mt-3 mb-3 rounded-3 border border-4">
      <h5 class="card-title text-center mt-3 text-secondary fw-bold">User's Analytical Data</h5>
      <div class="roow">
        <div class="cool-6">
          <div class="caard m-top1">
            <div class="d-flex justify-content-center">
              <canvas id="myChart" width="300" height="300"></canvas>
            </div>
          </div>
        </div>
        <div class="cool-6">
          <div class="caard container m-top2">
            <p class="text-secondary fw-bold">You have <?php echo $count; ?> images in your storage.</p>
            <p class="text-secondary fw-bold">You have <?php echo $tag_count; ?> different tags.</p>
            <p class="text-secondary fw-bold">Total images you uploaded <?php echo $total_size_mb; ?> MB.</p>
          </div> 
        </div>
      </div>
    </div>
    <style>
      .roow {
        display: flex;
        flex-wrap: wrap;
      }
      
      .caard {
        background-color: #fff;
        margin-bottom: 15px;
      } 
      
      @media (min-width: 768px) {
        .cool-6 {
          width: 50%;
          padding: 0 15px;
          box-sizing: border-box;
       }
       
       .m-top1 {
          margin-top: 18px;
       }

       .m-top2 {
          margin-top: 80px;
          text-align: left;
       }
      } 
      
      @media (max-width: 767px) {
        .cool-6 {
          width: 100%;
          padding: 0;
        }
        
       .m-top1 {
          margin-top: 18px;
       }
       
        .m-top2 {
          text-align: center;
        }
      } 
    </style>

    <script>
    var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: ['<?php echo $count; ?> Images', '<?php echo $tag_count; ?> Tags', '<?php echo $total_size_mb; ?> Size (MB)'],
        datasets: [{
          data: [<?php echo $count; ?>, <?php echo $tag_count; ?>, <?php echo $total_size_mb; ?>],
          backgroundColor: [
           'rgba(255, 99, 132, 0.5)',
           'rgba(54, 162, 235, 0.5)',
           'rgba(255, 206, 86, 0.5)'
          ],
          borderColor: [
           'rgba(255, 99, 132, 1)',
           'rgba(54, 162, 235, 1)',
           'rgba(255, 206, 86, 1)'
          ],
          borderWidth: 1
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
