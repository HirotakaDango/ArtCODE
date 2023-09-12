<?php
require_once('auth.php');

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Create the album table if it doesn't exist 
$stmt = $db->prepare('CREATE TABLE IF NOT EXISTS image_album (id INTEGER PRIMARY KEY AUTOINCREMENT, image_id INTEGER NOT NULL, email TEXT NOT NULL, album_id INTEGER NOT NULL, FOREIGN KEY (image_id) REFERENCES image(id), FOREIGN KEY (album_id) REFERENCES album(id));');
$stmt = $db->prepare('CREATE TABLE IF NOT EXISTS album ( id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, album_name TEXT NOT NULL);');
$stmt->execute();

// Check if an image has been added to the album
if (isset($_GET['add'])) {
  $image_id = intval($_GET['add']);
  $email = $_SESSION['email'];
  $stmt = $db->prepare('INSERT INTO album (email, image_id) VALUES (:email, :image_id)');
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':image_id', $image_id, SQLITE3_INTEGER);
  $stmt->execute();

  // Store success message in session
  $_SESSION['success_message'] = 'New album added';

  // Redirect to current page to prevent duplicate form submissions
  header("Location: {$_SERVER['REQUEST_URI']}");
  exit();
}

// Check if a new album name has been submitted
if (isset($_POST['album_name']) && !empty($_POST['album_name'])) {
  $album_name = filter_input(INPUT_POST, 'album_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $email = $_SESSION['email'];

  // Check if an album with the same name already exists
  $stmt_check_album = $db->prepare('SELECT COUNT(*) FROM album WHERE email = :email AND album_name = :album_name');
  $stmt_check_album->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt_check_album->bindValue(':album_name', $album_name, SQLITE3_TEXT);
  $existing_count = $stmt_check_album->execute()->fetchArray(SQLITE3_NUM)[0];

  if ($existing_count > 0) {
    // Generate a unique name by appending a number to the album name
    $album_name_number = $album_name;
    $counter = 1;
    while ($existing_count > 0) {
      $album_name_number = $album_name . '_' . $counter;
      $stmt_check_album->bindValue(':album_name', $album_name_number, SQLITE3_TEXT);
      $existing_count = $stmt_check_album->execute()->fetchArray(SQLITE3_NUM)[0];
      $counter++;
    }
    $album_name = $album_name_number;
  }

  $stmt = $db->prepare('INSERT INTO album (email, album_name) VALUES (:email, :album_name)');
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':album_name', $album_name, SQLITE3_TEXT);
  $stmt->execute();

  // Store success message in session
  $_SESSION['success_message'] = $album_name. ' added';

  // Redirect to current page to prevent duplicate form submissions
  header("Location: {$_SERVER['REQUEST_URI']}");
  exit();
}
 
if (isset($_POST['delete_album'])) {
  $album_name = filter_input(INPUT_POST, 'delete_album', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $email = $_SESSION['email'];
  
  // First, delete the images associated with the album from the "image_album" table
  $stmt_delete_images = $db->prepare('DELETE FROM image_album WHERE email = :email AND album_id IN (SELECT id FROM album WHERE email = :email AND album_name = :album_name)');
  $stmt_delete_images->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt_delete_images->bindValue(':album_name', $album_name, SQLITE3_TEXT);
  $stmt_delete_images->execute();
  
  // Then, delete the album from the "album" table
  $stmt_delete_album = $db->prepare('DELETE FROM album WHERE email = :email AND album_name = :album_name');
  $stmt_delete_album->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt_delete_album->bindValue(':album_name', $album_name, SQLITE3_TEXT);
  $stmt_delete_album->execute();

  $_SESSION['danger_message'] = $album_name . ' has been deleted';
  header("Location: {$_SERVER['REQUEST_URI']}");
  exit();
}

// Close the database connection
$db->close();
?>

<!DOCTYPE html>
<html>
  <head>
    <title>My Album</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <button type="button" class="btn btn-primary position-fixed bottom-0 end-0 me-3 mb-3 fw-bold" data-bs-toggle="modal" data-bs-target="#createAlbum" style="z-index: 2;">
      <i class="bi bi-plus-circle" style="-webkit-text-stroke: 1px;"></i> create new
    </button>
    <!-- Display alerts -->
    <div class="container mt-3">
      <?php if (isset($_SESSION['success_message'])) { ?>
        <div class="alert alert-success" role="alert">
          <?= $_SESSION['success_message'] ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
      <?php } ?>

      <?php if (isset($_SESSION['danger_message'])) { ?>
        <div class="alert alert-danger" role="alert">
          <?= $_SESSION['danger_message'] ?>
        </div>
        <?php unset($_SESSION['danger_message']); ?>
      <?php } ?>
    </div>
    <div class="modal fade" id="createAlbum" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h5 class="text-secondary fw-bold mt-2"><i class="bi bi-images"></i> Create New Album</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form method="post" class="container">
              <div class="form-floating mb-2">
                <input type="text" class="form-control rounded-3 border text-secondary fw-bold border-4" id="album_name" name="album_name" maxlength="25">
                <label for="floatingInput" class="text-secondary fw-bold">Create new album</label>
              </div>
              <input class="form-control bg-primary text-white fw-bold" type="submit" value="Create">
            </form>
          </div>
        </div>
      </div>
    </div>
    <h5 class="text-secondary fw-bold text-center mt-2 mb-3"><i class="bi bi-images"></i> My Albums</h5>
    <?php
      // Connect to the SQLite database
      $db = new SQLite3('database.sqlite');

      // Display the album list
      $email = $_SESSION['email'];
      $stmt = $db->prepare('SELECT DISTINCT id, album_name FROM album WHERE email = :email ORDER BY id DESC');
      $stmt->bindValue(':email', $email, SQLITE3_TEXT);
      $results = $stmt->execute();
    ?>

    <div class="images">
      <?php
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
          $album_name = $row['album_name'];
          $album_id = $row['id'];

          // Fetch the latest image from the image_album table for the current album
          $query = "SELECT ia.image_id, i.filename  FROM image_album AS ia INNER JOIN images AS i ON ia.image_id = i.id WHERE ia.album_id = '$album_id' ORDER BY ia.id DESC LIMIT 1";
          $latest_image = $db->querySingle($query, true);

          // Check if the album name is not null before calling urlencode()
          if ($album_name) {
      ?>
          <div class="image-container">
            <div class="position-relative">
              <?php
                if ($latest_image) {
                  $thumbnail_path = "thumbnails/" . $latest_image['filename'];
              ?>
                <a class="d-block shadow rounded" href="album_images.php?album=<?= urlencode($album_id) ?>"><img data-src="<?= $thumbnail_path ?>" class="lazy-load imagesImg rounded shadow" alt="Album Image"></a>
              <?php
                } else {
              ?>
                <a class="d-block shadow rounded" href="album_images.php?album=<?= urlencode($album_id) ?>"><img data-src="icon/bg.png" class="lazy-load imagesImg rounded shadow"></a>
              <?php
              } ?>
              <div class="position-absolute top-0 start-0">
                <div class="dropdown">
                  <button class="btn btn-sm btn-dark ms-1 mt-1 rounded-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                  </button>
                  <ul class="dropdown-menu">
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this album?');">
                      <input type="hidden" name="delete_album" value="<?= $album_name ?>">
                      <li><button class="dropdown-item fw-bold"><i class="bi bi-trash-fill"></i> Delete</button></li>
                    </form>
                    <li><a class="dropdown-item fw-bold" href="album_images.php?album=<?= urlencode($album_id) ?>"><i class="bi bi-eye-fill"></i> View</a></li>
                    <li><a class="dropdown-item fw-bold" href="edit_album.php?album=<?= urlencode($album_name) ?>"><i class="bi bi-pencil-fill"></i> Edit</a></li>
                  </ul>
                </div>
              </div>
              <button class="fw-bold position-absolute ms-1 mb-1 bottom-0 btn btn-sm btn-dark opacity-50 disabled" style="word-wrap: break-all;"><?= substr($album_name, 0, 13) ?></button>
            </div>
          </div>
        <?php }
      } ?>
    </div>
    <div class="mt-5"></div>
    <?php
      // Close the database connection
      $db->close();
    ?>
    <style>
      .button-w {
        width: 67px;
      }

      .images {
        display: grid;
        grid-template-columns: repeat(2, 1fr); /* Two columns in mobile view */
        grid-gap: 3px;
        justify-content: center;
        margin-right: 3px;
        margin-left: 3px;
      }

      @media (min-width: 768px) {
        /* For desktop view, change the grid layout */
        .images {
          grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
      }

      .imagesA {
        display: block;
        overflow: hidden;
      }

      .imagesImg {
        width: 100%;
        height: auto;
        object-fit: cover;
        height: 200px;
        transition: transform 0.5s ease-in-out;
      }
    </style>
    <script>
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      let imageContainer = document.getElementById("image-container");

      // Set the default placeholder image
      const defaultPlaceholder = "icon/bg.png";

      if ("IntersectionObserver" in window) {
        let imageObserver = new IntersectionObserver(function(entries, observer) {
          entries.forEach(function(entry) {
            if (entry.isIntersecting) {
              let image = entry.target;
              image.src = image.dataset.src;
              imageObserver.unobserve(image);
            }
          });
        });

        lazyloadImages.forEach(function(image) {
          image.src = defaultPlaceholder; // Apply default placeholder
          imageObserver.observe(image);
          image.style.filter = "blur(5px)"; // Apply initial blur to all images
          image.addEventListener("load", function() {
            image.style.filter = "none"; // Remove blur after image loads
          });
        });
      } else {
        let lazyloadThrottleTimeout;

        function lazyload() {
          if (lazyloadThrottleTimeout) {
            clearTimeout(lazyloadThrottleTimeout);
          }
          lazyloadThrottleTimeout = setTimeout(function() {
            let scrollTop = window.pageYOffset;
            lazyloadImages.forEach(function(img) {
              if (img.offsetTop < window.innerHeight + scrollTop) {
                img.src = img.dataset.src;
                img.classList.remove("lazy-load");
              }
            });
            lazyloadImages = Array.from(lazyloadImages).filter(function(image) {
              return image.classList.contains("lazy-load");
            });
            if (lazyloadImages.length === 0) {
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

      // Infinite scrolling
      let loading = false;

      function loadMoreImages() {
        if (loading) return;
        loading = true;

        // Simulate loading delay for demo purposes
        setTimeout(function() {
          for (let i = 0; i < 10; i++) {
            if (lazyloadImages.length === 0) {
              break;
            }
            let image = lazyloadImages[0];
            imageContainer.appendChild(image);
            lazyloadImages = Array.from(lazyloadImages).slice(1);
          }
          loading = false;
        }, 1000);
      }

      window.addEventListener("scroll", function() {
        if (window.innerHeight + window.scrollY >= imageContainer.clientHeight) {
          loadMoreImages();
        }
      });

      // Initial loading
      loadMoreImages();
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>