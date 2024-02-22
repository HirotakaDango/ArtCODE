<?php
// Build the query to fetch forum entries based on category with user information
$query = "SELECT forum.*, users.artist, users.pic, users.id AS iduser, COUNT(reply_forum.id) AS reply_count 
          FROM forum 
          JOIN users ON forum.email = users.email 
          LEFT JOIN reply_forum ON forum.id = reply_forum.comment_id";
 if (!empty($category)) {
  $query .= " WHERE forum.category = :category";
}

$query .= " GROUP BY forum.id, users.artist, users.pic, users.id 
            ORDER BY forum.id ASC 
            LIMIT :items_per_page 
            OFFSET :offset";
$stmt = $db->prepare($query);

if (!empty($category)) {
  $stmt->bindValue(':category', $category, SQLITE3_TEXT);
}

$stmt->bindValue(':items_per_page', $items_per_page, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);

// Execute the query to fetch forum entries
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
          <a class="btn btn-sm btn-primary fw-bold" href="?by=oldest&q=<?php echo urlencode($category); ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
        <?php endif; ?>

        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?by=oldest&q=<?php echo urlencode($category); ?>&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
              echo '<a class="btn btn-sm btn-primary fw-bold" href="?by=oldest&q=' . urlencode($category) . '&page=' . $i . '">' . $i . '</a>';
            }
          }
        ?>

        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?by=oldest&q=<?php echo urlencode($category); ?>&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?by=oldest&q=<?php echo urlencode($category); ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
        <?php endif; ?>
      </div>
    </div>