<?php
require_once('auth.php');

// Open the SQLite database
$db = new SQLite3('database.sqlite');

// Create the reply_forum table if it doesn't exist
$db->exec('CREATE TABLE IF NOT EXISTS reply_forum (id INTEGER PRIMARY KEY AUTOINCREMENT, comment_id INTEGER, email TEXT, reply TEXT, date DATETIME, FOREIGN KEY (comment_id) REFERENCES comments(id))');

// Get the image id from comment.php
$pageUrl = $_GET['page'];
$sortUrl = $_GET['by'];
$replySort = $_GET['sort'];
$commentId = $_GET['comment_id'];

// Check if the reply form was submitted
if (isset($_POST['reply_comment_id'], $_POST['reply'])) {
  // Trim the reply text to remove leading and trailing spaces
  $reply = trim($_POST['reply']);

  // Check if the reply is empty after trimming
  if (!empty($reply)) {
    // Prepare the reply text by removing special characters and converting newlines to <br> tags
    $reply = nl2br(filter_var($reply, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));

    // Get the current date in the format (years/month/day)
    $currentDate = date('Y/m/d');

    // Insert a new reply into the reply_comments table
    $stmt = $db->prepare('INSERT INTO reply_forum (comment_id, email, reply, date) VALUES (?, ?, ?, ?)');
    $stmt->bindValue(1, $_POST['reply_comment_id'], SQLITE3_INTEGER);
    $stmt->bindValue(2, $_SESSION['email'], SQLITE3_TEXT);
    $stmt->bindValue(3, $reply, SQLITE3_TEXT);
    $stmt->bindValue(4, $currentDate, SQLITE3_TEXT);
    $stmt->execute();

    // Redirect back to the image page
    $currentURL = $_SERVER['REQUEST_URI'];
    $redirectURL = $currentURL;
    header("Location: $redirectURL");
    exit();
  } else {
    // Handle the case where the reply is empty
    // Display an error message or take appropriate action
  }
}

// Check if the "delete_reply_id" key is set in the $_GET superglobal
if (isset($_GET['delete_reply_id'])) {
  // Get the comment_id for the reply to be deleted
  $get_comment_id_stmt = $db->prepare('SELECT comment_id FROM reply_forum WHERE id = ?');
  $get_comment_id_stmt->bindValue(1, $_GET['delete_reply_id'], SQLITE3_INTEGER);
  $comment_id_result = $get_comment_id_stmt->execute()->fetchArray(SQLITE3_ASSOC);
  $pageUrl = $_GET['page'];
  $sortUrl = $_GET['by'];
  $replySort = $_GET['sort'];

  if ($comment_id_result !== false) {
    $comment_id = $comment_id_result['comment_id'];

    // Delete the selected reply from the reply_forum table
    $delete_reply_stmt = $db->prepare('DELETE FROM reply_forum WHERE id = ?');
    $delete_reply_stmt->bindValue(1, $_GET['delete_reply_id'], SQLITE3_INTEGER);
    $delete_reply_stmt->execute();

    // Redirect back to the current page with the comment_id parameter
    $redirect_url = 'reply_forum.php?sort=' . urlencode($replySort) . '&by=' . urlencode($sortUrl) . '&comment_id=' . urlencode($comment_id) . '&page=' . urlencode($pageUrl);
    header('Location: ' . $redirect_url);
    exit();
  } else {
    // Handle the case where the comment_id could not be retrieved
  }
}

// Get the current comment from the comments table
$stmt = $db->prepare("SELECT comment FROM forum WHERE id=:comment_id");
$stmt->bindValue(':comment_id', $commentId, SQLITE3_INTEGER);
$commentResult = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

// Assign the comment to the variable $commentName
$commentName = $commentResult['comment'];
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <title>Reply to <?php echo $commentName; ?></title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="container d-flex">
      <div class="dropdown me-auto">
        <button class="btn btn-sm fw-bold rounded-pill mb-2 btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-images"></i> sort by
        </button>
        <ul class="dropdown-menu">
          <li><a href="?sort=newest&by=<?php echo $sortUrl; ?>&comment_id=<?php echo $commentId; ?>&page=<?php echo $pageUrl; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['sort']) || $_GET['sort'] == 'newest') echo 'active'; ?>">newest</a></li>
          <li><a href="?sort=oldest&by=<?php echo $sortUrl; ?>&comment_id=<?php echo $commentId; ?>&page=<?php echo $pageUrl; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['sort']) && $_GET['sort'] == 'oldest') echo 'active'; ?>">oldest</a></li>
          <li><a href="?sort=top&by=<?php echo $sortUrl; ?>&comment_id=<?php echo $commentId; ?>&page=<?php echo $pageUrl; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['sort']) && $_GET['sort'] == 'top') echo 'active'; ?>">top comments</a></li>
        </ul> 
      </div>
      <button class="btn btn-sm fw-bold rounded-pill mb-2 btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" onclick="goBack()">
        <i class="bi bi-chevron-left" style="-webkit-text-stroke: 2px;"></i> back
      </button>
    </div>
        <?php 
        if(isset($_GET['sort'])){
          $sort = $_GET['sort'];
 
          switch ($sort) {
            case 'newest':
            include "reply_forum_desc.php";
            break;
            case 'oldest':
            include "reply_forum_asc.php";
            break;
            case 'top':
            include "reply_forum_top.php";
            break;
          }
        }
        else {
          include "reply_forum_desc.php";
        }
        
        ?>
    <nav class="navbar fixed-bottom navbar-expand justify-content-center">
      <div class="container">
        <button type="button" class="w-100 btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold rounded-3" data-bs-toggle="modal" data-bs-target="#comments">post your reply</button>
      </div>
    </nav>
    <div class="modal fade" id="comments" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 bg-transparent">
          <div class="modal-body px-1">
            <form class="form-control border-0 bg-transparent shadow p-0" action="" method="POST">
              <textarea type="text" class="form-control fw-medium bg-body-tertiary border-0 rounded-4 rounded-bottom-0 focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>" style="height: 400px; max-height: 800px;" name="reply" id="reply" placeholder="Post your reply..." aria-label="Type a message..." aria-describedby="basic-addon2" required></textarea>
              <input type="hidden" name="reply_comment_id" value="<?= $comment['id'] ?>">
              <button class="w-100 btn btn-primary rounded-4 rounded-top-0" type="submit"><i class="bi bi-send-fill"></i></button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <br><br><br>
    <div class="d-none-sm position-fixed top-50 start-0 translate-middle-y">
      <button class="btn btn-primary rounded-pill rounded-start-0 fw-bold btn-md ps-1" onclick="goBack()">
        <i class="bi bi-arrow-left-circle-fill"></i>
      </button>
    </div>
    <script>
      function goBack() {
        window.location.href = "forum.php?by=<?php echo $sortUrl; ?>&page=<?php echo $pageUrl; ?>";
      }
    </script>
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