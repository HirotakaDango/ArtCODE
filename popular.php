<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}

$dbP = new SQLite3('database.sqlite'); 

// Define the number of images to display per page
$limit = 100;

// Get the current page number from the query string
if (isset($_GET['page'])) {
  $page = $_GET['page'];
} else {
  $page = 1;
}

$page = isset($_GET['page']) ? $_GET['page'] : 1;

// Calculate the offset value for the SQL query
$offset = ($page - 1) * $limit;

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $existing_fav = $dbP->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = $image_id");

  if ($existing_fav == 0) {
    $dbP->exec("INSERT INTO favorites (email, image_id) VALUES ('{$_SESSION['email']}', $image_id)");
  }

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: popular.php?page=$page");
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $dbP->exec("DELETE FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = $image_id");

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: popular.php?page=$page"); 
  exit();
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
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
      
      .image-container {
        margin-bottom: -24px;  
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
    </style>
  </head>
  <body>
    <?php include('header.php'); ?>
    <h5 class="ms-2 mt-2 text-secondary fw-bold"><i class="bi bi-images"></i> Popular Images</h5>
    <div class="images mb-2">
      <?php
        $email = $_SESSION['email'];

        // Get the total number of images
        $total = $dbP->querySingle("SELECT COUNT(*) FROM images");

        // Calculate the total number of pages
        $totalPages = ceil($total / $limit);
 
        // Get the images for the current page using a parameterized query with LIMIT and OFFSET clauses
        $stmtP = $dbP->prepare("SELECT images.id, images.filename, images.tags, images.title, COUNT(favorites.id) AS favorite_count FROM images LEFT JOIN favorites ON images.id = favorites.image_id GROUP BY images.id ORDER BY favorite_count DESC LIMIT :limit OFFSET :offset");
        $stmtP->bindValue(':limit', $limit, SQLITE3_INTEGER);
        $stmtP->bindValue(':offset', $offset, SQLITE3_INTEGER);
        $resultP = $stmtP->execute();

        // Display the images for the current page
        while ($imageP = $resultP->fetchArray()):
      ?>
        <div class="image-container">
          <a href="image.php?filename=<?php echo $imageP['id']; ?>">
            <img class="lazy-load hori" data-src="thumbnails/<?php echo $imageP['filename']; ?>">
          </a>
          <div class="favorite-btn">
            <?php
              $stmt = $dbP->prepare("SELECT COUNT(*) FROM favorites WHERE email = ? AND image_id = ?");
              $stmt->bindValue(1, $email, SQLITE3_TEXT);
              $stmt->bindValue(2, $imageP['id'], SQLITE3_INTEGER);
              $result = $stmt->execute()->fetchArray();
              $is_favorited = $result[0];
              if ($is_favorited) {
            ?>
              <form method="POST">
                <input type="hidden" name="image_id" value="<?php echo $imageP['id']; ?>">
                <button style="margin-top: -68px;" type="submit" class="p-b3 btn btn-sm rounded btn-dark opacity-50 fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i></button>
              </form>
            <?php } else { ?>
              <form method="POST">
                <input type="hidden" name="image_id" value="<?php echo $imageP['id']; ?>">
                <button type="submit" class="p-b3 btn btn-sm rounded btn-dark opacity-50 fw-bold" name="favorite"><i class="bi bi-heart"></i></button>
              </form>
            <?php } ?> 
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