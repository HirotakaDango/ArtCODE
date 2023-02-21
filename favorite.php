<?php
  session_start();
  if (!isset($_SESSION['username'])) {
    header("Location: session.php");
    exit;
  }

  // Connect to the SQLite database
  $db = new SQLite3('database.sqlite');

  // Get all of the favorite images for the current user
  $username = $_SESSION['username'];
  $result = $db->query("SELECT images.* FROM images INNER JOIN favorites ON images.id = favorites.image_id WHERE favorites.username = '$username' ORDER BY favorites.id DESC");

  // Process any favorite/unfavorite requests
  if (isset($_POST['favorite'])) {
    $image_id = $_POST['image_id'];
    
    // Check if the image has already been favorited by the current user
    $existing_fav = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE username = '$username' AND image_id = $image_id");
    
    if ($existing_fav == 0) {
      $db->exec("INSERT INTO favorites (username, image_id) VALUES ('$username', $image_id)");
    }
    
    // Redirect to the same page to prevent duplicate form submissions
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
    
  } elseif (isset($_POST['unfavorite'])) {
    $image_id = $_POST['image_id'];
    $db->exec("DELETE FROM favorites WHERE username = '$username' AND image_id = $image_id");
    
    // Redirect to the same page to prevent duplicate form submissions
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
  }
?>

<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  </head>
  <body>
    <center style="margin-bottom: 60px; font-weight: 800; color: gray;">
      <nav class="navbar fixed-top bg-light shadow" style="padding-bottom: 7px; padding-top: 7px;">
        <div class="bb1 container">
          <a class="nav-link" href="https://discord.gg/KhgFJV2N6J"><i class="bi bi-discord"></i></a>
          <a class="nav-link px-2 text-secondary" href="imgupload.php"><i class="bi bi-cloud-arrow-up-fill"></i></a>
          <h1 style="color: gray; margin-top: 7px;" class="nav-link px-2 text-secondary"><a class="nav-link border-bottom" href="index.php">ArtCODE LITE</a></h1>
          <a class="nav-link px-2 text-secondary" href="favorite.php"><i class="bi bi-heart-fill"></i></a>
          <div class="dropdown">
            <a class="nav-link px-2 text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle" style="font-size: 15.5px;"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-start">
              <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a></li>
              <li><a class="dropdown-item" href="logout.php"><i class="bi bi-door-open-fill"></i> Logout</a></li>
            </ul>
          </div>
        </div>
      </nav>
    </center>
   <style>
      .card-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        grid-gap: 20px;
      }

      .card {
        border: 1px solid #ccc;
        border-radius: 5px;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
        padding: 10px;
        width: 100%;
        height: auto;
      }

      .card a {
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
      }
      
      .card img {
        width: 100%;
        height: 350px;
        object-fit: cover;
        border-radius: 4px;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
      }
    </style>
    <div class="container-fluid">
      <h1 class="text-center mt-3 text-secondary fw-bold">MY FAVORITES</h1>
      <div class="card-container">
        <?php while ($image = $result->fetchArray()): ?>
            <div class="card">
              <a href="images/<?php echo $image['filename']; ?>"><img class="card-img-top" src="thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['filename']; ?>"></a>
              <div class="card-body">
                <form method="POST">
                  <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                  <button type="submit" class="form-control bg-danger fw-bold text-white" name="unfavorite"><i class="bi bi-heart-fill"></i> Unfavorite</button>
                </form>
              </div>
            </div>
        <?php endwhile; ?> 
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>
