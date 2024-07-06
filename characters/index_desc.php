<?php
// Get the category from URL parameters, default to 'A'
$category = isset($_GET['category']) ? strtoupper($_GET['category']) : 'A';
$by = isset($_GET['by']) ? $_GET['by'] : 'ascending';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 20; // Limit to 20 characters per page
$offset = ($page - 1) * $limit;

// Retrieve the count of images for each character matching the search condition
$query = "SELECT characters, COUNT(*) as count FROM images";
if (!empty($searchCondition)) {
  $query .= " WHERE $searchCondition";
}
$query .= " GROUP BY characters";

$result = $db->query($query);

// Store the character counts as an associative array
$characterCounts = [];
while ($row = $result->fetchArray()) {
  $characterList = explode(',', $row['characters']);
  foreach ($characterList as $character) {
    $trimmedcharacter = trim($character);
    if (!isset($characterCounts[$trimmedcharacter])) {
      $characterCounts[$trimmedcharacter] = 0;
    }
    $characterCounts[$trimmedcharacter] += $row['count'];
  }
}

// Group the characters by the last character and sort them in descending order
$groupedcharacters = [];
foreach ($characterCounts as $character => $count) {
  $lastChar = strtoupper(mb_substr($character, -1, 1));
  $groupedcharacters[$lastChar][$character] = $count;
}

krsort($groupedcharacters); // Sort groups by last character in descending order

// Get characters for the current category and sort them in descending order
$currentcharacters = isset($groupedcharacters[$category]) ? $groupedcharacters[$category] : [];
arsort($currentcharacters); // Sort characters within the category in descending order
$totalcharacters = count($currentcharacters);
$totalPages = ceil($totalcharacters / $limit);
$currentcharacters = array_slice($currentcharacters, $offset, $limit, true);
?>

    <div class="container-fluid mt-2">
      <div class="container-fluid">
        <div class="row justify-content-center">
          <?php foreach ($groupedcharacters as $group => $characters): ?>
            <div class="col-4 col-md-2 col-sm-5 px-0">
              <a class="btn btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> border-0 fw-medium d-flex flex-column align-items-center" href="?by=<?php echo $by; ?>&category=<?php echo $group; ?>">
                <h6 class="fw-medium">Category</h6>
                <h6 class="fw-bold"><?php echo $group; ?></h6>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    
      <?php include('character_card.php'); ?>
    
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