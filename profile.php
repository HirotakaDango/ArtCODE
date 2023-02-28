<?php
  session_start();
  if (!isset($_SESSION['username'])) {
    header("Location: session.php");
    exit;
  }

  // Connect to the SQLite database
  $db = new SQLite3('database.sqlite');

  // Get the artist name from the database
  $username = $_SESSION['username'];
  $stmt = $db->prepare("SELECT artist, pic, desc, bgpic FROM users WHERE username = :username");
  $stmt->bindValue(':username', $username);
  $result = $stmt->execute();
  $row = $result->fetchArray();
  $artist = $row['artist'];
  $pic = $row['pic'];
  $desc = $row['desc'];
  $bgpic = $row['bgpic'];

  // Handle the user's click on the "Favorite" button
  if (isset($_POST['favorite'])) {
    $filename = $_POST['filename'];
    $stmt = $db->prepare("UPDATE images SET favorite = 1 WHERE filename = :filename AND username = :username");
    $stmt->bindValue(':filename', $filename);
    $stmt->bindValue(':username', $_SESSION['username']);
    $stmt->execute();
  }

  // Get all of the images uploaded by the current user
  $stmt = $db->prepare("SELECT * FROM images WHERE username = :username ORDER BY id DESC");
  $stmt->bindValue(':username', $username);
  $result = $stmt->execute();

  // Count the number of images uploaded by the current user
  $count = 0;
  while ($image = $result->fetchArray()) {
    $count++;
  }
?>

<!DOCTYPE html>
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
          <a class="nav-link" href="forum-chat/index.php"><i class="bi bi-chat-dots-fill"></i></a>
          <a class="nav-link px-2 text-secondary" href="imgupload.php"><i class="bi bi-cloud-arrow-up-fill"></i></a>
          <h1 style="color: gray; margin-top: 7px;" class="nav-link px-2 text-secondary"><a class="nav-link border-bottom" href="index.php">ArtCODE LITE</a></h1>
          <a class="nav-link px-2 text-secondary" href="users.php"><i class="bi bi-person-fill"></i></a>
          <div class="dropdown">
            <a class="nav-link px-2 text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle" style="font-size: 15.5px;"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-start">
              <li><a class="dropdown-item" href="setting.php"><i class="bi bi-gear-fill"></i> Setting</a></li>
              <li><a class="dropdown-item" href="favorite.php"><i class="bi bi-heart-fill"></i> Favorites</a></li>
              <li><a class="dropdown-item" href="tags.php"><i class="bi bi-tags-fill"></i> Tags</a></li>
              <li><a class="dropdown-item" href="logout.php"><i class="bi bi-door-open-fill"></i> Logout</a></li>
            </ul>
          </div>
        </div>
      </nav>
    </center>  
    <center>
      <div class="container w-auto mb-2">
        <div class="row justify-content-center">
          <div>
            <div class="card">
              <div class="card-body">
                <div class="row featurette container">
                  <div class="col-md-5 order-md-1 img-thumbnail" style="background-image: url('<?php echo $bgpic; ?>'); background-size: cover; height: 180px;">
                    <div style="position: relative;">
                      <img class="img-thumbnail mt-4 rounded-circle" src="<?php echo $pic; ?>" alt="Profile Picture" style="width: 120px; height: 120px; border-radius: 4px; margin-left: -32px;">
                      <a class="btn-sm btn btn-danger float-start mt-2" type="button" href="setting.php"><i class="bi bi-pencil-fill"></i></a>
                    </div>
                  </div>
                  <div class="col-md-7 order-md-2">
                    <h3 class="text-secondary ms-1 mt-2 fw-bold"><i class="bi bi-person-circle"></i> <?php echo $artist; ?> <i class="ms-2 bi bi-images"></i> <?php echo $count; ?> </h3>
                    <p class="text-secondary text-center fw-bold"><?php echo $desc; ?></p>
                  </div>      
                </div> 
              </div>
            </div>
          </div>
        </div>
      </div>
    </center>
    <div class="images">
      <?php while ($image = $result->fetchArray()): ?>
        <div class="image-container">
          <a href="images/<?php echo $image['filename']; ?>">
            <img class="lazy-load" data-src="thumbnails/<?php echo $image['filename']; ?>">
          </a>
          <div>
            <form action="delete.php" method="post">
              <input type="hidden" name="filename" value="<?php echo $image['filename']; ?>">
              <input style="margin-top: -73px; margin-left: 8px; font-size: 10px;" class="btn btn-danger fw-bold" type="submit" onclick="return confirm('Are you sure?')" value="Delete">
            </form>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
    <style>
      .image-container {
        margin-bottom: -24px;  
      }
      
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
        height: 200px;
        object-fit: cover;
      }

      .btn1 {
        padding: 10px;
        margin: 10px; 
        border: 8px solid #eee;
        border-radius: 15px;
        color: gray;
        font-weight: 700;
        padding: 8px 20px;
       }
  </style>
  <script>
      document.addEventListener("DOMContentLoaded", function() {
        let lazyloadImages;
        if("IntersectionObserver" in window) {
          lazyloadImages = document.querySelectorAll(".lazy-load");
          let imageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
              if(entry.isIntersecting) {
                let image = entry.target;
                image.src = image.dataset.src;
                image.classList.remove("lazy-load");
                imageObserver.unobserve(image);
              }
            });
          });
          lazyloadImages.forEach(function(image) {
            imageObserver.observe(image);
          });
        } else {
          let lazyloadThrottleTimeout;
          lazyloadImages = document.querySelectorAll(".lazy-load");

          function lazyload() {
            if(lazyloadThrottleTimeout) {
              clearTimeout(lazyloadThrottleTimeout);
            }
            lazyloadThrottleTimeout = setTimeout(function() {
              let scrollTop = window.pageYOffset;
              lazyloadImages.forEach(function(img) {
                if(img.offsetTop < (window.innerHeight + scrollTop)) {
                  img.src = img.dataset.src;
                  img.classList.remove('lazy-load');
                }
              });
              if(lazyloadImages.length == 0) {
                document.removeEventListener("scroll", lazyload);
                window.removeEventListener("resize", lazyload);
                window.removeEventListener("orientationChange", lazyload);
              }
            }, 20);
          }
          document.addEventListener("scroll", lazyload);
          window.addEventListener("resize", lazyload);
          window.addEventListener("orientationChange", lazyload);
        }
      })
  </script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>
