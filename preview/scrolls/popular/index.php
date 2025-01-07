<?php
// Connect to the SQLite database using parameterized query
$db = new SQLite3('../../../database.sqlite');

// Get all images from the database
$stmt = $db->prepare("
  SELECT images.*, 
       COUNT(favorites.id) AS favorite_count, 
       users.artist, 
       users.pic, 
       users.id AS uid
FROM images
LEFT JOIN favorites ON images.id = favorites.image_id
LEFT JOIN users ON images.email = users.email
GROUP BY images.id
ORDER BY favorite_count DESC
LIMIT 12
");
$result = $stmt->execute();
?>

<!DOCTYPE html>
  <html lang="en">
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
    <?php include('../../header_preview.php'); ?>
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
                  <div class="col-4 d-flex justify-content-between">
                    <button type="button" class="btn border-0" data-bs-toggle="modal" data-bs-target="#shareImage<?php echo $image['id']; ?>"><i class="bi bi-share-fill"></i></button>
                  </div>
                  <div class="col-4 d-flex justify-content-between">
                    <button class="btn d-flex gap-2 border-0"><i class="bi bi-bar-chart-line-fill"></i> <?= $image['view_count']; ?></button>
                  </div>
                  <div class="col-4 d-flex justify-content-between">
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
            } else {
              // Handle errors
              console.error('Failed to load more images. Status: ' + xhr.status);
            }
          };
          xhr.send('offset=' + offset);
        }
    
        // Load more images when button is clicked
        loadMoreBtn.addEventListener('click', loadMoreImages);
    
        // Optional: Load more images when the user scrolls near the bottom
        window.addEventListener('scroll', function() {
          if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight) {
            loadMoreImages();
          }
        });
      });
    </script>
    <?php include('../../../bootstrapjs.php'); ?>
  </body>
</html>