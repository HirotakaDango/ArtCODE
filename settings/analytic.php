<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
  header('Location: ../session.php');
  exit();
}

// Connect to the SQLite database
$db = new SQLite3('../database.sqlite');

// Get the logged in user's email
$email = $_SESSION['email'];

// Count the number of image filenames for the logged in user from the 'images' table
$stmt = $db->prepare('SELECT COUNT(*) FROM images WHERE email = :email');
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();
$count_images = $result->fetchArray()[0];

// Count the number of image filenames for the logged in user from the 'image_child' table
$stmt = $db->prepare('SELECT COUNT(*) FROM image_child WHERE email = :email');
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();
$count_image_child = $result->fetchArray()[0];

// Total count of images from both tables
$total_count = $count_images + $count_image_child;

// Count the number of unique tags for the logged in user from the 'images' table
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
  $filesize = filesize($filepath);
  $total_size_images += $filesize;
}

// Loop through each image from the 'image_child' table and get the size
$total_size_image_child = 0;
$stmt = $db->prepare('SELECT filename FROM image_child WHERE email = :email');
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  $filename = $row['filename'];
  $filepath = "../images/$filename";
  $filesize = filesize($filepath);
  $total_size_image_child += $filesize;
}

// Total size of images from both tables in MB
$total_size_mb = round(($total_size_images + $total_size_image_child) / (1024 * 1024), 2);

// Close the database connection
$db->close();
?>

    <?php include('setheader.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <div class="mt-3 mb-3 rounded-4 border border-4">
      <h5 class="card-title text-center mt-3 text-dark fw-bold">User's Analytical Data</h5>
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
            <p class="text-dark fw-bold">You have <?php echo $total_count; ?> images in your storage.</p>
            <p class="text-dark fw-bold">You have <?php echo $tag_count; ?> different tags.</p>
            <p class="text-dark fw-bold">Total images you uploaded <?php echo $total_size_mb; ?> MB.</p>
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
          labels: ['<?php echo $total_count; ?> Images', '<?php echo $tag_count; ?> Tags', '<?php echo $total_size_mb; ?> Size (MB)'],
          datasets: [{
            data: [<?php echo $total_count; ?>, <?php echo $tag_count; ?>, <?php echo $total_size_mb; ?>],
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
