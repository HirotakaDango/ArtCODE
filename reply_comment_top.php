<?php
// Get the selected comment based on its ID
$comment_id = isset($_GET['comment_id']) ? $_GET['comment_id'] : null;
if ($comment_id !== null) {
  $comment = $db->prepare('SELECT * FROM comments WHERE id = ?');
  $comment->bindValue(1, $comment_id, SQLITE3_INTEGER);
  $comment = $comment->execute()->fetchArray(SQLITE3_ASSOC);

  // Get all replies for the selected comment from the reply_comments table, along with the user information
  $replies = $db->prepare('SELECT rc.*, u.artist, u.pic, u.id as userid FROM reply_comments rc JOIN users u ON rc.email = u.email WHERE rc.comment_id = ? ORDER BY rc.id DESC');
  $replies->bindValue(1, $comment_id, SQLITE3_INTEGER);
  $replies = $replies->execute();
}
?>

    <div class="container mt-2">
      <?php if ($comment_id !== null && $comment !== false): ?>
        <div class="modal-dialog my-2" role="document">
          <div class="modal-content card border-0 shadow mb-1 position-relative p-2 bg-body-tertiary rounded-4">
            <div class="modal-body">
              <h5 class="mb-0 fw-bold text-center">Reply to</h5>
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
          <?php include('reply_comment_card.php'); ?>
        <?php endwhile; ?>
      <?php else:
        // Display an error message if the comment ID is invalid
        echo "<p>Invalid comment ID.</p>";
      endif; ?>
    </div>