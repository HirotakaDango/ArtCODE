<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: session.php");
  exit;
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
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
  </head>
  <body>
    <?php include('header.php'); ?>
    <h5 class="ms-2 mt-2 text-secondary fw-bold"><i class="bi bi-images"></i> Popular Images</h5>
    <div class="images mb-2">
      <?php
        $dbP = new SQLite3('database.sqlite');
        $username = $_SESSION['username'];
        
        // Get all of the images from the database using parameterized query
        $stmtP = $dbP->prepare("SELECT images.id, images.filename, images.tags, images.title, COUNT(favorites.id) AS favorite_count FROM images LEFT JOIN favorites ON images.id = favorites.image_id GROUP BY images.id ORDER BY favorite_count DESC LIMIT 100");
        $resultP = $stmtP->execute();
        while ($imageP = $resultP->fetchArray()): ?>
          <div class="image-container">
            <a href="image.php?filename=<?php echo $imageP['filename']; ?>">
              <img class="lazy-load hori" data-src="thumbnails/<?php echo $imageP['filename']; ?>">
            </a>
          <div class="favorite-btn">
            <?php
              $favorite_countP = $dbP->querySingle("SELECT COUNT(*) FROM favorites WHERE username = '$username' AND image_id = {$imageP['id']}");
              $favorite_countP = $imageP['favorite_count'];
              if ($favorite_countP) {
            ?>
              <p style="margin-top: -40px; margin-left: 8px; text-shadow: 1px 1px 1px #020202;" class="text-white fw-bold"><?php echo $favorite_countP; ?> favorites</p>
            <?php } ?> 
          </div>  
        </div>
      <?php endwhile; ?>
    </div>
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