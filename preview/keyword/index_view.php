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
  if ($filter == 'characters LIKE :q OR parodies LIKE :q OR `group` LIKE :q OR tags LIKE :q') {
    $sql .= " WHERE $filter";
  } else {
    $sql .= " WHERE $filter LIKE :filterValue";
  }
}
$sql .= " ORDER BY view_count DESC LIMIT :limit OFFSET :offset";

// Get the total number of images with the filter applied
$countSql = "SELECT COUNT(*) FROM images";
if ($filter && $filterValue) {
  if ($filter == 'characters LIKE :q OR parodies LIKE :q OR `group` LIKE :q OR tags LIKE :q') {
    $countSql .= " WHERE $filter";
  } else {
    $countSql .= " WHERE $filter LIKE :filterValue";
  }
}

try {
  // Get the total count
  $countStmt = $db->prepare($countSql);
  if ($filter && $filterValue) {
    if ($filter == 'characters LIKE :q OR parodies LIKE :q OR `group` LIKE :q OR tags LIKE :q') {
      $countStmt->bindValue(':q', "%$filterValue%");
    } else {
      $countStmt->bindValue(':filterValue', "%$filterValue%");
    }
  }
  $countStmt->execute();
  $total = $countStmt->fetchColumn();

  // Get the images for the current page
  $stmt = $db->prepare($sql);
  if ($filter && $filterValue) {
    if ($filter == 'characters LIKE :q OR parodies LIKE :q OR `group` LIKE :q OR tags LIKE :q') {
      $stmt->bindValue(':q', "%$filterValue%");
    } else {
      $stmt->bindValue(':filterValue', "%$filterValue%");
    }
  }
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->execute();
  $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Query failed: " . $e->getMessage());
}
?>

    <?php include('image_card_admin.php')?>