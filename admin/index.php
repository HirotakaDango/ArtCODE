<?php
session_start();

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: ../admin/access.php');
    exit;
}
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
   <ul class="me-2 ms-2 mt-2 nav nav-pills nav-fill justify-content-center">
      <li class="nav-item"><a class="nav-link active" href="../admin/index.php"><i class="bi bi-house-fill"></i></a></li>
      <li class="nav-item"><a class="nav-link" aria-current="page" href="../admin/edit_users.php"><i class="bi bi-person-fill-gear"></i></a></li>
      <li class="nav-item"><a class="nav-link" href="../admin/remove_images.php"><i class="bi bi-images"></i></a></li> 
      <li class="nav-item"><a class="nav-link" href="../admin/remove_all.php"><i class="bi bi-person-fill-exclamation"></i></a></li>
    </ul>
    <div class="container mt-5">
      <div class="row">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header bg-primary text-white fw-bold">
              <i class="bi bi-people-fill"></i>
              Users
            </div>
            <div class="card-body">
              <?php
                // Open the database file
                $db = new SQLite3('../database.sqlite');

                // Count the number of users in the "users" table
                $user_count = $db->querySingle('SELECT COUNT(*) FROM users');

                // Output the user count in the card body
                echo "<h1 class='fw-bold text-secondary'>" . $user_count . "</h1>";

                // Close the database file
                $db->close();
              ?>
            </div>
          </div>
        </div>
        <div class="col-md-6 mt-2">
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
                    $tag_count = $db->querySingle('SELECT SUM(length(tags) - length(replace(tags, ",", "")) + 1) AS tag_count FROM images');

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
                    ?>
                    <script>
                        var ctx = document.getElementById('myChart').getContext('2d');
                        var myChart = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: ['(' + <?php echo $image_count_data; ?> + ' images)', '(' + <?php echo $tag_count; ?> + ' tags)', '(' + <?php echo $total_size_data; ?> + ' MB)'],
                                datasets: [{
                                    label: 'Image Data',
                                    data: [<?php echo $image_count_data; ?>, <?php echo $tag_count; ?>, <?php echo $total_size_data; ?>],
                                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                    borderColor: 'rgba(255, 99, 132, 1)',
                                    borderWidth: 1
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
