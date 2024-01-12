<?php
// Get all comments for the current image for the current page along with the count of replies
$stmt = $db->prepare("SELECT comments_novel.*, users.artist, users.pic, users.id as iduser, COUNT(reply_comments_novel.id) as reply_count FROM comments_novel JOIN users ON comments_novel.email = users.email LEFT JOIN reply_comments_novel ON comments_novel.id = reply_comments_novel.comment_id WHERE comments_novel.filename = :filename GROUP BY comments_novel.id ORDER BY comments_novel.id ASC LIMIT :comments_per_page OFFSET :offset");
$stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
$stmt->bindValue(':comments_per_page', $comments_per_page, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$comments = $stmt->execute();
?>

    <div class="container">
      <?php
        while ($comment = $comments->fetchArray()) :
      ?>
        <?php include('comment_card.php'); ?>
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
        <a class="btn btn-sm btn-primary fw-bold" href="?by=oldest&novelid=<?php echo $filename; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
      <?php endif; ?>

      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=oldest&novelid=<?php echo $filename; ?>&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?by=oldest&novelid=' . $filename . '&page=' . $i . '">' . $i . '</a>';
          }
        }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=oldest&novelid=<?php echo $filename; ?>&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=oldest&novelid=<?php echo $filename; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>