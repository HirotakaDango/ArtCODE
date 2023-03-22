<?php
require_once('prompt.php'); 
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Database Counts</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>
  <body>
    <?php include('admin_header.php'); ?> 
    <div class="container mt-5">
      <div class="row">
        <div class="col-md-6 mb-2">
          <div class="card">
            <div class="card-header bg-primary text-white fw-bold">
              <i class="bi bi-people-fill"></i>
              Users
            </div>
            <div class="card-body">
              <canvas id="user-chart"></canvas>
                <?php
                  // Open the database file
                  $db = new SQLite3('../database.sqlite');

                  // Count the number of users in the "users" table
                  $user_count = $db->querySingle('SELECT COUNT(*) FROM users');

                  // Close the database file
                  $db->close();

                  // Set up the data for the chart
                  $data = array(
                    'labels' => array("($user_count users)"),
                    'datasets' => array(
                      array(
                        'data' => array($user_count, 0),
                        'backgroundColor' => array('rgba(255, 99, 132, 0.2)',),
                        'borderColor' => array('rgba(255, 99, 132, 1)',), 
                        'borderWidth' => 3
                      )
                    )
                  );

                // Output the JavaScript code for the chart
                echo "<script>";
                echo "var ctx = document.getElementById('user-chart').getContext('2d');";
                echo "var myChart = new Chart(ctx, {type: 'pie', data: " . json_encode($data) . "});";
                echo "</script>";
                echo "<p class='fw-bold text-secondary mt-3'>Total Users: " . $user_count . "</p>";
                echo "<a class='btn btn-sm btn-primary fw-bold me-1' href='../admin/edit_users.php'>manage user</a>";
                echo "<a class='btn btn-sm btn-danger fw-bold ms-1' href='../admin/remove_all.php'>danger zone</a>";
              ?>
            </div>
          </div>
        </div>
        <div class="col-md-6 mb-2">
          <div class="card">
            <div class="card-header bg-primary text-white fw-bold">
              <i class="bi bi-images"></i>
              Images with Tags
            </div>
            <div class="card-body">
              <?php
                // Open the database file
                $db = new SQLite3('../database.sqlite');

                // Count the number of images in the "images" table
                $image_count = $db->querySingle('SELECT COUNT(*) FROM images');
                    
                // Count the total number of tags in the "tags" column of the "images" table
                $tag_count = $db->querySingle('SELECT COUNT(DISTINCT trim(tags)) FROM images WHERE tags IS NOT NULL');

                // Close the database file
                $db->close();

                // Calculate the total size of all images
                $dir = "../images"; // set your directory path here
                $size = 0; // initialize the size variable to 0
                $image_count_data = 0; // initialize the image count variable to 0

                if ($handle = opendir($dir)) {
                  while (($file = readdir($handle)) !== false) {
                    if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), array('jpg', 'jpeg', 'png', 'gif'))) {
                      $image_count_data++; // increment the image count
                      $size += filesize("$dir/$file"); // add the size of the image to the total size
                    }
                  }
                  closedir($handle);
                }

                // Convert the total size to MB and store the values in a variable
                function convert_bytes_to_mb($bytes) {
                  return number_format($bytes / 1048576, 2);
                }
                $total_size_data = convert_bytes_to_mb($size);

                // Display the image count, tag count, and total size of images as a line chart
                echo '<canvas id="myChart"></canvas>';
                echo "<p class='fw-bold text-secondary mt-3'>Total Images: " . $image_count_data . "</p>";
                echo "<p class='fw-bold text-secondary'>Total Tags: " . $tag_count . "</p>";
                echo "<p class='fw-bold text-secondary'>Total Size: " . $total_size_data . " MB</p>";
                echo "<a class='btn btn-sm btn-primary fw-bold me-1' href='../admin/remove_images.php'>manage images</a>";
                echo "<a class='btn btn-sm btn-primary fw-bold ms-1' href='../admin/management.php'>manage all files</a>";
              ?>
              <script>
                var ctx = document.getElementById('myChart').getContext('2d');
                var myChart = new Chart(ctx, {
                  type: 'pie',
                  data: {
                    labels: ['(' + <?php echo $image_count_data; ?> + ' images)', '(' + <?php echo $tag_count; ?> + ' tags)', '(' + <?php echo $total_size_data; ?> + ' MB)'],
                    datasets: [{
                      label: 'Image Data',
                      data: [<?php echo $image_count_data; ?>, <?php echo $tag_count; ?>, <?php echo $total_size_data; ?>],
                      backgroundColor: 'rgba(255, 99, 132, 0.2)',
                      borderColor: 'rgba(255, 99, 132, 1)',
                      borderWidth: 3
                    }]
                  },
                  options: {
                    scales: {
                      yAxes: [{
                            ticks: {
                          beginAtZero: true
                        }
                      }]
                    }
                  }
                });
              </script> 
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
