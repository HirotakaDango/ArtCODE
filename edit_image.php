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
  $stmt = $db->prepare('SELECT * FROM images WHERE id = :id');
  $stmt->bindParam(':id', $id);
  $stmt->execute();
  $image = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
  // Redirect to error page if image ID is not specified
  header('Location: edit_image.php?id=' . $id);
  exit();
}

// Update image details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = htmlspecialchars($_POST['title']);
  $imgdesc = htmlspecialchars($_POST['imgdesc']);
  $link = htmlspecialchars($_POST['link']);
  $tags = htmlspecialchars($_POST['tags']);
  
  $stmt = $db->prepare('UPDATE images SET title = :title, imgdesc = :imgdesc, link = :link, tags = :tags WHERE id = :id');
  $stmt->bindParam(':title', $title);
  $stmt->bindParam(':imgdesc', $imgdesc);
  $stmt->bindParam(':link', $link);
  $stmt->bindParam(':tags', $tags);
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  </head>
  <body>
    <?php include('backheader.php'); ?>
    <h3 class="text-secondary fw-bold mt-2 ms-2 text-center"><i class="bi bi-image"></i> Edit Image</h3>
    <div class="mt-3">
      <div class="roow">
        <div class="cool-6">
          <div class="caard">
            <center><img src="thumbnails/<?php echo htmlspecialchars($image['filename']); ?>" alt="<?php echo htmlspecialchars($image['title']); ?>" class="art"></center>
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
                <textarea class="form-control border rounded-3 text-secondary fw-bold border-4" type="text" value="<?php echo htmlspecialchars($image['imgdesc']); ?>" name="imgdesc" placeholder="Image description" maxlength="400" style="height: 100px;" required><?php echo htmlspecialchars($image['imgdesc']); ?></textarea>
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
              <input type="submit" value="Save" class="btn w-100 btn-lg bg-primary text-white fw-bold mb-2">
              <a class="btn btn-danger btn-lg w-100 fw-bold text-white" href="profile.php">back</a>
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

      @media (min-width: 768px) {
        .navbar-nav {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
        }
        
        .text-c {
          width: 100%;
        }
      
        .nav-center {
          margin-left: 15px;
          margin-right: 15px;
        }

        .nav-right {
          position: absolute;
          right: 10px;
          top: 10;
          align-items: center;
        }
      }
      
      @media (max-width: 767px) {
        .navbar-brand {
          position: static;
          display: block;
          text-align: center;
          margin: auto;
          transform: none;
        }
        
        .navbar-brand {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          font-size: 18px;
        }
      }
    
      .navbar {
        height: 45px;
      }
      
      .navbar-brand {
        font-size: 18px;
      }

      @media (min-width: 992px) {
        .navbar-toggler1 {
          display: none;
        }
      }
    
      .navbar-toggler1 {
        background-color: #ededed;
        border: none;
        font-size: 8px;
        margin-top: -2px;
        margin-left: 8px;
        border-radius: 5px;
        padding: 6px;
        transition: background-color 0.3s ease; 
      }

      .navbar-toggler1:hover {
        background-color: rgba(0,0,0,0.2);
      }
    </style> 
    <script>
      function goBack() {
        window.location.href = "profile.php";
      }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>