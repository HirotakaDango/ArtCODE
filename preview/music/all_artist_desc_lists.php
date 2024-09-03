<?php
// Set the limit of pagination
$recordsPerPage = 250;
$offset = ($page - 1) * $recordsPerPage;

// Fetch user records
$query = "SELECT id, email, artist, pic 
          FROM users 
          ORDER BY id DESC 
          LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);
$stmt->bindValue(':limit', $recordsPerPage, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();

// Fetch all rows as an associative array
$rows = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  $rows[] = $row;
}

// Calculate total pages for the logged-in user
$total = $db->querySingle("SELECT COUNT(*) FROM users");
$totalPages = ceil($total / $recordsPerPage);
$prevPage = $page - 1;
$nextPage = $page + 1;
?>

    <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)) : ?>
      <div class="card bg-dark-subtle bg-opacity-10 link-body-emphasis rounded-4 border-0 shadow mt-2">
        <div class="card-body">
          <h5 class="card-text fw-bold text-shadow"><a class="text-decoration-none text-white" href="artist.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&id=<?php echo $row['id']; ?>"><?php echo $row['artist']; ?></a></h5>
        </div>
      </div>
    <?php endwhile; ?>
