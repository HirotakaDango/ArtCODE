<?php
// Get the selected comment based on its ID
$comment_id = isset($_GET['comment_id']) ? $_GET['comment_id'] : null;
if ($comment_id !== null) {
  $comment = $db->prepare('SELECT * FROM comments_minutes WHERE id = ?');
  $comment->bindValue(1, $comment_id, SQLITE3_INTEGER);
  $comment = $comment->execute()->fetchArray(SQLITE3_ASSOC);

  // Get all replies for the selected comment from the reply_comments_minutes table, along with the user information
  $replies = $db->prepare('SELECT rc.*, u.artist, u.pic, u.id as userid FROM reply_comments_minutes rc JOIN users u ON rc.email = u.email WHERE rc.comment_id = ? ORDER BY rc.id DESC');
  $replies->bindValue(1, $comment_id, SQLITE3_INTEGER);
  $replies = $replies->execute();
}
?>

    <div class="container">
      <?php if ($comment_id !== null && $comment !== false): ?>
        <div class="modal-dialog my-2" role="document">
          <div class="modal-content card border-0 shadow mb-1 position-relative p-2 bg-body-tertiary rounded-4">
            <div class="modal-body">
              <h5 class="mb-0 fw-bold text-center">Comment Replies</h5>
              <div class="mt-5 container-fluid fw-medium">
                <div>
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
                    
                        echo "<p style=\"white-space: break-spaces; overflow: hidden;\">$formattedTextReply</p>";
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
    <div class="container">
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
                      <a href="edit_reply_comment_minute.php?reply_id=<?php echo $reply['id']; ?>&minuteid=<?php echo $minuteid; ?>" class="dropdown-item fw-semibold">
                        <i class="bi bi-pencil-fill"></i> Edit
                      </a>
                      <input type="hidden" name="delete_reply_id" value="<?= $reply['id'] ?>">
                      <input type="hidden" name="minuteid" value="<?= $minuteid ?>" />
                      <button onclick="return confirm('Are you sure?')" class="dropdown-item fw-semibold " type="submit">
                        <i class="bi bi-trash-fill"></i> Delete
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
                  
                      echo "<p style=\"white-space: break-spaces; overflow: hidden;\">$formattedText</p>";
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