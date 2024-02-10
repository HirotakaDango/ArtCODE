<?php
// Pagination
$searchPage = isset($_GET['q']) ? $_GET['q'] : null;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$recordsPerPage = 20;
$offset = ($page - 1) * $recordsPerPage;

// Get the search parameter from the URL
$searchQuery = isset($_GET['q']) ? $_GET['q'] : null;

// Fetch music records with user information and filter by search query if provided
$query = "SELECT videos.*, users.id AS userid, users.pic, users.artist 
          FROM videos 
          LEFT JOIN users ON videos.email = users.email";

// If search query is provided, filter by album or title
if (!empty($searchQuery)) {
  $query .= " WHERE videos.title LIKE :searchQuery";
}

$query .= " ORDER BY videos.id DESC LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
$stmt->bindValue(':limit', $recordsPerPage, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);

// Bind search parameter if provided
if (!empty($searchQuery)) {
  $stmt->bindValue(':searchQuery', "%$searchQuery%", SQLITE3_TEXT);
}

$result = $stmt->execute();

// Fetch all rows as an associative array
$rows = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  $rows[] = $row;
}

// Calculate total pages for the logged-in user
$total = $db->querySingle("SELECT COUNT(*) FROM videos WHERE email = '$email'");
$totalPages = ceil($total / $recordsPerPage);
$prevPage = $page - 1;
$nextPage = $page + 1;
?>

    <div class="container-fluid">
      <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-1">
        <?php foreach ($rows as $row): ?>
          <?php include('video_info.php'); ?>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="container mt-3">
      <div class="pagination d-flex gap-1 justify-content-center mt-3">
        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?q=<?php echo $searchPage; ?>&by=newest&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
          <a class="btn btn-sm btn-primary fw-bold" href="?q=<?php echo $searchPage; ?>&by=newest&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?q=' . $searchPage . '&by=newest&page=' . $i . '">' . $i . '</a>';
          }
        }
        ?>

        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?q=<?php echo $searchPage; ?>&by=newest&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
          <a class="btn btn-sm btn-primary fw-bold" href="?q=<?php echo $searchPage; ?>&by=newest&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
        <?php endif; ?>
      </div>
    </div>