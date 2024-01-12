<?php
// Get all comments for the current image for the current page
$stmt = $db->prepare("SELECT comments.*, users.artist, users.pic, users.id as iduser, COUNT(reply_comments.id) as reply_count FROM comments JOIN users ON comments.email = users.email LEFT JOIN reply_comments ON comments.id = reply_comments.comment_id WHERE comments.filename=:filename GROUP BY comments.id ORDER BY comments.id DESC LIMIT :comments_per_page OFFSET :offset");
$stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
$stmt->bindValue(':comments_per_page', $comments_per_page, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$comments = $stmt->execute();
?>

    <div class="container-fluid">
      <?php
        while ($comment = $comments->fetchArray()) :
      ?>
        <?php include('comment_preview_card.php'); ?>
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
        <a class="btn btn-sm btn-primary fw-bold" href="?by=newest&imageid=<?php echo $filename; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
      <?php endif; ?>

      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=newest&imageid=<?php echo $filename; ?>&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?by=newest&imageid=' . $filename . '&page=' . $i . '">' . $i . '</a>';
          }
        }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=newest&imageid=<?php echo $filename; ?>&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=newest&imageid=<?php echo $filename; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>