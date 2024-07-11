<?php
require_once('../../../auth.php');

// Connect to the SQLite database using parameterized query
$db = new SQLite3('../../../database.sqlite');

$email = $_SESSION['email'];

// Retrieve offset from POST parameters
$offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;

// Set limit for fetching images
$limit = 12; // Number of images per batch

// Prepare query to fetch images with LIMIT and OFFSET
$stmt = $db->prepare("SELECT images.*, users.artist, users.pic, users.id AS uid
  FROM images
  JOIN users ON images.email = users.email
  ORDER BY images.view_count ASC
  LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();

// Generate HTML for the fetched images
ob_start();
while ($image = $result->fetchArray()) {
?>

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
    <?php
    }
    $html = ob_get_clean();
    
    // Output the generated HTML
    echo $html;
    ?>