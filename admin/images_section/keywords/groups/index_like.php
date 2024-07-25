<?php
// Get the category from URL parameters, default to 'A'
$category = isset($_GET['category']) ? strtoupper($_GET['category']) : 'A';
$by = isset($_GET['by']) ? $_GET['by'] : 'ascending';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 20; // Limit to 20 groups per page
$offset = ($page - 1) * $limit;

// Retrieve the count of images for each group favorited by the user
$query = "
    SELECT `group`, COUNT(*) as count
    FROM images
    LEFT JOIN favorites ON images.id = favorites.image_id
    WHERE favorites.email = :email
    GROUP BY `group`
";
$result = $db->prepare($query);
$result->bindValue(':email', $email, SQLITE3_TEXT);
$result = $result->execute();

// Store the group counts as an associative array
$groupCounts = [];
while ($row = $result->fetchArray()) {
  $groupList = explode(',', $row['group']);
  foreach ($groupList as $group) {
    $trimmedgroup = trim($group);
    if (!isset($groupCounts[$trimmedgroup])) {
      $groupCounts[$trimmedgroup] = 0;
    }
    $groupCounts[$trimmedgroup] += $row['count'];
  }
}

// Group the groups by the first character and sort them
$groupedgroups = [];
foreach ($groupCounts as $group => $count) {
  $firstChar = strtoupper(mb_substr($group, 0, 1));
  $groupedgroups[$firstChar][$group] = $count;
}

ksort($groupedgroups); // Sort groups by first character

// Get groups for the current category and sort them
$currentgroups = isset($groupedgroups[$category]) ? $groupedgroups[$category] : [];
asort($currentgroups); // Sort groups within the category
$totalgroups = count($currentgroups);
$totalPages = ceil($totalgroups / $limit);
$currentgroups = array_slice($currentgroups, $offset, $limit, true);
?>

    <div class="container-fluid mt-2">
      <div class="container-fluid">
        <div class="row justify-content-center">
          <?php foreach ($groupedgroups as $group => $groups): ?>
            <div class="col-4 col-md-2 col-sm-5 px-0">
              <a class="btn btn-outline-light border-0 fw-medium d-flex flex-column align-items-center" href="?by=<?php echo $by; ?>&category=<?php echo $group; ?>">
                <h6 class="fw-medium">Category</h6>
                <h6 class="fw-bold"><?php echo $group; ?></h6>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    
      <?php include('group_card.php'); ?>
    
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