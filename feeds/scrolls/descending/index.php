<?php
require_once('../../../auth.php');

// Connect to the SQLite database using parameterized query
$db = new SQLite3('../../../database.sqlite');

$email = $_SESSION['email'];

// Process any favorite/unfavorite requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $image_id = $_POST['image_id'];

    if ($action === 'favorite') {
      $existing_fav = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = $image_id");

      if ($existing_fav == 0) {
        $db->exec("INSERT INTO favorites (email, image_id) VALUES ('$email', $image_id)");
        echo json_encode(['success' => true]);
        exit();
      }
    } elseif ($action === 'unfavorite') {
      $db->exec("DELETE FROM favorites WHERE email = '$email' AND image_id = $image_id");
      echo json_encode(['success' => true]);
      exit();
    }
  }
}

// Get all images from the database
$stmt = $db->prepare("
  SELECT images.*, users.artist, users.pic, users.id AS uid
  FROM images
  JOIN users ON images.email = users.email
  GROUP BY images.id
  ORDER BY images.title DESC
  LIMIT 12
");
$result = $stmt->execute();
?>

<!DOCTYPE html>
  <html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scrolls</title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="../../../icon/favicon.png">
    <link rel="stylesheet" href="../../../style.css">
    <?php include('../../../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('../../../header.php'); ?>
    <div class="container-fluid mb-5">
      <div class="row">
        <div class="col-md-4">
          <?php include('../scroll_header.php'); ?>
        </div>
        <div class="col-md-4" id="mainContent">
          <?php while ($image = $result->fetchArray()): ?>
            <div class="card border-0 link-body-emphasis shadow rounded-4 p-3 position-relative my-2">
              <div class="d-flex align-items-center mb-3 gap-2">
                <img class="rounded-circle object-fit-cover" src="/<?php echo !empty($image['pic']) ? $image['pic'] : "icon/profile.svg"; ?>" alt="Profile Picture" width="32" height="32">
                <small class="small fw-medium"><a class="link-body-emphasis text-decoration-none" href="/artist.php?id=<?php echo $image['uid']; ?>"><?php echo (mb_strlen($image['artist']) > 15) ? mb_substr($image['artist'], 0, 15) . '...' : $image['artist']; ?></a>・<?php echo (new DateTime($image['date']))->format("Y/m/d"); ?></small>
              </div>
              <h5 class="fw-bold mb-2"><?= $image['title']; ?></h5>
              <p style="word-break: break-word;">
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

                  $replyText = isset($image['imgdesc']) ? $image['imgdesc'] : '';

                  if (!empty($replyText)) {
                    // Truncate to 1000 characters
                    $truncatedText = mb_strimwidth($replyText, 0, 1000, '...');

                    $paragraphs = explode("\n", $truncatedText);

                    foreach ($paragraphs as $index => $paragraph) {
                      $textWithoutTags = strip_tags($paragraph);
                      $pattern = '/\bhttps?:\/\/\S+/i';

                      $formattedText = preg_replace_callback($pattern, function ($matches) {
                        $url = htmlspecialchars($matches[0]);

                        // Check if the URL ends with .png, .jpg, .jpeg, or .webp
                        if (preg_match('/\.(png|jpg|jpeg|webp)$/i', $url)) {
                          return '<a href="' . $url . '" target="_blank"><img class="img-fluid rounded-4" loading="lazy" src="' . $url . '" alt="Image"></a>';
                        } elseif (strpos($url, 'youtube.com') !== false) {
                          // If the URL is from YouTube, embed it as an iframe with a very low-resolution thumbnail
                          $videoId = getYouTubeVideoId($url);
                          if ($videoId) {
                            return '<div class="w-100 overflow-hidden position-relative ratio ratio-16x9"><iframe loading="lazy" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" class="rounded-4 position-absolute top-0 bottom-0 start-0 end-0 w-100 h-100 border-0 shadow" src="https://www.youtube.com/embed/' . $videoId . '" frameborder="0" allowfullscreen></iframe></div>';
                          } else {
                            return '<a href="' . $url . '">' . $url . '</a>';
                          }
                        } else {
                          return '<a href="' . $url . '">' . $url . '</a>';
                        }
                      }, $textWithoutTags);
                  
                      echo "<p style=\"white-space: break-spaces; overflow: hidden;\">$formattedText</p>";
                    }

                    // Add "Read more" button outside the loop
                    if (mb_strlen($replyText) > 1000) {
                      echo '<p><a class="link-body-emphasis text-decoration-none" href="/image.php?artworkid=' . $image['id'] . '">Read more</a></p>';
                    }
                  } else {
                    echo "Sorry, no text...";
                  }
                ?>
              </p>
              <a class="rounded mt-2" data-bs-toggle="modal" data-bs-target="#viewPost<?php echo $image['id']; ?>">
                <img class="rounded w-100" src="/thumbnails/<?= $image['filename']; ?>" alt="<?= $image['title']; ?>">
              </a>
              <!-- Favorite/unfavorite button -->
              <div class="d-flex justify-content-center w-100 mt-3">
                <div class="row g-5">
                  <div class="col-3 d-flex justify-content-between">
                    <button type="button" class="btn border-0" data-bs-toggle="modal" data-bs-target="#shareImage<?php echo $image['id']; ?>"><i class="bi bi-share-fill"></i></button>
                  </div>
                  <div class="col-3 d-flex justify-content-between">
                    <?php
                      $is_favorited = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = {$image['id']}");
                      if ($is_favorited) {
                    ?>
                      <form class="favoriteForm">
                        <input type="hidden" name="image_id" value="<?= $image['id']; ?>">
                        <input type="hidden" name="action" value="unfavorite">
                        <button type="button" class="btn border-0 unfavoriteBtn"><i class="bi bi-heart-fill"></i></button>
                      </form>
                    <?php } else { ?>
                      <form class="favoriteForm">
                        <input type="hidden" name="image_id" value="<?= $image['id']; ?>">
                        <input type="hidden" name="action" value="favorite">
                        <button type="button" class="btn border-0 favoriteBtn"><i class="bi bi-heart"></i></button>
                      </form>
                    <?php } ?>
                  </div>
                  <div class="col-3 d-flex justify-content-between">
                    <button class="btn d-flex gap-2 border-0"><i class="bi bi-bar-chart-line-fill"></i> <?= $image['view_count']; ?></button>
                  </div>
                  <div class="col-3 d-flex justify-content-between">
                    <a href="/image.php?artworkid=<?= $image['id']; ?>" class="btn border-0"><i class="bi bi-box-arrow-up-right"></i></a>
                  </div>
                </div>
                <?php include('../view_post.php'); ?>
                <?php include('../share_post.php'); ?>
              </div>
            </div>
          <?php endwhile; ?>
          <div id="load-more-container"></div>
          <div class="d-none d-md-block col-4">

          </div>
          <div id="load-more-btn-container">
            <button id="load-more-btn" class="btn btn-primary fw-bold w-100 rounded-4">Load More</button>
          </div>
        </div>
      </div>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Function to handle favorite/unfavorite action
        function handleFavoriteAction(button) {
          var formData = new FormData(button.closest('.favoriteForm'));
          var xhr = new XMLHttpRequest();
          xhr.open('POST', 'favorite.php', true);
          xhr.onload = function() {
            if (xhr.status === 200) {
              var response = JSON.parse(xhr.responseText);
              if (response.success) {
                // Update button appearance based on action
                var form = button.closest('.favoriteForm');
                var action = form.querySelector('input[name="action"]').value;
                if (action === 'favorite') {
                  form.querySelector('.btn').innerHTML = '<i class="bi bi-heart-fill"></i>';
                  form.querySelector('input[name="action"]').value = 'unfavorite';
                } else if (action === 'unfavorite') {
                  form.querySelector('.btn').innerHTML = '<i class="bi bi-heart"></i>';
                  form.querySelector('input[name="action"]').value = 'favorite';
                }
              } else {
                console.error('Failed to update favorite status.');
              }
            }
          };
          xhr.send(formData);
        }
    
        // Event delegation for favorite/unfavorite buttons
        document.getElementById('mainContent').addEventListener('click', function(event) {
          var target = event.target;
          if (target.matches('.favoriteBtn') || target.matches('.unfavoriteBtn')) {
            handleFavoriteAction(target);
          }
        });
    
        // Infinite scroll functionality
        var loading = false; // Flag to prevent multiple simultaneous requests
        var loadMoreBtn = document.getElementById('load-more-btn');
        var loadMoreContainer = document.getElementById('load-more-container');
        var offset = 12; // Initial offset
    
        function loadMoreImages() {
          if (loading) return;
          loading = true;
    
          var xhr = new XMLHttpRequest();
          xhr.open('POST', 'scroll.php', true);
          xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
          xhr.onload = function() {
            loading = false;
            if (xhr.status === 200) {
              loadMoreContainer.innerHTML += xhr.responseText;
              offset += 12; // Increment offset for next batch
    
              // Reattach event listeners for new favorite/unfavorite buttons
              updateEventListeners();
            }
          };
          xhr.send('offset=' + offset);
        }
    
        // Throttle scroll event to prevent excessive AJAX requests
        var throttleTimeout;
        function throttleScroll() {
          if (!throttleTimeout) {
            throttleTimeout = setTimeout(function() {
              throttleTimeout = null;
              var windowHeight = window.innerHeight;
              var bodyHeight = document.body.offsetHeight;
              var scrollY = window.scrollY || window.pageYOffset;
              if (windowHeight + scrollY >= bodyHeight) {
                loadMoreImages();
              }
            }, 200); // Adjust throttle time as needed
          }
        }
    
        // Function to update event listeners for favorite/unfavorite buttons
        function updateEventListeners() {
          document.querySelectorAll('.favoriteBtn, .unfavoriteBtn').forEach(function(button) {
            button.addEventListener('click', function() {
              handleFavoriteAction(this);
            });
          });
        }
    
        // Initial load more button click handler
        loadMoreBtn.addEventListener('click', loadMoreImages);
    
        // Attach event listeners for existing favorite/unfavorite buttons
        updateEventListeners();
      });
    </script>
    <?php include('../../../bootstrapjs.php'); ?>
  </body>
</html>