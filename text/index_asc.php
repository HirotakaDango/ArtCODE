<?php
// Get the current page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build the SQL query based on search and tag parameters
$query = "SELECT * FROM texts WHERE 1=1";

if ($searchQuery) {
    $query .= " AND (title LIKE '%" . $db->escapeString($searchQuery) . "%' OR content LIKE '%" . $db->escapeString($searchQuery) . "%' OR tags LIKE '%" . $db->escapeString($searchQuery) . "%')";
}

if ($tagFilter) {
    $query .= " AND tags LIKE '%" . $db->escapeString($tagFilter) . "%'";
}

$query .= " ORDER BY id ASC LIMIT $limit OFFSET $offset";

// Fetch results
$results = $db->query($query);

// Fetch total number of items based on search and tag parameters
$countQuery = "SELECT COUNT(*) FROM texts WHERE 1=1";

if ($searchQuery) {
    $countQuery .= " AND (title LIKE '%" . $db->escapeString($searchQuery) . "%' OR content LIKE '%" . $db->escapeString($searchQuery) . "%' OR tags LIKE '%" . $db->escapeString($searchQuery) . "%')";
}

if ($tagFilter) {
    $countQuery .= " AND tags LIKE '%" . $db->escapeString($tagFilter) . "%'";
}

$totalResults = $db->querySingle($countQuery);
$totalPages = ceil($totalResults / $limit);

// Current URL and query parameters for pagination
$currentUrl = $_SERVER['PHP_SELF'];
$queryParams = array_diff_key($_GET, ['page' => '']);
$prevPage = max($page - 1, 1);
$nextPage = min($page + 1, $totalPages);
?>

<div class="container-fluid mt-1">
  <?php include('text_card.php'); ?>
</div>