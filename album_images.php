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
  $album_id = $_GET['album'];
  $email = $_SESSION['email'];
  
  // Get the images for the specified album
  $stmt = $db->prepare('SELECT images.id, images.filename, images.title, images.imgdesc, images.link, album.album_name, image_album.id AS image_album_id FROM image_album INNER JOIN images ON image_album.image_id = images.id INNER JOIN album ON image_album.album_id = album.id WHERE image_album.album_id = :album_id AND image_album.email = :email ORDER BY images.id ASC');
  $stmt->bindValue(':album_id', $album_id, SQLITE3_INTEGER);
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $results = $stmt->execute();

  // Store images in an array for later use
  $images = [];
  while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $images[] = $row;
    
    $album_name = $row['album_name'];
  }
  
} else {
  // If no album is specified, redirect to album.php
  header('Location: album.php');
  exit();
}

// Handle deleting an image from the album if the delete button was clicked
if (isset($_POST['delete_image'])) {
  $image_album_id = $_POST['image_album_id'];
  $stmt = $db->prepare('DELETE FROM image_album WHERE id = :id AND email = :email');
  $stmt->bindValue(':id', $image_album_id, SQLITE3_INTEGER);
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->execute();
  
  // Redirect back to the same page to update the displayed images
  header("Location: album_images.php?album=$album_id");
  exit();
}

// Close the database connection 
$db->close();
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Album Images - <?php echo htmlspecialchars($album_name); ?></title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include 'header.php'; ?>
    <?php if (empty($images)): ?>
      <h5 class="text-secondary fw-bold text-center mt-2">This album is empty</h5>
    <?php else: ?>
      <h5 class="text-secondary fw-bold text-center mt-2">My Album <?php echo htmlspecialchars($album_name); ?></h5>
    <?php endif; ?>
    <div class="images">
      <?php foreach ($images as $image): ?>
        <div class="image-container">
          <a class="shadow" href="image.php?artworkid=<?php echo htmlspecialchars($image['id']); ?>">
            <img src="thumbnails/<?php echo htmlspecialchars($image['filename']); ?>">
          </a>
          <div>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this image from the album?');">
              <input type="hidden" name="image_album_id" value="<?php echo htmlspecialchars($image['image_album_id']); ?>">
              <button type="submit" name="delete_image" class="btn p-b3 btn-sm btn-dark opacity-50"><i class="bi bi-trash-fill"></i></button>
            </form> 
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <style>
      .images {
        display: grid;
        grid-template-columns: repeat(2, 1fr); /* Two columns in mobile view */
        grid-gap: 3px;
        justify-content: center;
        margin-right: 3px;
        margin-left: 3px;
      }

      @media (min-width: 768px) {
        /* For desktop view, change the grid layout */
        .images {
          grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
      }

      .images a {
        display: block;
        border-radius: 4px;
        overflow: hidden;
      }

      .images img {
        width: 100%;
        height: auto;
        object-fit: cover;
        height: 200px;
        transition: transform 0.5s ease-in-out;
      }
      
      @media (min-width: 768px) {
        .p-b3 {
          margin-left: 6px;
          border-radius: 4px;
          margin-top: -71px;
        } 
      }
      
      @media (max-width: 767px) {
        .p-b3 {
          margin-left: 5px;
          border-radius: 4px;
          margin-top: -71px;
        }
      } 

      @media (max-width: 450px) {
        .p-b3 {
          margin-left: 6px;
          border-radius: 4px;
          margin-top: -70px;
        } 
      }

      @media (max-width: 415px) {
        .p-b3 {
          margin-left: 6px;
          border-radius: 4px;
          margin-top: -70px;
        } 
      }

      @media (max-width: 380px) {
        .p-b3 {
          margin-left: 6px;
          border-radius: 4px;
          margin-top: -70px;
        } 
      }
      
      .image-container {
        margin-bottom: -24px;  
      }
    </style> 
    <?php include('bootstrapjs.php'); ?>
  </body>
</html> 