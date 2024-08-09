<?php
// Set the limit of pagination
$recordsPerPage = 20;
$offset = ($page - 1) * $recordsPerPage;

// Fetch user records
$query = "SELECT id, email, artist, pic 
          FROM users 
          ORDER BY id ASC 
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
$total = $db->querySingle("SELECT COUNT(*) FROM users WHERE email = '$email'");
$totalPages = ceil($total / $recordsPerPage);
$prevPage = $page - 1;
$nextPage = $page + 1;
?>

      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 row-cols-xl-8 g-1">
        <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)) : ?>
          <div class="col">
            <div class="card shadow-sm h-100 position-relative rounded-3">
              <a class="shadow position-relative btn p-0" href="artist.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&id=<?php echo $row['id']; ?>">
                <img class="w-100 object-fit-cover rounded" height="200" src="../../<?php echo empty($row['pic']) ? '../../icon/profile.svg' : $row['pic']; ?>">
              </a>
              <div class="p-2 position-absolute bottom-0 start-0">
                <h5 class="card-text fw-bold text-shadow"><a class="text-decoration-none text-white" href="artist.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&id=<?php echo $row['id']; ?>"><?php echo $row['artist']; ?></a></h5>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>