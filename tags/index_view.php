<?php
// Get the category from URL parameters, default to 'A'
$category = isset($_GET['category']) ? strtoupper($_GET['category']) : 'A';
$by = isset($_GET['by']) ? $_GET['by'] : 'ascending';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 20; // Limit to 20 tags per page
$offset = ($page - 1) * $limit;

// Retrieve the count of images for each tag matching the search condition
$query = "SELECT tags, COUNT(*) as count FROM images";
if (!empty($searchCondition)) {
  $query .= " WHERE $searchCondition";
}
$query .= " GROUP BY tags";

$result = $db->query($query);

// Store the tag counts as an associative array
$tagCounts = [];
while ($row = $result->fetchArray()) {
  $tagList = explode(',', $row['tags']);
  foreach ($tagList as $tag) {
    $trimmedTag = trim($tag);
    if (!isset($tagCounts[$trimmedTag])) {
      $tagCounts[$trimmedTag] = 0;
    }
    $tagCounts[$trimmedTag] += $row['count'];
  }
}

// Group the tags by the first character and sort them
$groupedTags = [];
foreach ($tagCounts as $tag => $count) {
  $firstChar = strtoupper(mb_substr($tag, 0, 1));
  $groupedTags[$firstChar][$tag] = $count;
}

ksort($groupedTags); // Sort groups by first character

// Get tags for the current category and sort them by view_count of images
$currentTags = isset($groupedTags[$category]) ? $groupedTags[$category] : [];

// Fetch view_count for each tag
$viewCounts = [];
foreach ($currentTags as $tag => $count) {
  $stmt = $db->prepare("SELECT SUM(view_count) as total_views FROM images WHERE tags LIKE ?");
  $stmt->bindValue(1, '%' . $tag . '%');
  $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
  $viewCounts[$tag] = $result['total_views'] ?? 0;
}

// Sort tags by view_count in descending order
array_multisort($viewCounts, SORT_DESC, $currentTags);

$totalTags = count($currentTags);
$totalPages = ceil($totalTags / $limit);
$currentTags = array_slice($currentTags, $offset, $limit, true);
?>

    <div class="container-fluid mt-2">
      <div class="container-fluid">
        <div class="row justify-content-center">
          <?php foreach ($groupedTags as $group => $tags): ?>
            <div class="col-4 col-md-2 col-sm-5 px-0">
              <a class="btn btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> border-0 fw-medium d-flex flex-column align-items-center" href="?by=<?php echo $by; ?>&category=<?php echo $group; ?>">
                <h6 class="fw-medium">Category</h6>
                <h6 class="fw-bold"><?php echo $group; ?></h6>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    
      <?php include('tag_card.php'); ?>
    
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