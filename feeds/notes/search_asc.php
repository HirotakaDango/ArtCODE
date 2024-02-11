<?php
// Prepare the query to get the user's numpage
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, PDO::PARAM_STR);
$resultNum = $queryNum->execute();
$user = $queryNum->fetch(PDO::FETCH_ASSOC);

$numpage = $user['numpage'];

// Set the limit of images per page
$limit = empty($numpage) ? 50 : $numpage;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Get the total number of images for the specified tag
$count_query = "SELECT COUNT(*) FROM posts WHERE email = :email ";
$params = [':email' => $email];
if (!empty($searchTerm)) {
  $count_query .= " AND (title LIKE :searchTerm OR tags LIKE :searchTag)";
  $params[':searchTerm'] = "%$searchTerm%";
  $params[':searchTag'] = "%,$searchTerm,%";
}
$stmtCount = $db->prepare($count_query);
$stmtCount->execute($params);
$total = $stmtCount->fetchColumn();

// Query the database for the posts on the current page
$query = 'SELECT * FROM posts WHERE email = :email ';
if (!empty($searchTerm)) {
  $query .= " AND (title LIKE :searchTerm OR tags LIKE :searchTag)";
}
$query .= ' ORDER BY id ASC LIMIT :offset, :limit';

$stmt = $db->prepare($query);
$stmt->bindValue(':email', $email);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
if (!empty($searchTerm)) {
  $stmt->bindValue(':searchTerm', "%$searchTerm%");
  $stmt->bindValue(':searchTag', "%,$searchTerm,%");
}
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

    <?php include('note_card.php'); ?>
    <?php
      $totalPages = ceil($total / $limit);
      $prevPage = $page - 1;
      $nextPage = $page + 1;
    ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=oldest&q=<?php echo $searchTerm; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
      <?php endif; ?>

      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=oldest&q=<?php echo $searchTerm; ?>&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?by=oldest&q=' . $searchTerm . '&page=' . $i . '">' . $i . '</a>';
          }
        }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=oldest&q=<?php echo $searchTerm; ?>&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=oldest&q=<?php echo $searchTerm; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>