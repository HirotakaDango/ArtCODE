<?php
require_once('prompt.php'); 

// Connect to SQLite database
$db = new PDO('sqlite:../database.sqlite');

// If the "Remove" button is clicked, delete the image from the database and the folder
if (isset($_POST['id'])) {
  $id = $_POST['id'];

  // Delete record from the 'images' table
  $stmt = $db->prepare("SELECT * FROM images WHERE id = :id");
  $stmt->bindValue(':id', $id, PDO::PARAM_INT);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($row) {
    $filename = $row['filename'];
    unlink('../images/' . $filename);
    unlink('../thumbnails/' . $filename);
    $stmt = $db->prepare("DELETE FROM images WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
  }

  // Delete records from the 'image_album' table
  $stmt = $db->prepare("DELETE FROM image_album WHERE image_id = :id");
  $stmt->bindValue(':id', $id, PDO::PARAM_INT);
  $stmt->execute();

  // Delete records from the 'favorites' table
  $stmt = $db->prepare("DELETE FROM favorites WHERE image_id = :id");
  $stmt->bindValue(':id', $id, PDO::PARAM_INT);
  $stmt->execute();

  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}

// Query to get all images from the "images" table
$stmt = $db->query('SELECT * FROM images ORDER BY id DESC');
?>

    <?php include('admin_header.php'); ?>
    <div class="container mt-5">
      <div class="row">
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?> 
          <div class="col-md-6 mb-2">
            <div class="card mb-2 h-100">
              <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-images"></i>
                Images
              </div>
          
              <div class="card-body">
                <a class="d-block" href="#" data-bs-toggle="modal" data-bs-target="#info<?= $row['id'] ?>">
                  <img data-src="../thumbnails/<?= $row['filename'] ?>" class="rounded lazy-load object-fit-cover" style="width: 100%; height: 300px;">
                </a>

                <!-- Modal -->
                <div class="modal fade" id="info<?= $row['id'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header border-0">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Image ID: "<?php echo $row['id'];?>"</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <img class="lazy-load rounded shadow mb-3" data-src="../thumbnails/<? echo $row['filename']; ?>" style="width: 100%; height: 100%;">
                        <p class="card-text fw-bold mt-1">User's email: <?= $row['email'] ?></p>
                        <p class="card-text fw-bold mt-1">Title: <?= $row['title'] ?></p>
                        <p class="card-text fw-bold mt-1">Desc: <?= $row['imgdesc'] ?></p>
                        <p class="card-text fw-bold mt-1">Link: <a href="<?= $row['link'] ?>"><?= $row['link'] ?></a></p>
                        <p class="card-text fw-bold mt-1">Tags: <?= $row['tags'] ?></p>
                        <?php
                          // Get image size in megabytes
                          $image_size = round(filesize('../images/' . $row['filename']) / (1024 * 1024), 2);

                          // Get image dimensions
                          list($width, $height) = getimagesize('../images/' . $row['filename']);

                          // Display image information
                          echo "<p class='text-start fw-semibold'>Image data size: " . $image_size . " MB</p>";
                          echo "<p class='text-start fw-semibold'>Image dimensions: " . $width . "x" . $height . "</p>";
                        ?>
                        <form method="post" action="" class="mt-3">
                          <div class="btn-group w-100">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-danger rounded-start fw-bold" onclick="return confirm('Are you sure you want to delete this image?')">
                              <i class="bi bi-trash"></i>
                            </button>
                            <a class="btn btn-primary fw-bold" href="../images/<?php echo $row['filename']; ?>" download><i class="bi bi-download"></i></a>
                            <a class="btn btn-primary rounded-end fw-bold" href="../image.php?artworkid=<?php echo $row['id'];?>"><i class="bi bi-eye-fill"></i></a>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div> 
              </div>
            </div>
          </div>
        <?php endwhile; ?> 
      </div>
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

    <?php include('end.php'); ?>
