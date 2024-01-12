<?php
// Get all forum items for the current page with reply counts
$stmt = $db->prepare("SELECT forum.*, users.artist, users.pic, users.id AS iduser, COUNT(reply_forum.id) AS reply_count FROM forum JOIN users ON forum.email = users.email LEFT JOIN reply_forum ON forum.id = reply_forum.comment_id GROUP BY forum.id, users.artist, users.pic, users.id ORDER BY forum.id DESC LIMIT :items_per_page OFFSET :offset");
$stmt->bindValue(':items_per_page', $items_per_page, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$forum = $stmt->execute();
?>

    <div class="container">
      <?php
        while ($comment = $forum->fetchArray()) :
      ?>
        <?php include('forum_card.php'); ?>
      <?php
        endwhile;
      ?>
      <?php
        $totalPages = ceil($total_items / $items_per_page);
        $prevPage = $page - 1;
        $nextPage = $page + 1;
      ?>
      <div class="pagination d-flex gap-1 justify-content-center mt-3">
        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?by=newest&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
        <?php endif; ?>

        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?by=newest&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
              echo '<a class="btn btn-sm btn-primary fw-bold" href="?by=newest&page=' . $i . '">' . $i . '</a>';
            }
          }
        ?>

        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?by=newest&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?by=newest&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
        <?php endif; ?>
      </div>
    </div>