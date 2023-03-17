<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
  header('Location: session.php');
  exit();
}

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Get the artist name from the database
$username = $_SESSION['username'];
$stmt = $db->prepare("SELECT id, artist FROM users WHERE username = :username");
$stmt->bindValue(':username', $username);
$result = $stmt->execute();
$row = $result->fetchArray();
$user_id = $row['id'];
$artist = $row['artist'];
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $artist; ?>'s Analytical Data</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
  </head>
  <body>
    <?php include('setheader.php'); ?>
    <div class="container-fluid my-3">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title text-center text-secondary fw-bold">User's Analytical Data</h5>
              <div class="d-flex justify-content-center">
                <canvas id="myChart" width="400" height="400"></canvas>
              </div>
              <?php

                // Connect to the database
                $db = new SQLite3('database.sqlite');

                // Get the logged in user's username
                $username = $_SESSION['username'];

                // Count the number of image filenames for the logged in user
                $stmt = $db->prepare('SELECT COUNT(*) FROM images WHERE username = :username');
                $stmt->bindValue(':username', $username, SQLITE3_TEXT);
                $result = $stmt->execute();
                $count = $result->fetchArray()[0];

                // Count the number of tags for the logged in user
                $stmt = $db->prepare('SELECT COUNT(DISTINCT tags) FROM images WHERE username = :username AND tags != "free image"');
                $stmt->bindValue(':username', $username, SQLITE3_TEXT);
                $result = $stmt->execute();
                $tag_count = $result->fetchArray()[0];

                // Close the database connection
                $db->close();

                echo "<p class='text-center text-secondary fw-bold'>You have $count images in your storage, tagged with $tag_count different tags.</p>";
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script>
      const ctx = document.getElementById('myChart').getContext('2d');
      const myChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Images', 'Tags'],
          datasets: [{
            label: '# of Files',
            data: [<?php echo $count; ?>, <?php echo $tag_count; ?>],
              backgroundColor: [
                'rgba(255, 99, 132, 0.6)',
                'rgba(54, 162, 235, 0.6)',
              ],
              borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
              ],
              borderWidth: 1
          }]
      },
      options: {
        plugins: {
          legend: {
              position: 'right'
            }
          }
        }
      });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>
