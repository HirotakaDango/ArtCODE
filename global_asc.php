<?php
$email = $_SESSION['email'];

// Connect to the SQLite database using parameterized query
$db = new SQLite3('database.sqlite');

// Pagination variables
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = 60;
$offset = ($page - 1) * $limit;

// Get the total number of images from the database
$countStmt = $db->prepare("SELECT COUNT(*) FROM images");
$countResult = $countStmt->execute();
$total = $countResult->fetchArray()[0];

// Get 25 images from the database using parameterized query
$stmt = $db->prepare("SELECT images.*, users.email FROM images INNER JOIN users ON images.email = users.email ORDER BY images.id ASC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();
?>

    <div class="dropdown mt-2 mb-2">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item fw-bold" href="?by=newest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>">Newest</a></li>
        <li><a class="dropdown-item fw-bold active" href="?by=oldest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>">Oldest</a></li>
      </ul>
    </div>
    <div class="container-fluid">
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
        <?php while ($image = $result->fetchArray()): ?>
          <?php
            $title = $image['title'];
            $filename = $image['filename'];
            $email = $image['email'];
            $artist = '';
            $stmt = $db->prepare("SELECT id, artist FROM users WHERE email = ?");
            $stmt->bindValue(1, $email, SQLITE3_TEXT);
            $result2 = $stmt->execute();
            if ($user = $result2->fetchArray()) {
              $artist = $user['artist'];
              $id = $user['id'];
            }
          ?>
          <div class="col">
            <div class="card h-100 shadow-sm">
              <a class="d-block" href="image.php?artworkid=<?php echo $image['id']; ?>">
                <img class="lazy-load object-fit-cover" style="width: 100%; height: 300px; border-radius: 4.9px 4.9px 0 0;" data-src="thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>">
              </a>
              <div class="card-body">
                <a class="text-decoration-none" href="image.php?artworkid=<?php echo $image['id']; ?>">
                  <h5 class="text-dark fw-bold"><?= $title ?></h5>
                </a>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-secondary disabled fw-bold">by</button>
                    <a class="btn btn-sm btn-secondary fw-bold" href="artist.php?id=<?= $id ?>"><?php echo (strlen($artist) > 25) ? substr($artist, 0, 25) . '...' : $artist; ?></a>
                  </div>
                  <small class="text-body-secondary fw-bold"><?php echo $image['date']; ?></small>
                </div>
              </div>
            </div>
          </div> 
        <?php endwhile; ?>
      </div>
    </div> 
    <div class="mt-5 mb-2 d-flex justify-content-center btn-toolbar container">
      <?php
        $totalPages = ceil($total / $limit);
        $prevPage = $page - 1;
        $nextPage = $page + 1;

        if ($page > 1) {
          echo '<a type="button" class="btn rounded-pill fw-bold btn-secondary opacity-50 me-1" href="?by=oldest&page=' . $prevPage . '"><i class="bi bi-arrow-left-circle-fill"></i> prev</a>';
        }
        if ($page < $totalPages) {
          echo '<a type="button" class="btn rounded-pill fw-bold btn-secondary opacity-50 ms-1" href="?by=oldest&page=' . $nextPage . '">next <i class="bi bi-arrow-right-circle-fill"></i></a>';
        }
      ?>
    </div>
    <div class="mt-5"></div>
    <style>
      body {
        margin: 0;
      }
      
      .content {
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
        margin-left: 3px;
        margin-right: 1px; 
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
        border-radius: 4px;
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
        
        .image img {
          border-radius: 0;
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