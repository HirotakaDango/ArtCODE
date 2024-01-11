<?php
// Get all comments for the current image for the current page
$stmt = $db->prepare("SELECT comments_minutes.*, users.artist, users.pic, users.id AS iduser, COUNT(reply_comments_minutes.id) AS reply_count FROM comments_minutes JOIN users ON comments_minutes.email = users.email LEFT JOIN reply_comments_minutes ON comments_minutes.id = reply_comments_minutes.comment_id WHERE comments_minutes.minute_id = :minute_id GROUP BY comments_minutes.id ORDER BY comments_minutes.id ASC LIMIT :comments_per_page OFFSET :offset");
$stmt->bindValue(':minute_id', $minute_id, SQLITE3_TEXT);
$stmt->bindValue(':comments_per_page', $comments_per_page, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$comments = $stmt->execute();
?>

    <div class="container">
      <?php
        while ($comment = $comments->fetchArray()) :
      ?>
        <div class="card border-0 shadow mb-1 position-relative p-2 bg-body-tertiary rounded-4">
          <div class="d-flex align-items-center mb-2 position-relative">
            <div class="position-absolute top-0 start-0 m-1">
              <img class="rounded-circle object-fit-cover" src="../../<?php echo !empty($comment['pic']) ? $comment['pic'] : "../../icon/profile.svg"; ?>" alt="Profile Picture" width="32" height="32">
              <a class="text-white text-decoration-none fw-medium" href="../../artist.php?id=<?php echo $comment['iduser'];?>" target="_blank"><small>@<?php echo (mb_strlen($comment['artist']) > 15) ? mb_substr($comment['artist'], 0, 15) . '...' : $comment['artist']; ?></small></a>・<small class="small fw-medium"><small><?php echo $comment['created_at']; ?></small></small>
            </div>
            <?php if ($comment['email'] == $_SESSION['email']) : ?>
              <div class="dropdown ms-auto position-relative">
                <button class="btn btn-sm position-absolute top-0 end-0 m-1" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                  <form action="" method="POST">
                    <a href="edit_comment.php?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'newest'; ?>&commentid=<?php echo $comment['id']; ?>&page=<?php echo isset($_GET['page']) ? intval($_GET['page']) : 1; ?>" class="dropdown-item fw-semibold">
                      <i class="bi bi-pencil-fill me-2"></i> Edit
                    </a>
                    <input type="hidden" name="id" value="<?php echo $minute_id; ?>">
                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                    <button type="submit" name="action" onclick="return confirm('Are you sure?')" value="delete" class="dropdown-item fw-semibold">
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
          <div class="mx-2 me-auto">
            <h6 class="fw-medium small"><small><?php echo $comment['reply_count']; ?> Replies</small></h6>
          </div>
          <div class="m-2 ms-auto">
            <?php
              $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
              $by = isset($_GET['by']) ? $_GET['by'] : 'newest';
              $sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
              $comment_id = isset($comment['id']) ? $comment['id'] : '';

              $url = "reply_comment_minute.php?sort=$sort&by=$by&minuteid=$minute_id&comment_id=$comment_id&page=$page";
            ?>
            <a class="btn btn-sm fw-semibold" href="<?php echo $url; ?>">
              <i class="bi bi-reply-fill"></i> Reply
            </a>
          </div>
        </div>
      <?php
        endwhile;
      ?>
    </div>
    <?php
      $totalPages = ceil($total_comments / $comments_per_page);
      $prevPage = $page - 1;
      $nextPage = $page + 1;
    ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=oldest&minute_id=<?php echo $minute_id; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
      <?php endif; ?>

      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=oldest&minute_id=<?php echo $minute_id; ?>&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
      <?php endif; ?>

      <?php
        // Calculate the range of page numbers to display
        $startPage = max($page - 2, 1);
        $endPage = min($page + 2, $totalPages);

        // Display page numbers within the range
        for ($i = $startPage; $i <= $endPage; $i++) {
          if ($i === $page) {
            echo '<span class="btn btn-sm btn-primary active fw-bold">' . $i . '</span>';
          } else {
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?by=oldest&minute_id=' . $minute_id . '&page=' . $i . '">' . $i . '</a>';
          }
        }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=oldest&minute_id=<?php echo $minute_id; ?>&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=oldest&minute_id=<?php echo $minute_id; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>