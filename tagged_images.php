<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: session.php");
  exit;
}

$username = $_SESSION['username'];
  
// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Retrieve the tag from the URL parameter and remove any leading/trailing white spaces
$tag = trim(htmlspecialchars($_GET['tag']));

// Retrieve the tag from the URL parameter and remove any leading/trailing white spaces
$tag = trim(htmlspecialchars($_GET['tag']));

// Remove any additional spaces from the tag
$tag = str_replace(' ', '', $tag);

$stmt = $db->prepare("SELECT COUNT(*) FROM images WHERE REPLACE(tags, ' ', '') LIKE :tag OR REPLACE(tags, ' ', '') LIKE :tag_start OR REPLACE(tags, ' ', '') LIKE :tag_end OR tags = :tag_exact");
$stmt->bindValue(':tag', "{$tag},%", SQLITE3_TEXT);
$stmt->bindValue(':tag_start', "%,{$tag}", SQLITE3_TEXT);
$stmt->bindValue(':tag_end', "%,{$tag},%", SQLITE3_TEXT);
$stmt->bindValue(':tag_exact', "{$tag}", SQLITE3_TEXT);
$count = $stmt->execute()->fetchArray()[0];

// Retrieve all images with the specified tag
$stmt = $db->prepare("SELECT * FROM images WHERE REPLACE(tags, ' ', '') LIKE :tag OR REPLACE(tags, ' ', '') LIKE :tag_start OR REPLACE(tags, ' ', '') LIKE :tag_end OR tags = :tag_exact ORDER BY id DESC");
$stmt->bindValue(':tag', "{$tag},%", SQLITE3_TEXT);
$stmt->bindValue(':tag_start', "%,{$tag}", SQLITE3_TEXT);
$stmt->bindValue(':tag_end', "%,{$tag},%", SQLITE3_TEXT);
$stmt->bindValue(':tag_exact', "{$tag}", SQLITE3_TEXT);
$result = $stmt->execute();

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $existing_fav = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE username = '$username' AND image_id = $image_id");

  if ($existing_fav == 0) {
    $db->exec("INSERT INTO favorites (username, image_id) VALUES ('$username', $image_id)");
  }

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: tagged_images.php?tag={$tag}");
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $db->exec("DELETE FROM favorites WHERE username = '$username' AND image_id = $image_id");

  // Redirect to the same page to prevent duplicate form submissions
  header("Location: tagged_images.php?tag={$tag}");
  exit();
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  </head>
  <body>  
    <?php include('header.php'); ?>
    <h5 class="text-secondary ms-2 mt-2 fw-bold"><i class="bi bi-tags-fill"></i> <?php echo $tag; ?> (<?php echo $count; ?>)</h5>
    <!-- Display the images -->
    <div class="images">
      <?php while ($image = $result->fetchArray()): ?>
        <div class="image-container">
          <a href="image.php?filename=<?php echo $image['filename']; ?>">
            <img class="lazy-load" data-src="thumbnails/<?php echo $image['filename']; ?>">
          </a>
          <div class="favorite-btn">
            <?php
              $is_favorited = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE username = '$username' AND image_id = {$image['id']}");
              if ($is_favorited) {
            ?>
              <form action="favindex.php" method="POST">
                <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                <button style="margin-top: -74px; margin-left: 8px; font-size: 10px;" type="submit" class="btn btn-danger rounded-5 fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i></button>
              </form>
            <?php } else { ?>
              <form action="favindex.php" method="POST">
                <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                <button style="margin-top: -74px; margin-left: 8px; font-size: 10px;" type="submit" class="btn btn-danger rounded-5 fw-bold" name="favorite"><i class="bi bi-heart"></i></button>
              </form>
            <?php } ?>
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
        height: auto;
        object-fit: cover;
        height: 200px;
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