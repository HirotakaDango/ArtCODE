          <div class="card border-0 shadow mb-1 position-relative p-2 bg-body-tertiary rounded-4">
            <div class="d-flex align-items-center mb-2 position-relative">
              <div class="d-flex align-items-center gap-2 position-absolute top-0 start-0 m-1">
                <img class="rounded-circle object-fit-cover" src="<?php echo !empty($reply['pic']) ? $reply['pic'] : "icon/profile.svg"; ?>" alt="Profile Picture" width="32" height="32">
                <div class="dropdown">
                  <a class="text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> text-decoration-none fw-medium link-body-emphasis" href="#" data-bs-toggle="dropdown" aria-expanded="false"><small>@<?php echo (mb_strlen($reply['artist']) > 15) ? mb_substr($reply['artist'], 0, 15) . '...' : $reply['artist']; ?></small></a>・<small class="small fw-medium"><small><?php echo $reply['date']; ?></small></small>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item fw-medium" href="artist.php?id=<?php echo $reply['userid'];?>" target="_blank">view user's profile</a></li>
                    <li><a class="dropdown-item fw-medium" href="forum_user.php?id=<?php echo $reply['userid'];?>" target="_blank">view user's forum profile</a></li>
                  </ul>
                </div>
              </div>
              <?php if ($_SESSION['email'] === $reply['email']): ?>
                <div class="dropdown ms-auto position-relative">
                  <button class="btn btn-sm position-absolute top-0 end-0 m-1" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end rounded-4 shadow border-0">
                    <form action="" method="get">
                      <a href="edit_reply_forum.php?sort=<?php echo $replySort; ?>&by=<?php echo $sortUrl; ?>&reply_id=<?php echo $reply['id']; ?>&comment_id=<?php echo $commentId; ?>&page=<?php echo $pageUrl; ?>" class="dropdown-item fw-medium">
                        <i class="bi bi-pencil-fill"></i> Edit
                      </a>
                      <input type="hidden" name="delete_reply_id" value="<?= $reply['id'] ?>">
                      <input type="hidden" name="comment_id" value="<?= $commentId ?>">
                      <input type="hidden" name="page" value="<?= $pageUrl ?>" />
                      <input type="hidden" name="by" value="<?= $sortUrl ?>" />
                      <input type="hidden" name="sort" value="<?= $replySort ?>" />
                      <button onclick="return confirm('Are you sure?')" class="dropdown-item fw-medium" value="delete_reply_id" type="submit">
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
                  
                      echo "<p class='small' style=\"white-space: break-spaces; overflow: hidden;\">$formattedText</p>";
                    }
                  } else {
                    echo "Sorry, no text...";
                  }
                ?>
              </div>
            </div>
          </div>