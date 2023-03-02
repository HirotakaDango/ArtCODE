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

                // Output the image count and tag count in the card body
                echo "<h1 class='fw-bold text-secondary'>" . $image_count . "</h1>";
                echo "<p class='fw-bold text-secondary'>Total Tags: " . $tag_count . "</p>";

                // Close the database file
                $db->close();
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
