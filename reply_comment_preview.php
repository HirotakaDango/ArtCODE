<?php
require_once('auth.php');

// Open the SQLite database
$db = new SQLite3('database.sqlite');
 
// Create the reply_comments table if it doesn't exist
$db->exec('CREATE TABLE IF NOT EXISTS reply_comments (id INTEGER PRIMARY KEY AUTOINCREMENT, comment_id INTEGER, email TEXT, reply TEXT, date DATETIME, FOREIGN KEY (comment_id) REFERENCES comments(id))');

// Get the image id from comment.php
$imageid = $_GET['imageid'];
$pageUrl = $_GET['page'];
$sortUrl = $_GET['by'];
$replySort = $_GET['sort'];
$commentId = $_GET['comment_id'];

// Get the id of the image
$stmt = $db->prepare("SELECT * FROM images WHERE id=:imageid");
$stmt->bindValue(':imageid', $imageid, SQLITE3_INTEGER);
$image = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

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
    $stmt = $db->prepare('INSERT INTO reply_comments (comment_id, email, reply, date) VALUES (?, ?, ?, ?)');
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
  // Get the comment_id and image_id for the reply to be deleted
  $get_reply_info_stmt = $db->prepare('SELECT comment_id FROM reply_comments WHERE id = ?');
  $get_reply_info_stmt->bindValue(1, $_GET['delete_reply_id'], SQLITE3_INTEGER);
  $reply_info_result = $get_reply_info_stmt->execute()->fetchArray(SQLITE3_ASSOC);
  $imageid = $_GET['imageid'];
  $pageUrl = $_GET['page'];
  $sortUrl = $_GET['by'];
  $replySort = $_GET['sort'];

  if ($reply_info_result !== false) {
    $comment_id = $reply_info_result['comment_id'];
    // $image_id = $_GET['imageid'];
    // $image_id = $reply_info_result['image_id'];

    // Delete the selected reply from the reply_comments table
    $delete_reply_stmt = $db->prepare('DELETE FROM reply_comments WHERE id = ?');
    $delete_reply_stmt->bindValue(1, $_GET['delete_reply_id'], SQLITE3_INTEGER);
    $delete_reply_stmt->execute();

    // Redirect back to the current page with the imageid and comment_id parameters
    $redirect_url = 'reply_comment_preview.php?sort=' . urlencode($replySort) . '&by=' . urlencode($sortUrl) . '&imageid=' . urlencode($imageid) . '&comment_id=' . urlencode($comment_id) . '&page=' . urlencode($pageUrl);
    header('Location: ' . $redirect_url);
    exit();
  } else {
    // Handle the case where the comment_id or image_id could not be retrieved
  }
}

// Get the current comment from the comments table
$stmt = $db->prepare("SELECT comment FROM comments WHERE id=:comment_id");
$stmt->bindValue(':comment_id', $commentId, SQLITE3_INTEGER);
$commentResult = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

// Assign the comment to the variable $commentName
$commentName = $commentResult['comment'];
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Reply to <?php echo $commentName; ?></title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="dropdown">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 my-2 btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?sort=newest&by=<?php echo $sortUrl; ?>&imageid=<?php echo $imageid; ?>&comment_id=<?php echo $commentId; ?>&page=<?php echo $pageUrl; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['sort']) || $_GET['sort'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?sort=oldest&by=<?php echo $sortUrl; ?>&imageid=<?php echo $imageid; ?>&comment_id=<?php echo $commentId; ?>&page=<?php echo $pageUrl; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['sort']) && $_GET['sort'] == 'oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?sort=top&by=<?php echo $sortUrl; ?>&imageid=<?php echo $imageid; ?>&comment_id=<?php echo $commentId; ?>&page=<?php echo $pageUrl; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['sort']) && $_GET['sort'] == 'top') echo 'active'; ?>">top comments</a></li>
      </ul> 
    </div>
        <?php 
        if(isset($_GET['sort'])){
          $sort = $_GET['sort'];
 
          switch ($sort) {
            case 'newest':
            include "reply_comment_preview_desc.php";
            break;
            case 'oldest':
            include "reply_comment_preview_asc.php";
            break;
            case 'top':
            include "reply_comment_preview_top.php";
            break;
          }
        }
        else {
          include "reply_comment_preview_desc.php";
        }
        
        ?>
    <div class="fixed-bottom w-100">
      <form action="" method="POST">
        <div class="input-group w-100">
          <textarea id="reply" name="reply" class="form-control fw-semibold" style="height: 40px; max-height: 150px;" placeholder="Type your comment..." aria-label="Type a message..." aria-describedby="basic-addon2" 
            onkeydown="if(event.keyCode == 13) { this.style.height = (parseInt(this.style.height) + 10) + 'px'; return true; }"
            onkeyup="this.style.height = '40px'; var newHeight = (this.scrollHeight + 10 * (this.value.split(/\r?\n/).length - 1)) + 'px'; if (parseInt(newHeight) > 150) { this.style.height = '150px'; } else { this.style.height = newHeight; }"></textarea>
          <input type="hidden" name="reply_comment_id" value="<?= $comment['id'] ?>">
          <button type="submit" class="btn btn-primary fw-bold">send</button>
        </div>
      </form> 
    </div>
    <br><br><br>
    <div class="d-none-sm position-fixed top-50 start-0 translate-middle-y">
      <button class="btn btn-primary rounded-pill rounded-start-0 fw-bold btn-md ps-1" onclick="goBack()">
        <i class="bi bi-arrow-left-circle-fill"></i>
      </button>
    </div>
    <script>
      function goBack() {
        window.location.href = "comments_preview.php?by=<?php echo $sortUrl; ?>&imageid=<?php echo $imageid; ?>&page=<?php echo $pageUrl; ?>";
        // window.location.href = "index.php";
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