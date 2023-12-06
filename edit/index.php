<?php
require_once('../auth.php');

// Connect to SQLite database
$db = new SQLite3('../database.sqlite');

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  // Redirect to index.php if not logged in
  header("Location: ../index.php");
  exit;
}

// Retrieve image details
if (isset($_GET['id'])) {
  $id = $_GET['id'];
  
  // Retrieve the email of the logged-in user
  $email = $_SESSION['email'];
  
  // Select the image details using the image ID and the email of the logged-in user
  $stmt = $db->prepare('SELECT * FROM images WHERE id = :id AND email = :email');
  $stmt->bindParam(':id', $id);
  $stmt->bindParam(':email', $email);
  $result = $stmt->execute();
  $image = $result->fetchArray(SQLITE3_ASSOC); // Retrieve result as an associative array
  
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
  header('Location: ?id=' . $id);
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
  header('Location: ?id=' . $id);
  exit();
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Image</title>
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('backheader.php'); ?>
    <div class="container-fluid mt-2">
      <h3 class="text-dark fw-bold mt-2 ms-2 text-center"><i class="bi bi-image"></i> Edit Image</h3>
      <div class="mt-3">
        <div class="roow">
          <div class="cool-6">
            <div class="caard">
              <img src="../thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>" class="h-100 w-100 rounded shadow">
              <div class="text-c">
                <div class="border border-4 bg-light text-dark fw-bold rounded-3 mt-2">
                  <a class="btn fw-bold text-dark w-100 border-0" data-bs-toggle="collapse" href="#collapseExpand" role="button" aria-expanded="false" aria-controls="collapseExample">
                    more information
                  </a>
                  <div class="collapse container" id="collapseExpand">
                    <?php
                      // Get image size in megabytes
                      $image_size = round(filesize('../images/' . $image['filename']) / (1024 * 1024), 2);

                      // Get image dimensions
                      list($width, $height) = getimagesize('../images/' . $image['filename']);

                      // Display image information
                      echo "<p class='mt-3 text-start ms-1'>Image Filename: <a href='../images/". $image['filename'] ."'>" . $image['filename'] . "</a></p>";
                      echo "<p class='text-start ms-1'>Image data size: " . $image_size . " MB</p>";
                      echo "<p class='text-start ms-1'>Image dimensions: " . $width . "x" . $height . "</p>";
                      echo "<p class='mt-3 text-start ms-1'><a href='../images/". $image['filename'] ."' download>Download Image</a></p>";

                      // Retrieve child images' information
                      $stmt = $db->prepare('SELECT * FROM image_child WHERE image_id = :image_id');
                      $stmt->bindParam(':image_id', $id);
                      $result = $stmt->execute();

                      // Loop through and display child images' information
                      while ($childImage = $result->fetchArray(SQLITE3_ASSOC)) {
                        // Get child image size in megabytes
                        $child_image_size = round(filesize('../images/' . $childImage['filename']) / (1024 * 1024), 2);

                        // Get child image dimensions
                        list($child_width, $child_height) = getimagesize('../images/' . $childImage['filename']);

                        // Display child image information
                        echo "<hr class='border-dark border-5 rounded-pill'></hr>";
                        echo "<p class='mt-3 text-start ms-1'>Image Filename: <a href='../images/". $childImage['filename'] ."'>" . $childImage['filename'] . "</a></p>";
                        echo "<p class='text-start ms-1'>Child Image Data Size: " . $child_image_size . " MB</p>";
                        echo "<p class='text-start ms-1'>Child Image Dimensions: " . $child_width . "x" . $child_height . "</p>";
                        echo "<p class='mt-3 text-start ms-1'><a href='../images/". $childImage['filename'] ."' download>Download Image</a></p>";
                      }
                    ?> 
                  </div>
                </div>
              </div>
            </center>
          </div>
        </div>
        <div class="cool-6">
          <div class="caard">
            <form method="POST">
              <div class="form-floating mb-2">
                <input class="form-control border rounded-3 text-dark fw-bold border-4" type="text" value="<?php echo htmlspecialchars($image['title']); ?>" name="title" placeholder="Image title" maxlength="250" required>  
                <label for="floatingInput" class="text-dark fw-bold">Enter title for your image</label>
              </div>
              <div class="form-floating mb-2">
                <textarea class="form-control border rounded-3 text-dark fw-bold border-4" oninput="stripHtmlTags(this)" type="text" value="<?php echo htmlspecialchars($image['imgdesc']); ?>" name="imgdesc" placeholder="Image description" maxlength="1400" style="height: 200px;" required><?php echo strip_tags($image['imgdesc']); ?></textarea>
                <label for="floatingInput" class="text-dark fw-bold">Enter description for your image</label>
              </div>
              <div class="form-floating mb-2">
                <input class="form-control border rounded-3 text-dark fw-bold border-4" type="text" value="<?php echo htmlspecialchars($image['tags']); ?>" name="tags" placeholder="Image tag" maxlength="800" required>
                <label for="floatingInput" class="text-dark fw-bold">Enter tag for your image</label>
              </div>
              <div class="form-floating mb-2">
                <input class="form-control border rounded-3 text-dark fw-bold border-4" type="text" value="<?php echo htmlspecialchars($image['link']); ?>" name="link" placeholder="Image link" maxlength="140"> 
                <label for="floatingInput" class="text-dark fw-bold">Enter link for your image</label>
              </div>
              <select class="form-select rounded-3 text-dark fw-bold mb-2 border-4" name="type" aria-label="Large select example" required>
                <option value="safe" <?php echo ($image['type'] === 'safe') ? 'selected' : ''; ?>>Safe For Works</option>
                <option value="nsfw" <?php echo ($image['type'] === 'nsfw') ? 'selected' : ''; ?>>NSFW/R-18</option>
              </select>
              <div class="btn-group gap-2 w-100">
                <button type="button" class="btn btn-dark fw-bold w-100 mb-2 rounded" data-bs-toggle="modal" data-bs-target="#deleteImage">
                  <i class="bi bi-trash-fill"></i> delete this image
                </button>
                <button type="submit" class="btn btn-dark fw-bold w-100 mb-2 rounded">
                  <i class="bi bi-floppy-fill"></i> save this image
                </button>
              </div>
              <div class="btn-group gap-2 w-100">
                <a class="btn btn-dark fw-bold w-100 mb-2 rounded" href="upload.php?id=<?php echo $image['id']; ?>">
                  <i class="bi bi-cloud-arrow-up-fill"></i> upload new images child
                </a>
                <a class="btn btn-dark fw-bold w-100 mb-2 rounded" href="all.php?id=<?php echo $image['id']; ?>">
                  <i class="bi bi-images"></i> all images child
                </a>
              </div>
              <div class="btn-group w-100">
                <a class="btn btn-dark fw-bold" href="../profile.php">Back to Profile</a>
                <a class="btn btn-dark fw-bold" href="../image.php?artworkid=<?php echo $image['id']; ?>">Back to Image</a>
              </div>
              <div class="mt-5"></div>
            </form> 
          </div> 
        </div>
      </div>
    </div>
    <div class="mt-5"></div>
    <div class="modal fade" id="deleteImage" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-3 shadow">
          <div class="modal-body p-4 text-center">
            <h5 class="mb-0">Delete this image?</h5>
            <p class="mb-0">This action can't be undone</p>
          </div>
          <form method="POST" action="delete.php">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="modal-footer flex-nowrap p-0">
              <button type="submit" class="btn btn-lg btn-link fs-6 text-danger text-decoration-none col-6 py-3 m-0 rounded-0 border-end"><strong>Yes, delete</strong></button>
              <button type="button" class="btn btn-lg btn-link fs-6 text-decoration-none col-6 py-3 m-0 rounded-0" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <style>
      .roow {
        display: flex;
        flex-wrap: wrap;
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
        window.location.href = "../profile.php";
      }
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>