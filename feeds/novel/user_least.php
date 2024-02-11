<?php
// Pagination variables
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get total number of records for the user
$totalStmt = $db->prepare("SELECT COUNT(*) FROM novel WHERE email = :email");
$totalStmt->bindValue(':email', $user['email']);
$totalStmt->execute();
$total = $totalStmt->fetchColumn();

// Get all of the images from the database for the user with pagination
try {
  $result = $db->prepare("SELECT novel.*, users.id AS user_id, users.email, users.artist FROM novel JOIN users ON novel.email = users.email WHERE novel.email = :email ORDER BY novel.view_count ASC LIMIT :limit OFFSET :offset");
  $result->bindValue(':email', $user['email']);
  $result->bindValue(':limit', $limit, PDO::PARAM_INT);
  $result->bindValue(':offset', $offset, PDO::PARAM_INT);
  $result->execute();
} catch (PDOException $e) {
  die("Error retrieving data from novel table: " . $e->getMessage());
}
?>

    <div class="container-fluid">
      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 row-cols-xl-8 g-1">
        <?php while ($image = $result->fetch(PDO::FETCH_ASSOC)): ?>
          <?php include('novel_info.php'); ?>
        <?php endwhile; ?>
      </div>
    </div>
    <?php
      $totalPages = ceil($total / $limit);
      $prevPage = $page - 1;
      $nextPage = $page + 1;
    ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=least&id=<?php echo $userID; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=least&id=<?php echo $userID; ?>&page=<?php echo $page - 1; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?by=least&id=' . $userID . '&page=' . $i . '">' . $i . '</a>';
          }
        }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=least&id=<?php echo $userID; ?>&page=<?php echo $page + 1; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=least&id=<?php echo $userID; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>