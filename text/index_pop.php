<?php
// Get the current page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build the SQL query to sort by most favorited
$query = "SELECT texts.*, COUNT(text_favorites.id) AS favorite_count 
          FROM texts
          LEFT JOIN text_favorites ON texts.id = text_favorites.text_id
          WHERE 1=1";

if ($searchQuery) {
    $query .= " AND (texts.title LIKE '%" . $db->escapeString($searchQuery) . "%' 
                OR texts.content LIKE '%" . $db->escapeString($searchQuery) . "%' 
                OR texts.tags LIKE '%" . $db->escapeString($searchQuery) . "%')";
}

if ($tagFilter) {
    $query .= " AND texts.tags LIKE '%" . $db->escapeString($tagFilter) . "%'";
}

$query .= " GROUP BY texts.id
            ORDER BY favorite_count DESC, texts.id DESC
            LIMIT $limit OFFSET $offset";

// Fetch results
$results = $db->query($query);

// Fetch total number of items based on search and tag parameters
$countQuery = "SELECT COUNT(DISTINCT texts.id) 
               FROM texts
               LEFT JOIN text_favorites ON texts.id = text_favorites.text_id
               WHERE 1=1";

if ($searchQuery) {
    $countQuery .= " AND (texts.title LIKE '%" . $db->escapeString($searchQuery) . "%' 
                        OR texts.content LIKE '%" . $db->escapeString($searchQuery) . "%' 
                        OR texts.tags LIKE '%" . $db->escapeString($searchQuery) . "%')";
}

if ($tagFilter) {
    $countQuery .= " AND texts.tags LIKE '%" . $db->escapeString($tagFilter) . "%'";
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