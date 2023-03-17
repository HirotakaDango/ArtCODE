<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: session.php");
  exit;
}

$username = $_SESSION['username'];

// Connect to the SQLite database using parameterized query
$db = new SQLite3('database.sqlite');

// Pagination variables
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = 30;
$offset = ($page - 1) * $limit;

// Get the total number of images from the database
$countStmt = $db->prepare("SELECT COUNT(*) FROM images");
$countResult = $countStmt->execute();
$total = $countResult->fetchArray()[0];

// Get 25 images from the database using parameterized query
$stmt = $db->prepare("SELECT images.*, users.username FROM images INNER JOIN users ON images.username = users.username ORDER BY images.id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <script src="script.js"></script>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  </head>
  <body>
    <?php include('header.php'); ?>
    <h5 class="text-secondary fw-bold mt-2 mb-2 ms-2"><i class="bi bi-clock-history"></i> Recents Page <?php echo $page; ?></h5>
    <div class="content">
      <?php while ($image = $result->fetchArray()): ?>
        <?php
          $title = $image['title'];
          $filename = $image['filename'];
          $username = $image['username'];
          $artist = '';
          $stmt = $db->prepare("SELECT id, artist FROM users WHERE username = ?");
          $stmt->bindValue(1, $username, SQLITE3_TEXT);
          $result2 = $stmt->execute();
          if ($user = $result2->fetchArray()) {
            $artist = $user['artist'];
            $id = $user['id'];
          }
        ?>
        <div class="image">
          <a class="img-block" href="image.php?filename=<?php echo $image['filename']; ?>">
            <img class="lazy-load" data-src="thumbnails/<?php echo $image['filename']; ?>">
          </a>
          <div>
            <h5 class="text-white ms-2 fw-bold" style="margin-top: -76px; text-shadow: 1px 1px 1px #000;"><?= $title ?></h5>
            <?php if ($artist != ''): ?>
              <p class="text-white text-decoration-none fw-bold ms-2" style="text-shadow: 1px 1px 1px #000;">uploaded by <a class="text-decoration-none text-white btn btn-sm btn-secondary rounded-pill opacity-75 fw-bold mb-1" href="artist.php?id=<?= $id ?>"><?php echo $artist; ?></a></p>
            <?php endif; ?>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
    <div class="mt-5 mb-2 d-flex justify-content-center btn-toolbar container">
      <?php
        $totalPages = ceil($total / $limit);
        $prevPage = $page - 1;
        $nextPage = $page + 1;

        if ($page > 1) {
          echo '<a type="button" class="btn rounded-pill fw-bold btn-secondary opacity-50 me-1" href="?page=' . $prevPage . '"><i class="bi bi-arrow-left-circle-fill"></i> prev</a>';
        }
        if ($page < $totalPages) {
          echo '<a type="button" class="btn rounded-pill fw-bold btn-secondary opacity-50 ms-1" href="?page=' . $nextPage . '">next <i class="bi bi-arrow-right-circle-fill"></i></a>';
        }
      ?>
    </div>
    <style>
      body {
        margin: 0;
      }
      
      .content {
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
        margin-left: -2px; /* Remove gap on left edge */
        margin-right: -2px; 
      }
      
      .image {
        width: 100%;
        width: calc(33.33% - 2px);
        margin: 0;
        padding: auto;
        box-sizing: border-box;
      }
      
      .img-block {
        display: block;
        width: auto;
        margin-bottom: 17px;
      }
      
      .image img {
        width: 100%;
        height: 400px;
        object-fit: cover;
      }
      
      .image h5, .image p {
        margin: 0;
      }
      
    
      @media screen and (max-width: 767px) {
        .content {
          margin-left: 0;
          margin-right: 0;
        }
        
        .image {
          width: 100%;
          margin: 0;
          border-top: 2px solid lightgray;
          border-bottom: 2px solid lightgray;
        }
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
    <script>
        if ('serviceWorker' in navigator) {
          window.addEventListener('load', function() {
            navigator.serviceWorker.register('sw.js').then(function(registration) {
              console.log('ServiceWorker registration successful with scope: ', registration.scope);
            }, function(err) {
              console.log('ServiceWorker registration failed: ', err);
            });
          });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>