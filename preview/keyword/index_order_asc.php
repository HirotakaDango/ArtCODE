<?php
// Set the limit of images per page
$limit = 12;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Build the query to get images based on the filter
$sql = "SELECT * FROM images";
if ($filter && $filterValue) {
  $sql .= " WHERE $filter LIKE :filterValue";
}
$sql .= " ORDER BY title ASC LIMIT :limit OFFSET :offset";

// Get the total number of images with the filter applied
$countSql = "SELECT COUNT(*) FROM images";
if ($filter && $filterValue) {
  $countSql .= " WHERE $filter LIKE :filterValue";
}

try {
  // Get the total count
  $countStmt = $db->prepare($countSql);
  if ($filter && $filterValue) {
    $countStmt->bindValue(':filterValue', "%$filterValue%");
  }
  $countStmt->execute();
  $total = $countStmt->fetchColumn();

  // Get the images for the current page
  $stmt = $db->prepare($sql);
  if ($filter && $filterValue) {
    $stmt->bindValue(':filterValue', "%$filterValue%");
  }
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->execute();
  $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Query failed: " . $e->getMessage());
}
?>

    <?php include('image_card_keyword_preview.php')?>