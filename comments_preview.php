<?php
require_once('auth.php');

// Connect to the database
$db = new SQLite3('database.sqlite');
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS comments (id INTEGER PRIMARY KEY, filename TEXT, email TEXT, comment TEXT, created_at DATETIME)");
$stmt->execute();

// Get the filename from the query string
$filename = $_GET['imageid'];

// Get the image information from the database
$stmt = $db->prepare("SELECT * FROM images WHERE id=:filename");
$stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
$image = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

// Check if the image exists
if (!$image) {
  // Redirect to the homepage if not
  header('Location: index.php');
  exit();
}

// Check if the form was submitted for adding a new comment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
  // Get the comment from the form data
  $comment = filter_var($_POST['comment'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $comment = nl2br($comment);
  $email = $_SESSION['email'];

  // Check if the comment is not empty
  if (!empty(trim($comment))) {
    // Get the current date in the format "years/month/day"
    $currentDate = date("Y/m/d");

    // Insert the comment into the database
    $stmt = $db->prepare("INSERT INTO comments (filename, email, comment, created_at) VALUES (:filename, :email, :comment, :created_at)");
    $stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':comment', $comment, SQLITE3_TEXT);
    $stmt->bindValue(':created_at', $currentDate, SQLITE3_TEXT); // Bind the current date
    $stmt->execute();
  }

  // Redirect back to the image page
  $currentURL = $_SERVER['REQUEST_URI'];
  $redirectURL = $currentURL;
  header("Location: $redirectURL");
  exit();
}

// Check if the form was submitted for updating or deleting a comment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
  $action = $_POST['action'];
  $comment_id = $_POST['comment_id'];

  // Get the email of the current user
  $email = $_SESSION['email'];

  // Check if the comment belongs to the current user
  $stmt = $db->prepare("SELECT * FROM comments WHERE id=:comment_id AND email=:email");
  $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $comment = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

  if ($comment) {
    if ($action == 'delete') {
      // Delete the comment from the comments table
      $stmt = $db->prepare("DELETE FROM comments WHERE id=:comment_id");
      $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
      $stmt->execute();

      // Delete the corresponding replies from the reply_comments table
      $stmt = $db->prepare("DELETE FROM reply_comments WHERE comment_id=:comment_id");
      $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
      $stmt->execute();
    }
  }

  // Redirect back to the image page
  $currentURL = $_SERVER['REQUEST_URI'];
  $redirectURL = $currentURL;
  header("Location: $redirectURL");
  exit();
}

// Set the number of comments to display per page
$comments_per_page = 100;

// Get the current page from the URL, or default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the starting offset for the current page
$offset = ($page - 1) * $comments_per_page;

// Get the total number of comments for the current image
$total_comments_stmt = $db->prepare("SELECT COUNT(*) FROM comments WHERE filename=:filename");
$total_comments_stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
$total_comments = $total_comments_stmt->execute()->fetchArray()[0];

// Calculate the total number of pages
$total_pages = ceil($total_comments / $comments_per_page);
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Comment Section</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="dropdown">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=newest&imageid=<?php echo $filename; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?by=oldest&imageid=<?php echo $filename; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
      </ul> 
    </div>
        <?php 
        if(isset($_GET['by'])){
          $sort = $_GET['by'];
 
          switch ($sort) {
            case 'newest':
            include "comments_preview_desc.php";
            break;
            case 'oldest':
            include "comments_preview_asc.php";
            break;
          }
        }
        else {
          include "comments_preview_desc.php";
        }
        
        ?>
    <div class="fixed-bottom w-100">
      <form action="" method="POST">
        <div class="input-group w-100 rounded-bottom-2">
          <textarea id="message-input" name="comment" class="form-control fw-semibold" style="height: 40px; max-height: 150px;" placeholder="Type your comment..." aria-label="Type a message..." aria-describedby="basic-addon2" 
            onkeydown="if(event.keyCode == 13) { this.style.height = (parseInt(this.style.height) + 10) + 'px'; return true; }"
            onkeyup="this.style.height = '40px'; var newHeight = (this.scrollHeight + 10 * (this.value.split(/\r?\n/).length - 1)) + 'px'; if (parseInt(newHeight) > 150) { this.style.height = '150px'; } else { this.style.height = newHeight; }"></textarea>
          <button type="submit" class="btn btn-primary fw-bold">send</button>
        </div>
      </form> 
    </div>
    <br><br><br>
    <style>
      .text-stroke {
        -webkit-text-stroke: 1px;
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

          // Remove blur and apply custom blur to NSFW images after they load
          image.addEventListener("load", function() {
            image.style.filter = ""; // Remove initial blur
            if (image.classList.contains("nsfw")) {
              image.style.filter = "blur(4px)"; // Apply blur to NSFW images
          
              // Add overlay with icon and text
              let overlay = document.createElement("div");
              overlay.classList.add("overlay", "rounded");
              let icon = document.createElement("i");
              icon.classList.add("bi", "bi-eye-slash-fill", "text-white");
              overlay.appendChild(icon);
              let text = document.createElement("span");
              text.textContent = "R-18";
              text.classList.add("shadowed-text", "fw-bold", "text-white");
              overlay.appendChild(text);
              image.parentNode.appendChild(overlay);
            }
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