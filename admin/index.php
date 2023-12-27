<?php
require_once('prompt.php'); 
?>

    <?php include('admin_header.php'); ?> 
    <div class="container mt-5">
      <div class="row">
        <div class="col-md-6 mb-2">
          <div class="card border-0">
            <div class="card-body">
              <canvas id="userChart"></canvas>
              <?php
              // Open the database file
              $db = new SQLite3('../database.sqlite');

              // Count the number of users in the "users" table
              $user_count = $db->querySingle('SELECT COUNT(*) FROM users');

              // Count the total visit count from all rows in the "visit" table
              $visit_count_total = $db->querySingle('SELECT SUM(visit_count) FROM visit');

              // Close the database file
              $db->close();
              ?>
              <script>
                var ctx = document.getElementById('userChart').getContext('2d');
                var userChart = new Chart(ctx, {
                  type: 'doughnut',
                  data: {
                    labels: [
                      '(' + <?php echo $user_count; ?> + ' Users)',
                      '(' + <?php echo $visit_count_total; ?> + ' Visits)'
                    ],
                    datasets: [{
                      label: 'Data',
                      data: [
                        <?php echo $user_count; ?>,
                        <?php echo $visit_count_total; ?>
                      ],
                      backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)'
                      ],
                      borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)'
                      ],
                      borderWidth: 3
                    }]
                  },
                  options: {
                    plugins: {
                      legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                          font: {
                            size: 12
                          }
                        }
                      },
                      tooltips: {
                        position: 'top' // Set the position of tooltips to bottom
                      }
                    }
                  }
                });
              </script>
              <div class="my-3 row">
                <label for="totalUsers" class="col-4 col-form-label text-nowrap fw-medium">Total Users</label>
                <div class="col-8">
                  <input type="text" class="form-control-plaintext fw-bold" id="totalUsers" value=": <?= $user_count ?>" readonly>
                </div>
              </div>
              <div class="mb-3 row">
                <label for="totalVisit" class="col-4 col-form-label text-nowrap fw-medium">Total Visit</label>
                <div class="col-8">
                  <input type="text" class="form-control-plaintext fw-bold" id="totalVisit" value=": <?= $visit_count_total ?>" readonly>
                </div>
              </div>
              <div class="mt-3">
                <a class='btn btn-sm btn-primary fw-bold me-1' href='../admin/edit_users.php'>manage users</a>
                <a class='btn btn-sm btn-danger fw-bold ms-1' href='../admin/remove_all.php'>danger zone</a>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6 mb-2">
          <div class="card border-0">
            <div class="card-body">
              <?php
              // Open the database file
              $db = new SQLite3('../database.sqlite');

              // Count the number of images in the "images" table
              $image_count = $db->querySingle('SELECT COUNT(*) FROM images');
              $image_child_count = $db->querySingle('SELECT COUNT(*) FROM image_child');
              $videos_count = $db->querySingle('SELECT COUNT(*) FROM videos');
              $music_count = $db->querySingle('SELECT COUNT(*) FROM music');

              // Count the total number of tags in the "tags" column of the "images" table
              $tag_count = $db->querySingle('SELECT COUNT(DISTINCT trim(tags)) FROM images WHERE tags IS NOT NULL');

              // Close the database file
              $db->close();

              // Calculate the total size of all images
              $image_dir = "../images"; // image path
              $music_dir = "../feeds/music/uploads/"; // music path
              $videos_dir = "../feeds/minutes/videos/"; // videos path
              $size = 0; // initialize the size variable to 0
              $image_count_data = 0; // initialize the image count variable to 0

              // Function to calculate the size of a directory
              function calculateDirectorySize($directory) {
                $size = 0;
                foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
                  $size += $file->getSize();
                }
                return $size;
              }

              $image_count_data = $image_count + $image_child_count;
              $size += calculateDirectorySize($image_dir);
              $size += calculateDirectorySize($music_dir);
              $size += calculateDirectorySize($videos_dir);

              // Convert the total size to MB and store the values in a variable
              function convert_bytes_to_mb($bytes) {
                return number_format($bytes / 1048576, 2);
              }
              $total_size_data = convert_bytes_to_mb($size);
              ?>
              <canvas id="myChart"></canvas>
              <div class="my-3 row">
                <label for="totalImages" class="col-4 col-form-label text-nowrap fw-medium">Total Images</label>
                <div class="col-8">
                  <input type="text" class="form-control-plaintext fw-bold" id="totalImages" value=": <?= $image_count_data ?>" readonly>
                </div>
              </div>
              <div class="mb-3 row">
                <label for="totalVideos" class="col-4 col-form-label text-nowrap fw-medium">Total Videos</label>
                <div class="col-8">
                  <input type="text" class="form-control-plaintext fw-bold" id="totalVideos" value=": <?= $videos_count ?>" readonly>
                </div>
              </div>
              <div class="mb-3 row">
                <label for="totalMusic" class="col-4 col-form-label text-nowrap fw-medium">Total Music</label>
                <div class="col-8">
                  <input type="text" class="form-control-plaintext fw-bold" id="totalMusic" value=": <?= $music_count ?>" readonly>
                </div>
              </div>
              <div class="mb-3 row">
                <label for="totalTags" class="col-4 col-form-label text-nowrap fw-medium">Total Tags</label>
                <div class="col-8">
                  <input type="text" class="form-control-plaintext fw-bold" id="totalTags" value=": <?= $tag_count ?>" readonly>
                </div>
              </div>
              <div class="mb-3 row">
                <label for="totalSize" class="col-4 col-form-label text-nowrap fw-medium">Total Size</label>
                <div class="col-8">
                  <input type="text" class="form-control-plaintext fw-bold" id="totalSize" value=": <?= $total_size_data ?> MB" readonly>
                </div>
              </div>
              <a class='btn btn-sm btn-primary fw-bold me-1' href='../admin/remove_images.php'>manage images</a>
              <a class='btn btn-sm btn-primary fw-bold ms-1' href='../admin/management.php'>manage all files</a>
              <a class='btn btn-sm btn-primary fw-bold ms-1' href='../phpliteadmin.php'>manage database</a>
              <script>
                var ctx = document.getElementById('myChart').getContext('2d');
                var myChart = new Chart(ctx, {
                  type: 'doughnut',
                  data: {
                    labels: [
                      '(' + <?php echo $image_count_data; ?> + ' images)',
                      '(' + <?php echo $videos_count; ?> + ' videos)',
                      '(' + <?php echo $music_count; ?> + ' music)',
                      '(' + <?php echo $tag_count; ?> + ' tags)',
                      '(' + <?php echo $total_size_data; ?> + ' MB)'
                    ],
                    datasets: [{
                      label: 'Data',
                      data: [
                        <?php echo $image_count_data; ?>,
                        <?php echo $videos_count; ?>,
                        <?php echo $music_count; ?>,
                        <?php echo $tag_count; ?>,
                        <?php echo $total_size_data; ?>
                      ],
                      backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)'
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
                    plugins: {
                      legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            font: {
                              size: 12
                          }
                        }
                      },
                      tooltip: {
                        position: 'average'
                      }
                    }
                  }
                });
              </script>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="mt-5"></div>
    <?php include('end.php'); ?>