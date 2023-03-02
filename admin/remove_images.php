<?php
session_start();

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: ../admin/access.php');
    exit;
}

// Connect to SQLite database
$db = new PDO('sqlite:../database.sqlite');

// If the "Remove" button is clicked, delete the image from the database and the folder
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $stmt = $db->prepare("SELECT * FROM images WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $filename = $row['filename'];
        unlink('../images/' . $filename);
        unlink('../thumbnails/' . $filename);
        $stmt = $db->prepare("DELETE FROM images WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Images</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
</head>
<body>
   <ul class="me-2 ms-2 mt-2 nav nav-pills nav-fill justify-content-center">
      <li class="nav-item"><a class="nav-link" aria-current="page" href="../admin/edit_users.php"><i class="bi bi-person-fill-gear"></i></a></li>
      <li class="nav-item"><a class="nav-link active" href="../admin/remove_images.php"><i class="bi bi-images"></i></a></li> 
      <li class="nav-item"><a class="nav-link" href="../admin/remove_all.php"><i class="bi bi-person-fill-exclamation"></i></a></li>
    </ul>
    <div class="container">
        <h1 class="my-4">Images</h1>
        <div class="row">
            <?php
            // Query to get all images from the "images" table
            $stmt = $db->query('SELECT * FROM images');

            // Loop through the results and display images
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Display the image filename
                echo '<div class="col-md-4 my-3">
                          <div class="card">
                              <div class="card-body">
                                  <h5 class="card-title">' . $row['filename'] . '</h5>';

                                  // Display the image thumbnail
                                  echo '<img src="../thumbnails/' . $row['filename'] . '" class="rounded">';
                                  echo '<p class="card-text fw-bold mt-2">Username: ' . $row['username'] . '</p>';
                                  echo '<p class="card-text fw-bold mt-2">Tags: ' . $row['tags'] . '</p>';
            
                                  // Display a button to delete the image
                                  echo '<form method="post" action="" class="mt-3">
                                            <input type="hidden" name="id" value="' . $row['id'] . '">
                                            <button type="submit" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to delete this image?\')">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </form>
                              </div>
                          </div>
                      </div>';
                  }
                ?>
        </div>
    </div>
</body>
</html>
