<?php
// Get the current page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get uid parameter
$uid = isset($_GET['uid']) ? (int)$_GET['uid'] : null;

// Build the SQL query to filter by user's favorites
$query = "SELECT texts.*, COUNT(text_favorites.id) AS favorite_count 
          FROM texts
          JOIN text_favorites ON texts.id = text_favorites.text_id
          JOIN users ON text_favorites.email = users.email
          WHERE 1=1";

if ($email) {
    $query .= " AND text_favorites.email = :email";
}

if ($uid) {
    $query .= " AND users.id = :uid";
}

if ($searchQuery) {
    $query .= " AND (texts.title LIKE '%" . $db->escapeString($searchQuery) . "%' 
                OR texts.content LIKE '%" . $db->escapeString($searchQuery) . "%' 
                OR texts.tags LIKE '%" . $db->escapeString($searchQuery) . "%')";
}

if ($tagFilter) {
    $query .= " AND texts.tags LIKE '%" . $db->escapeString($tagFilter) . "%'";
}

$query .= " GROUP BY texts.id
            ORDER BY texts.view_count ASC
            LIMIT $limit OFFSET $offset";

// Prepare and execute the query
$stmt = $db->prepare($query);
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
if ($uid) {
    $stmt->bindValue(':uid', $uid, SQLITE3_INTEGER);
}
$results = $stmt->execute();

// Fetch total number of items based on search, tag, and uid parameters
$countQuery = "SELECT COUNT(DISTINCT texts.id) 
               FROM texts
               JOIN text_favorites ON texts.id = text_favorites.text_id
               JOIN users ON text_favorites.email = users.email
               WHERE 1=1";

if ($email) {
    $countQuery .= " AND text_favorites.email = :email";
}

if ($uid) {
    $countQuery .= " AND users.id = :uid";
}

if ($searchQuery) {
    $countQuery .= " AND (texts.title LIKE '%" . $db->escapeString($searchQuery) . "%' 
                        OR texts.content LIKE '%" . $db->escapeString($searchQuery) . "%' 
                        OR texts.tags LIKE '%" . $db->escapeString($searchQuery) . "%')";
}

if ($tagFilter) {
    $countQuery .= " AND texts.tags LIKE '%" . $db->escapeString($tagFilter) . "%'";
}

// Prepare and execute the count query
$countStmt = $db->prepare($countQuery);
$countStmt->bindValue(':email', $email, SQLITE3_TEXT);
if ($uid) {
    $countStmt->bindValue(':uid', $uid, SQLITE3_INTEGER);
}
$totalResults = $countStmt->execute()->fetchArray(SQLITE3_NUM)[0];
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