<?php
// Set the limit of pagination
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

      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 row-cols-xl-8 g-1">
        <?php foreach ($rows as $row): ?>
          <?php include('music_info.php'); ?>
        <?php endforeach; ?>
      </div>