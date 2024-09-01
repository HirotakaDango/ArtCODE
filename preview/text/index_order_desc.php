<?php
// Get the current page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get uid parameter
$uid = isset($_GET['uid']) ? (int)$_GET['uid'] : null;

// Build the SQL query based on search, tag, and uid parameters
$query = "SELECT texts.*, users.email AS user_email, users.artist 
          FROM texts 
          LEFT JOIN users ON texts.email = users.email 
          WHERE 1=1";

if ($searchQuery) {
    $query .= " AND (texts.title LIKE '%" . $db->escapeString($searchQuery) . "%' 
                     OR texts.content LIKE '%" . $db->escapeString($searchQuery) . "%' 
                     OR texts.tags LIKE '%" . $db->escapeString($searchQuery) . "%')";
}

if ($tagFilter) {
    $query .= " AND texts.tags LIKE '%" . $db->escapeString($tagFilter) . "%'";
}

if ($uid) {
    $query .= " AND users.id = " . $db->escapeString($uid);
}

$query .= " ORDER BY texts.title DESC LIMIT $limit OFFSET $offset";

// Fetch results
$results = $db->query($query);

// Fetch total number of items based on search, tag, and uid parameters
$countQuery = "SELECT COUNT(*) 
               FROM texts 
               LEFT JOIN users ON texts.email = users.email 
               WHERE 1=1";

if ($searchQuery) {
    $countQuery .= " AND (texts.title LIKE '%" . $db->escapeString($searchQuery) . "%' 
                         OR texts.content LIKE '%" . $db->escapeString($searchQuery) . "%' 
                         OR texts.tags LIKE '%" . $db->escapeString($searchQuery) . "%')";
}

if ($tagFilter) {
    $countQuery .= " AND texts.tags LIKE '%" . $db->escapeString($tagFilter) . "%'";
}

if ($uid) {
    $countQuery .= " AND users.id = " . $db->escapeString($uid);
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