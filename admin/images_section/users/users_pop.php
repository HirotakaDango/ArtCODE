<?php
// Get the category (first letter) from URL parameters, default to 'A'
$category = isset($_GET['category']) ? strtoupper($_GET['category']) : 'A';

// Pagination variables
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$by = isset($_GET['by']) ? $_GET['by'] : 'ascending';
$limit = 20;
$offset = ($page - 1) * $limit;

// Retrieve users from the database based on the search query and sorted by follower count
$query = "SELECT users.*, SUBSTR(users.artist, 1, 1) AS first_letter, COUNT(following.follower_email) AS follower_count 
          FROM users 
          LEFT JOIN following ON users.email = following.following_email";
if (!empty($searchQuery)) {
  $query .= " WHERE users.artist LIKE '%$searchQuery%'";
}
$query .= " GROUP BY users.email 
            ORDER BY first_letter COLLATE NOCASE ASC, follower_count DESC, users.artist COLLATE NOCASE ASC";

$users = $db->query($query);

// Group users by category (first letter)
$groupedUsers = [];
while ($user = $users->fetchArray(SQLITE3_ASSOC)) {
  $letter = strtoupper($user['first_letter']);
  $groupedUsers[$letter][] = $user;
}

// Select users for the current category (first letter)
$currentUsers = isset($groupedUsers[$category]) ? $groupedUsers[$category] : [];

// Pagination logic
$totalUsers = count($currentUsers);
$totalPages = ceil($totalUsers / $limit);
$currentUsers = array_slice($currentUsers, $offset, $limit, true);
?>

    <div class="container-fluid mt-2">
      <div class="container-fluid">
        <div class="row justify-content-center">
          <?php foreach ($groupedUsers as $group => $users): ?>
            <div class="col-4 col-md-2 col-sm-5 px-0">
              <a class="btn btn-outline-light border-0 fw-medium d-flex flex-column align-items-center" href="?by=<?php echo $by; ?>&category=<?php echo $group; ?>">
                <h6 class="fw-medium">Category</h6>
                <h6 class="fw-bold"><?php echo $group; ?></h6>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    
      <?php include('user_card.php'); ?>
    
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