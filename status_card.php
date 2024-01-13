        <div class="card border-0 shadow mb-1 position-relative p-2 bg-body-tertiary rounded-4">
          <div class="d-flex align-items-center mb-2 position-relative">
            <div class="position-absolute top-0 start-0 m-1">
              <img class="rounded-circle object-fit-cover" src="<?php echo !empty($message['pic']) ? $message['pic'] : "icon/profile.svg"; ?>" alt="Profile Picture" width="32" height="32">
              <a class="text-dark text-decoration-none fw-medium link-body-emphasis" href="artist.php?id=<?php echo $message['userid'];?>" target="_blank"><small>@<?php echo (mb_strlen($message['artist']) > 15) ? mb_substr($message['artist'], 0, 15) . '...' : $message['artist']; ?></small></a>・<small class="small fw-medium"><small><?php echo date('Y/m/d', strtotime($message['date'])); ?></small></small>
            </div>
          </div>
          <div class="mt-5 container-fluid">
            <div class="fw-medium">
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

                $commentText = isset($message['message']) ? $message['message'] : '';

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
                
                    echo "<p style=\"white-space: break-spaces; overflow: hidden;\">$formattedText</p>";
                  }
                } else {
                  echo "Sorry, no text...";
                }
              ?>
            </div>
            <?php if ($message['email'] == $email): ?>
              <form method="post" action="">
                <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                <button type="submit" name="delete" class="btn btn-outline-dark border-0 btn-sm position-absolute top-0 end-0 m-2" onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i></button>
              </form>
            <?php endif; ?>
            <div class="mt-5">
              <?php
                $stmt = $db->prepare("SELECT COUNT(*) FROM favorites_status WHERE email = :email AND status_id = :status_id");
                $stmt->bindParam(':email', $_SESSION['email']);
                $stmt->bindParam(':status_id', $message['id']);
                $stmt->execute();
                $is_favorited = $stmt->fetchColumn();

                if ($is_favorited) {
              ?>
                <form method="POST">
                  <input type="hidden" name="status_id" value="<?php echo $message['id']; ?>">
                  <button type="submit" class="btn border-0 link-body-emphasis fw-medium position-absolute bottom-0 start-0 m-1" name="unfavorite">
                    <i class="bi bi-heart-fill text-danger"></i> <?php echo $message['like_count']; ?>
                  </button>
                </form>
              <?php } else { ?>
                <form method="POST">
                  <input type="hidden" name="status_id" value="<?php echo $message['id']; ?>">
                  <button type="submit" class="btn border-0 link-body-emphasis fw-medium position-absolute bottom-0 start-0 m-1" name="favorite">
                    <i class="bi bi-heart"></i> <?php echo $message['like_count']; ?>
                  </button>
                </form>
              <?php } ?>
            </div>
          </div>
        </div>