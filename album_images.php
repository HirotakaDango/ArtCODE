<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  header('Location: session.php');
  exit();
}

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Check if an album has been specified
if (isset($_GET['album'])) {
  $album_name = $_GET['album'];
  $email = $_SESSION['email'];
  
  // Get the images for the specified album
  $stmt = $db->prepare('SELECT images.filename, images.title, images.imgdesc, images.link FROM images JOIN album ON images.id = album.image_id WHERE album.email = :email AND album.album_name = :album_name');
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':album_name', $album_name, SQLITE3_TEXT);
  $results = $stmt->execute();

  // Store images in an array for later use
  $images = [];
  while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $images[] = $row;
  }
  
  // Close database connection
  $db->close();
} else {
  // If no album is specified, redirect to album.php
  header('Location: album.php');
  exit();
}
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Album Images - <?php echo htmlspecialchars($album_name); ?></title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  </head>
  <body>
    <?php include 'header.php'; ?>
    <h5 class="text-secondary fw-bold text-center mt-2">My Album <?php echo htmlspecialchars($album_name); ?></h5>
    <div class="images">
      <?php foreach ($images as $image): ?>
        <a href="image.php?filename=<?php echo htmlspecialchars($image['filename']); ?>">
          <div class="image">
            <img src="thumbnails/<?php echo htmlspecialchars($image['filename']); ?>">
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <style>
      .images {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        grid-gap: 2px;
        justify-content: center;
        margin-right: 3px;
        margin-left: 3px;
      }

      .images a {
        display: block;
        border-radius: 4px;
        overflow: hidden;
        border: 2px solid #ccc;
      }

      .images img {
        width: 100%;
        height: auto;
        object-fit: cover;
        height: 200px;
        transition: transform 0.5s ease-in-out;
      }
    
    </style> 
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html> 