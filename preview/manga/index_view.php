<?php
// Determine sorting order based on 'by' parameter
$query .= " ORDER BY images.view_count DESC";

// Prepare and execute the SQL statement with bound parameters
$stmt = $db->prepare($query);
foreach ($params as $param => $value) {
  $stmt->bindValue($param, $value);
}
$stmt->execute();

// Fetch results and remove sensitive email field
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($images as &$result) {
  unset($result['email']);
}

// Set up pagination variables
$totalImages = count($images);

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$totalPages = ceil($totalImages / $limit);
$displayImages = array_slice($images, $offset, $limit);
?>

    <?php include('image_card_feeds_manga.php'); ?>