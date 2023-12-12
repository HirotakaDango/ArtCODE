<?php
require_once('../../auth.php');
$db = new SQLite3('../../database.sqlite');
$email = $_SESSION['email'];

// Pagination
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$recordsPerPage = 20;
$offset = ($page - 1) * $recordsPerPage;

// Get the search parameter from the URL
$searchQuery = isset($_GET['q']) ? $_GET['q'] : null;

// Fetch music records with user information and filter by search query if provided
$query = "SELECT music.id, music.file, music.email, music.cover, music.album, music.title, users.id AS userid, users.artist 
          FROM music 
          LEFT JOIN users ON music.email = users.email";

// If search query is provided, filter by album or title
if (!empty($searchQuery)) {
    $query .= " WHERE music.album LIKE :searchQuery OR music.title LIKE :searchQuery";
}

$query .= " ORDER BY music.id DESC LIMIT :limit OFFSET :offset";

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
$total = $db->querySingle("SELECT COUNT(*) FROM music WHERE email = '$email'");
$totalPages = ceil($total / $recordsPerPage);
$prevPage = $page - 1;
$nextPage = $page + 1;
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo (!empty($rows) ? htmlspecialchars($rows[0]['album']) : 'Untitled Album'); ?></title>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container-fluid mt-3">
      <?php include('header.php'); ?>
      <h5 class="fw-bold mb-3">Album: <?php echo (!empty($rows) ? htmlspecialchars($rows[0]['album']) : 'Untitled Album'); ?></h5>
      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 row-cols-xl-8 g-1">
        <?php foreach ($rows as $row): ?>
          <div class="col">
            <div class="card shadow-sm h-100">
              <a class="shadow rounded" href="music.php?album=<?php echo urlencode($row['album']); ?>&id=<?php echo $row['id']; ?>">
                <img class="w-100 object-fit-cover" style="border-radius: 2.9px 2.9px 0 0;" height="200" src="covers/<?php echo $row['cover']; ?>">
              </a>
              <div class="card-body">
                <h5 class="card-text text-center fw-bold"><?php echo $row['title']; ?></h5>
                <p class="card-text text-center small fw-bold"><small>by <?php echo $row['artist']; ?></small></p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Pagination -->
    <div class="container mt-3">
      <div class="pagination d-flex gap-1 justify-content-center mt-3">
        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
          <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?page=' . $i . '">' . $i . '</a>';
          }
        }
        ?>

        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
          <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
        <?php endif; ?>
      </div>
    </div>

    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
