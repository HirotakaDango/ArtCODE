<?php
// Get the category from URL parameters, default to 'A'
$category = isset($_GET['category']) ? strtoupper($_GET['category']) : 'A';
$by = isset($_GET['by']) ? $_GET['by'] : 'ascending';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 20; // Limit to 20 parodies per page
$offset = ($page - 1) * $limit;

// Retrieve the count of images for each parody matching the search condition
$query = "SELECT parodies, COUNT(*) as count FROM images";
if (!empty($searchCondition)) {
  $query .= " WHERE $searchCondition";
}
$query .= " GROUP BY parodies";

$result = $db->query($query);

// Store the parody counts as an associative array
$parodyCounts = [];
while ($row = $result->fetchArray()) {
  $parodyList = explode(',', $row['parodies']);
  foreach ($parodyList as $parody) {
    $trimmedparody = trim($parody);
    if (!isset($parodyCounts[$trimmedparody])) {
      $parodyCounts[$trimmedparody] = 0;
    }
    $parodyCounts[$trimmedparody] += $row['count'];
  }
}

// Group the parodies by the first character and sort them
$groupedparodies = [];
foreach ($parodyCounts as $parody => $count) {
  $firstChar = strtoupper(mb_substr($parody, 0, 1));
  $groupedparodies[$firstChar][$parody] = $count;
}

ksort($groupedparodies); // Sort groups by first character

// Get parodies for the current category and sort them by view_count of images
$currentparodies = isset($groupedparodies[$category]) ? $groupedparodies[$category] : [];

// Fetch view_count for each parody
$viewCounts = [];
foreach ($currentparodies as $parody => $count) {
  $stmt = $db->prepare("SELECT SUM(view_count) as total_views FROM images WHERE parodies LIKE ?");
  $stmt->bindValue(1, '%' . $parody . '%');
  $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
  $viewCounts[$parody] = $result['total_views'] ?? 0;
}

// Sort parodies by view_count in descending order
array_multisort($viewCounts, SORT_DESC, $currentparodies);

$totalparodies = count($currentparodies);
$totalPages = ceil($totalparodies / $limit);
$currentparodies = array_slice($currentparodies, $offset, $limit, true);
?>

    <div class="container-fluid mt-2">
      <div class="container-fluid">
        <div class="row justify-content-center">
          <?php foreach ($groupedparodies as $group => $parodies): ?>
            <div class="col-4 col-md-2 col-sm-5 px-0">
              <a class="btn btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> border-0 fw-medium d-flex flex-column align-items-center" href="?by=<?php echo $by; ?>&category=<?php echo $group; ?>">
                <h6 class="fw-medium">Category</h6>
                <h6 class="fw-bold"><?php echo $group; ?></h6>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    
      <?php include('parody_card.php'); ?>
    
      <!-- Pagination -->
      <div class="pagination d-flex gap-1 justify-content-center mt-3">
        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo $by; ?>&category=<?php echo $category; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
        <?php endif; ?>
    
        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo $by; ?>&category=<?php echo $category; ?>&page=<?php echo $page - 1; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?by=' . $by . '&category=' . $category . '&page=' . $i . '">' . $i . '</a>';
          }
        }
        ?>
    
        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo $by; ?>&category=<?php echo $category; ?>&page=<?php echo $page + 1; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
        <?php endif; ?>
    
        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo $by; ?>&category=<?php echo $category; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
        <?php endif; ?>
      </div>
    </div>
    <div class="mt-5"></div>