<?php
// Get the category (first letter) from URL parameters, default to 'A'
$category = strtoupper($_GET['category'] ?? 'A');

// Pagination variables
$page = intval($_GET['page'] ?? 1);
$by = $_GET['by'] ?? 'ascending';
$limit = 20;
$offset = ($page - 1) * $limit;

// Get the list of users that the logged-in user is following
$stmt = $db->prepare("SELECT * FROM following WHERE follower_email = :email");
$stmt->bindValue(':email', $email);
$result = $stmt->execute();
$follower_users = array();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  $follower_users[] = $row['following_email'];
}

// Get the details of the following users, ordered alphabetically by artist name (A-Z)
$stmt = $db->prepare("SELECT *, SUBSTR(artist, 1, 1) AS first_letter FROM users WHERE email IN ('" . implode("','", $follower_users) . "') ORDER BY id ASC");
$result = $stmt->execute();

// Count the number of followers
$follower_count = count($follower_users);

// Group users by category
$groupedUsers = [];
while ($user = $result->fetchArray(SQLITE3_ASSOC)) {
  $letter = strtoupper($user['first_letter']);
  $groupedUsers[$letter][] = $user;
}

ksort($groupedUsers);

// Select users for the current category (first letter)
$currentUsers = $groupedUsers[$category] ?? [];

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
              <a class="btn btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> border-0 fw-medium d-flex flex-column align-items-center" href="?id=<?php echo $user_id; ?>&by=<?php echo $by; ?>&category=<?php echo $group; ?>">
                <h6 class="fw-medium">Category</h6>
                <h6 class="fw-bold"><?php echo $group; ?></h6>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    
      <?php include('following_card.php'); ?>
    
      <!-- Pagination -->
      <div class="pagination d-flex gap-1 justify-content-center mt-3">
        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?id=<?php echo $user_id; ?>&by=<?php echo $by; ?>&category=<?php echo $category; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
        <?php endif; ?>
    
        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?id=<?php echo $user_id; ?>&by=<?php echo $by; ?>&category=<?php echo $category; ?>&page=<?php echo $page - 1; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?id=' . $user_id . '&by=' . $by . '&category=' . $category . '&page=' . $i . '">' . $i . '</a>';
          }
        }
        ?>
    
        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?id=<?php echo $user_id; ?>&by=<?php echo $by; ?>&category=<?php echo $category; ?>&page=<?php echo $page + 1; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
        <?php endif; ?>
    
        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?id=<?php echo $user_id; ?>&by=<?php echo $by; ?>&category=<?php echo $category; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
        <?php endif; ?>
      </div>
    </div>
    <div class="mt-5"></div>