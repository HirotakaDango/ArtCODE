<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}

// Connect to SQLite database
$db = new PDO('sqlite:database.sqlite');

// Retrieve image details
if (isset($_GET['id'])) {
  $id = $_GET['id'];
  
  // Retrieve the email of the logged-in user
  $email = $_SESSION['email'];
  
  // Select the image details using the image ID and the email of the logged-in user
  $stmt = $db->prepare('SELECT * FROM images WHERE id = :id AND email = :email');
  $stmt->bindParam(':id', $id);
  $stmt->bindParam(':email', $email);
  $stmt->execute();
  $image = $stmt->fetch(PDO::FETCH_ASSOC);

  // Check if the image exists and belongs to the logged-in user
  if (!$image) {
    echo '<meta charset="UTF-8"> 
          <meta name="viewport" content="width=device-width, initial-scale=1">
          <img src="icon/403-Error-Forbidden.svg" style="height: 100%; width: 100%;">
         ';
    exit();
  }

} else {
  // Redirect to error page if image ID is not specified
  header('Location: edit_image.php?id=' . $id);
  exit();
}

// Update image details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $imgdesc = nl2br(filter_var($_POST['imgdesc'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));
  $link = filter_var($_POST['link'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $tags = filter_var($_POST['tags'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $type = filter_var($_POST['type'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW); // Sanitize the type input

  $stmt = $db->prepare('UPDATE images SET title = :title, imgdesc = :imgdesc, link = :link, tags = :tags, type = :type WHERE id = :id');
  $stmt->bindParam(':title', $title);
  $stmt->bindParam(':imgdesc', $imgdesc);
  $stmt->bindParam(':link', $link);
  $stmt->bindParam(':tags', $tags);
  $stmt->bindParam(':type', $type); // Bind the type parameter
  $stmt->bindParam(':id', $id);
  $stmt->execute();
  
  // Redirect to image details page after update
  header('Location: edit_image.php?id=' . $id);
  exit();
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Image</title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('backheader.php'); ?>
    <h3 class="text-secondary fw-bold mt-2 ms-2 text-center"><i class="bi bi-image"></i> Edit Image</h3>
    <div class="mt-3">
      <div class="roow">
        <div class="cool-6">
          <div class="caard">
            <div class="d-flex justify-content-center">
              <img src="thumbnails/<?php echo htmlspecialchars($image['filename']); ?>" alt="<?php echo htmlspecialchars($image['title']); ?>" class="art">
            </div>
            <center>
              <div class="text-c">
                <div class="border border-4 bg-light text-secondary fw-bold rounded-3 container mt-2">
                  <?php
                    // Get image size in megabytes
                    $image_size = round(filesize('images/' . $image['filename']) / (1024 * 1024), 2);
              
                    // Get image dimensions
                    list($width, $height) = getimagesize('images/' . $image['filename']);

                    // Display image information
                    echo "<p class='mb-3'></p>";
                    echo "<p class='me-1 text-left ms-1'>Image data size: " . $image_size . " MB</p>";
                    echo "<p class='me-1 text-left ms-1'>Image dimensions: " . $width . "x" . $height . "</p>";
                  ?> 
                </div>
              </div>
            </center>
          </div>
        </div>
        <div class="cool-6">
          <div class="caard container">
            <form method="POST">
              <div class="form-floating mb-2">
                <input class="form-control border rounded-3 text-secondary fw-bold border-4" type="text" value="<?php echo htmlspecialchars($image['title']); ?>" name="title" placeholder="Image title" maxlength="50" required>  
                <label for="floatingInput" class="text-secondary fw-bold">Enter title for your image</label>
              </div>
              <div class="form-floating mb-2">
                <textarea class="form-control border rounded-3 text-secondary fw-bold border-4" oninput="stripHtmlTags(this)" type="text" value="<?php echo htmlspecialchars($image['imgdesc']); ?>" name="imgdesc" placeholder="Image description" maxlength="400" style="height: 200px;" required><?php echo strip_tags($image['imgdesc']); ?></textarea>
                <label for="floatingInput" class="text-secondary fw-bold">Enter description for your image</label>
              </div>
              <div class="form-floating mb-2">
                <input class="form-control border rounded-3 text-secondary fw-bold border-4" type="text" value="<?php echo htmlspecialchars($image['tags']); ?>" name="tags" placeholder="Image tag" maxlength="180" required>
                <label for="floatingInput" class="text-secondary fw-bold">Enter tag for your image</label>
              </div>
              <div class="form-floating mb-2">
                <input class="form-control border rounded-3 text-secondary fw-bold border-4" type="text" value="<?php echo htmlspecialchars($image['link']); ?>" name="link" placeholder="Image link" maxlength="140"> 
                <label for="floatingInput" class="text-secondary fw-bold">Enter link for your image</label>
              </div>
              <select class="form-select rounded-3 text-secondary fw-bold mb-2 border-4" name="type" aria-label="Large select example" required>
                <option value="safe" <?php echo ($image['type'] === 'safe') ? 'selected' : ''; ?>>Safe For Works</option>
                <option value="nsfw" <?php echo ($image['type'] === 'nsfw') ? 'selected' : ''; ?>>NSFW/R-18</option>
              </select>
              <div class="btn-group w-100">
                <button type="submit" class="btn bg-primary text-white fw-bold">Save</button>
                <a class="btn btn-danger fw-bold text-white" href="profile.php">Back</a>
              </div>
              <div class="mt-5"></div>
            </form> 
          </div> 
        </div>
      </div>
    </div>
    <style>
      .roow {
        display: flex;
        flex-wrap: wrap;
      }

      .art {
        border: 2px solid lightgray;
        border-radius: 5px;
        object-fit: cover;
        width: 98.7%;
        height: 428px;
      }
       
      .text-left {
        text-align: left;
      }
      
      .cool-6 {
        width: 50%;
        padding: 0 15px;
        box-sizing: border-box;
      }

      .caard {
        background-color: #fff;
        margin-bottom: 15px;
      }

      @media (max-width: 767px) {
        .cool-6 {
          width: 100%;
          padding: 0;
        }
        
        .text-c {
          width: 94%;
        }
      } 
    </style> 
    <script>
      function goBack() {
        window.location.href = "profile.php";
      }
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>