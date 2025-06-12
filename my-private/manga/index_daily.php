<?php
$currentDate = date('Y-m-d');
$params[':currentDate'] = $currentDate;

// Modify the query to include private_daily views
$query = "
  SELECT base.*, COALESCE(private_daily.views, 0) AS views
  FROM (
    $query
  ) AS base
  LEFT JOIN private_daily ON base.id = private_daily.image_id AND private_daily.date = :currentDate
  ORDER BY views DESC, base.id DESC
  LIMIT :limit OFFSET :offset
";

// Pagination variables
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$params[':limit'] = $limit;
$params[':offset'] = $offset;

// Prepare and execute the SQL statement with bound parameters
$stmt = $db->prepare($query);
foreach ($params as $param => $value) {
  if (in_array($param, [':limit', ':offset'])) {
    $stmt->bindValue($param, $value, PDO::PARAM_INT);
  } else {
    $stmt->bindValue($param, $value);
  }
}
$stmt->execute();

// Fetch results and remove sensitive email field
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($images as &$result) {
  unset($result['email']);
}

// Pagination
$totalImages = count($images);
$totalPages = ceil($totalImages / $limit);
$displayImages = array_slice($images, 0, $limit);
?>

    <?php include('image_card_feeds_manga.php'); ?>