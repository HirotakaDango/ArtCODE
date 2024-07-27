        <div class="card border-0 shadow mb-1 position-relative p-2 bg-body-tertiary rounded-4">
          <div class="d-flex align-items-center mb-2 position-relative">
            <div class="position-absolute top-0 start-0 m-1">
              <img class="rounded-circle object-fit-cover" src="<?php echo !empty($comment['pic']) ? $comment['pic'] : "icon/profile.svg"; ?>" alt="Profile Picture" width="32" height="32">
              <a class="text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> text-decoration-none fw-medium link-body-emphasis" href="artist.php?id=<?php echo $comment['iduser'];?>" target="_blank"><small>@<?php echo (mb_strlen($comment['artist']) > 15) ? mb_substr($comment['artist'], 0, 15) . '...' : $comment['artist']; ?></small></a>・<small class="small fw-medium"><small><?php echo $comment['created_at']; ?></small></small>
            </div>
            <?php if ($comment['email'] == $_SESSION['email']) : ?>
              <div class="dropdown ms-auto position-relative">
                <button class="btn btn-sm position-absolute top-0 end-0 m-1" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end rounded-4 shadow border-0">
                  <form action="" method="POST">
                    <a href="edit_comment_preview.php?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'newest'; ?>&commentid=<?php echo $comment['id']; ?>&page=<?php echo isset($_GET['page']) ? intval($_GET['page']) : 1; ?>" class="dropdown-item fw-medium">
                      <i class="bi bi-pencil-fill me-2"></i> Edit
                    </a>
                    <input type="hidden" name="imageid" value="<?php echo $imageId; ?>">
                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                    <button type="submit" name="action" onclick="return confirm('Are you sure?')" value="delete" class="dropdown-item fw-medium">
                      <i class="bi bi-trash-fill me-2"></i> Delete
                    </button>
                  </form>
                </div>
              </div>
            <?php endif; ?>
          </div>
          <div class="mt-5 container-fluid fw-medium">
            <div>
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

                $commentText = isset($comment['comment']) ? $comment['comment'] : '';

                if (!empty($commentText)) {
                  $paragraphs = explode("\n", $commentText);

                  foreach ($paragraphs as $index => $paragraph) {
                    $messageTextWithoutTags = strip_tags($paragraph);
                    $pattern = '/\bhttps?:\/\/\S+/i';

                    $formattedText = preg_replace_callback($pattern, function ($matches) {
                      $urlComment = htmlspecialchars($matches[0]);

                      if (preg_match('/\.(png|jpg|jpeg|webp|gif|svg|heic|tiff|bmp|raw|heif|icns|webp2)$/i', $urlComment)) {
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
          </div>
          <div class="mx-2 me-auto">
            <h6 class="fw-medium small"><small><?php echo $comment['reply_count']; ?> Replies</small></h6>
          </div>
          <div class="m-2 ms-auto">
            <?php
              $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
              $by = isset($_GET['by']) ? $_GET['by'] : 'newest';
              $sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
              $comment_id = isset($comment['id']) ? $comment['id'] : '';

              $url = "reply_comment_preview.php?sort=$sort&by=$by&imageid=$imageId&comment_id=$comment_id&page=$page";
            ?>
            <a class="btn btn-sm fw-semibold link-body-emphasis" href="<?php echo $url; ?>">
              <i class="bi bi-reply-fill"></i> Reply
            </a>
          </div>
        </div>