<?php
require_once('auth.php');

// Open the SQLite database
$db = new SQLite3('../../database.sqlite');
 
// Create the reply_comments_novel table if it doesn't exist
$db->exec('CREATE TABLE IF NOT EXISTS reply_comments_novel (id INTEGER PRIMARY KEY AUTOINCREMENT, comment_id INTEGER, email TEXT, reply TEXT, date DATETIME, FOREIGN KEY (comment_id) REFERENCES comments(id))');

// Get the image id from comment.php
$novelid = $_GET['novelid'];

// Get the id of the image
$stmt = $db->prepare("SELECT * FROM novel WHERE id=:novelid");
$stmt->bindValue(':novelid', $novelid, SQLITE3_INTEGER);
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

    // Insert a new reply into the reply_comments_novel table along with the date
    $stmt = $db->prepare('INSERT INTO reply_comments_novel (comment_id, email, reply, date) VALUES (?, ?, ?, ?)');
    $stmt->bindValue(1, $_POST['reply_comment_id'], SQLITE3_INTEGER);
    $stmt->bindValue(2, $_SESSION['email'], SQLITE3_TEXT);
    $stmt->bindValue(3, $reply, SQLITE3_TEXT);
    $stmt->bindValue(4, $currentDate, SQLITE3_TEXT);
    $stmt->execute();

    // Redirect back to the current page with the comment_id parameter
    header('Location: reply_comment_novel.php?novelid='.$novelid.'&comment_id=' . $_POST['reply_comment_id']);
    exit();
  } else {
    // Handle the case where the reply is empty
    // Display an error message or take appropriate action
  }
}

// Check if the "delete_reply_id" key is set in the $_GET superglobal
if (isset($_GET['delete_reply_id'])) {
  // Get the comment_id and image_id for the reply to be deleted
  $get_reply_info_stmt = $db->prepare('SELECT comment_id FROM reply_comments_novel WHERE id = ?');
  $get_reply_info_stmt->bindValue(1, $_GET['delete_reply_id'], SQLITE3_INTEGER);
  $reply_info_result = $get_reply_info_stmt->execute()->fetchArray(SQLITE3_ASSOC);
  $novelid = $_GET['novelid'];
  

  if ($reply_info_result !== false) {
    $comment_id = $reply_info_result['comment_id'];
    // $image_id = $_GET['novelid'];
    // $image_id = $reply_info_result['image_id'];

    // Delete the selected reply from the reply_comments_novel table
    $delete_reply_stmt = $db->prepare('DELETE FROM reply_comments_novel WHERE id = ?');
    $delete_reply_stmt->bindValue(1, $_GET['delete_reply_id'], SQLITE3_INTEGER);
    $delete_reply_stmt->execute();

    // Redirect back to the current page with the novelid and comment_id parameters
    $redirect_url = 'reply_comment_novel.php?novelid=' . urlencode($novelid) . '&comment_id=' . urlencode($comment_id);
    header('Location: ' . $redirect_url);
    exit();
  } else {
    // Handle the case where the comment_id or image_id could not be retrieved
  }
}

// Get the selected comment based on its ID
$comment_id = isset($_GET['comment_id']) ? $_GET['comment_id'] : null;
if ($comment_id !== null) {
  $comment = $db->prepare('SELECT * FROM comments_novel WHERE id = ?');
  $comment->bindValue(1, $comment_id, SQLITE3_INTEGER);
  $comment = $comment->execute()->fetchArray(SQLITE3_ASSOC);

  // Get all replies for the selected comment from the reply_comments_novel table, along with the user information
  $replies = $db->prepare('SELECT rc.*, u.artist, u.pic, u.id as userid FROM reply_comments_novel rc JOIN users u ON rc.email = u.email WHERE rc.comment_id = ? ORDER BY rc.id DESC');
  $replies->bindValue(1, $comment_id, SQLITE3_INTEGER);
  $replies = $replies->execute();
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <title>Reply Section</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container-fluid mt-3">
      <nav aria-label="breadcrumb">
        <div class="d-none d-md-block d-lg-block">
          <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-3" style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);">
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none" href="index.php">
                ArtCODE
              </a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/novel/">Home</a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none text-white" href="view.php?id=<?php echo $image['id']; ?>"><?php echo $image['title']; ?></a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none text-white" href="comments.php?novelid=<?php echo $novelid; ?>">Comment ID: <?php echo $comment['id']; ?></a>
            </li>
            <li class="breadcrumb-item active disabled fw-bold" aria-current="page">
              Reply
            </li>
          </ol>
        </div>
        <div class="d-md-none d-lg-none">
          <a class="btn bg-body-tertiary p-3 fw-bold w-100 text-start mb-2" data-bs-toggle="collapse" href="#collapseModal" role="button" aria-expanded="false" aria-controls="collapseExample">
            <i class="bi bi-list" style="-webkit-text-stroke: 1px;"></i> Menu
          </a>
          <div class="collapse bg-body-tertiary mb-2 rounded" id="collapseModal">
            <div class="btn-group-vertical w-100">
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">ArtCODE</a>
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/novel/">Home</a>
              <a class="btn py-2 rounded text-start fw-medium"href="view.php?id=<?php echo $image['id']; ?>"><?php echo $image['title']; ?></a>
              <a class="btn py-2 rounded text-start fw-medium"href="comments.php?novelid=<?php echo $novelid; ?>">Comment ID: <?php echo $comment['id']; ?></a>
              <a class="btn py-2 rounded text-start fw-bold disabled border-0"href="#"><i class="bi bi-chevron-right small" style="-webkit-text-stroke: 2px;"></i> Reply</a>
            </div>
          </div>
        </div>
      </nav>
      <?php if ($comment_id !== null && $comment !== false): ?>
        <div class="modal-dialog my-2" role="document">
          <div class="modal-content card border-0 shadow mb-1 position-relative p-2 bg-body-tertiary rounded-4">
            <div class="modal-body">
              <h5 class="mb-0 fw-bold text-center">Comment Replies</h5>
              <div class="fw-bold mt-2">
                <div class="small">
                  <?php
                    if (!function_exists('getYouTubeVideoId')) {
                      function getYouTubeVideoId($urlCommentReply)
                      {
                        $videoIdReply = '';
                        $patternReply = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
                        if (preg_match($patternReply, $urlCommentReply, $matchesReply)) {
                          $videoIdReply = $matchesReply[1];
                        }
                        return $videoIdReply;
                      }
                    }

                    $commentTextReply = isset($comment['comment']) ? $comment['comment'] : '';

                    if (!empty($commentTextReply)) {
                      $paragraphsReply = explode("\n", $commentTextReply);

                      foreach ($paragraphsReply as $indexReply => $paragraphReply) {
                        $messageTextWithoutTagsReply = strip_tags($paragraphReply);
                        $patternReply = '/\bhttps?:\/\/\S+/i';

                        $formattedTextReply = preg_replace_callback($patternReply, function ($matchesReply) {
                          $urlCommentReply = htmlspecialchars($matchesReply[0]);

                          if (preg_match('/\.(png|jpg|jpeg|webp)$/i', $urlCommentReply)) {
                            return '<a href="' . $urlCommentReply . '" target="_blank"><img class="w-100 h-100 rounded-4 lazy-load" loading="lazy" data-src="' . $urlCommentReply . '" alt="Image"></a>';
                          } elseif (strpos($urlCommentReply, 'youtube.com') !== false) {
                            $videoIdReply = getYouTubeVideoId($urlCommentReply);
                            if ($videoIdReply) {
                              $thumbnailUrlReply = 'https://img.youtube.com/vi/' . $videoIdReply . '/default.jpg';
                              return '<div class="w-100 overflow-hidden position-relative ratio ratio-16x9"><iframe loading="lazy" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" class="rounded-4 position-absolute top-0 bottom-0 start-0 end-0 w-100 h-100 border-0 shadow" src="https://www.youtube.com/embed/' . $videoIdReply . '" frameborder="0" allowfullscreen></iframe></div>';
                            } else {
                              return '<a href="' . $urlCommentReply . '">' . $urlCommentReply . '</a>';
                            }
                          } else {
                            return '<a href="' . $urlCommentReply . '">' . $urlCommentReply . '</a>';
                          }
                        }, $messageTextWithoutTagsReply);
                    
                        echo "<p class='small' style=\"white-space: break-spaces; overflow: hidden;\">$formattedTextReply</p>";
                      }
                    } else {
                      echo "Sorry, no text...";
                    }
                  ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid">
      <div>
        <?php
          // Display each reply and a delete button
          while ($reply = $replies->fetchArray(SQLITE3_ASSOC)):
        ?>
          <div class="card border-0 shadow mb-1 position-relative p-2 bg-body-tertiary rounded-4">
            <div class="d-flex align-items-center mb-2 position-relative">
              <div class="position-absolute top-0 start-0 m-1">
                <img class="rounded-circle object-fit-cover" src="../../<?php echo !empty($reply['pic']) ? $reply['pic'] : "../../icon/profile.svg"; ?>" alt="Profile Picture" width="32" height="32">
                <a class="text-white text-decoration-none fw-medium" href="../../artist.php?id=<?php echo $reply['userid']; ?>"><small>@<?php echo (mb_strlen($reply['artist']) > 15) ? mb_substr($reply['artist'], 0, 15) . '...' : $reply['artist']; ?></small></a>・<small class="small fw-medium"><small><?php echo $reply['date']; ?></small></small>
              </div>
              <?php if ($_SESSION['email'] === $reply['email']): ?>
                <div class="dropdown ms-auto position-relative">
                  <button class="btn btn-sm position-absolute top-0 end-0 m-1" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end">
                    <form action="" method="get">
                      <a href="edit_reply_comment_novel.php?reply_id=<?php echo $reply['id']; ?>&novelid=<?php echo $novelid; ?>" class="dropdown-item fw-semibold">
                        <i class="bi bi-pencil-fill"></i> Edit
                      </a>
                      <input type="hidden" name="delete_reply_id" value="<?= $reply['id'] ?>">
                      <input type="hidden" name="novelid" value="<?= $novelid ?>" />
                      <button onclick="return confirm('Are you sure?')" class="dropdown-item fw-semibold" type="submit">
                        <i class="bi bi-trash-fill"></i> Delete
                      </button>
                    </form>
                  </div>
                </div>
              <?php endif; ?>
            </div>
            <div class="mt-5 container-fluid fw-medium">
              <div class="small">
                <?php
                  if (!function_exists('getYouTubeVideoId')) {
                    function getYouTubeVideoId($urlComment)
                    {
                      $videoId = '';
                      $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
                      if (preg_match($pattern, $urlComment, $matches)) {
                        $videoId = $matches[1];
                      }
                      return $videoId;
                    }
                  }

                  $commentText = isset($reply['reply']) ? $reply['reply'] : '';

                  if (!empty($commentText)) {
                    $paragraphs = explode("\n", $commentText);

                    foreach ($paragraphs as $index => $paragraph) {
                      $messageTextWithoutTags = strip_tags($paragraph);
                      $pattern = '/\bhttps?:\/\/\S+/i';

                      $formattedText = preg_replace_callback($pattern, function ($matches) {
                        $urlComment = htmlspecialchars($matches[0]);

                        if (preg_match('/\.(png|jpg|jpeg|webp)$/i', $urlComment)) {
                          return '<a href="' . $urlComment . '" target="_blank"><img class="w-100 h-100 rounded-4 lazy-load" loading="lazy" data-src="' . $urlComment . '" alt="Image"></a>';
                        } elseif (strpos($urlComment, 'youtube.com') !== false) {
                          $videoId = getYouTubeVideoId($urlComment);
                          if ($videoId) {
                            $thumbnailUrl = 'https://img.youtube.com/vi/' . $videoId . '/default.jpg';
                            return '<div class="w-100 overflow-hidden position-relative ratio ratio-16x9"><iframe loading="lazy" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" class="rounded-4 position-absolute top-0 bottom-0 start-0 end-0 w-100 h-100 border-0 shadow" src="https://www.youtube.com/embed/' . $videoId . '" frameborder="0" allowfullscreen></iframe></div>';
                          } else {
                            return '<a href="' . $urlComment . '">' . $urlComment . '</a>';
                          }
                        } else {
                          return '<a href="' . $urlComment . '">' . $urlComment . '</a>';
                        }
                      }, $messageTextWithoutTags);
                  
                      echo "<p class='small' style=\"white-space: break-spaces; overflow: hidden;\">$formattedText</p>";
                    }
                  } else {
                    echo "Sorry, no text...";
                  }
                ?>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else:
        // Display an error message if the comment ID is invalid
        echo "<p>Invalid comment ID.</p>";
      endif; ?>
    </div>
    <nav class="navbar fixed-bottom navbar-expand justify-content-center">
      <div class="container-fluid">
        <button type="button" class="w-100 btn btn-primary fw-bold rounded-3" data-bs-toggle="modal" data-bs-target="#comments">send your comment</button>
      </div>
    </nav>    
    <div class="modal fade" id="comments" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Type something else...</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div>
            <form class="form-control border-0" action="" method="POST">
              <input type="hidden" name="reply_comment_id" value="<?= $comment['id'] ?>">
              <textarea type="text" class="form-control fw-bold rounded-3 mb-2" style="height: 200px; max-height: 800px;" name="reply" id="reply" placeholder="Type something..." aria-label="Type a message..." aria-describedby="basic-addon2" 
                onkeydown="if(event.keyCode == 13) { this.style.height = (parseInt(this.style.height) + 10) + 'px'; return true; }"
                onkeyup="this.style.height = '40px'; var newHeight = (this.scrollHeight + 10 * (this.value.split(/\r?\n/).length - 1)) + 'px'; if (parseInt(newHeight) > 800) { this.style.height = '800px'; } else { this.style.height = newHeight; }" required></textarea>
              <button class="w-100 btn btn-primary rounded-3" type="submit"><i class="bi bi-send-fill"></i></button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <br><br><br>
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
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
