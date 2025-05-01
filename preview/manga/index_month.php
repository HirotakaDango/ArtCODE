<?php
$startOfMonth = date('Y-m-01');
$endOfMonth = date('Y-m-t');
$params[':startDate'] = $startOfMonth;
$params[':endDate'] = $endOfMonth;

$query = "
  SELECT base.*, COALESCE(SUM(daily.views), 0) AS views
  FROM (
    $query
  ) AS base
  LEFT JOIN daily ON base.id = daily.image_id AND daily.date BETWEEN :startDate AND :endDate
  GROUP BY base.id
  ORDER BY views DESC, base.id DESC
  LIMIT :limit OFFSET :offset
";

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$params[':limit'] = $limit;
$params[':offset'] = $offset;

$stmt = $db->prepare($query);
foreach ($params as $param => $value) {
  $type = in_array($param, [':limit', ':offset']) ? PDO::PARAM_INT : PDO::PARAM_STR;
  $stmt->bindValue($param, $value, $type);
}
$stmt->execute();

$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($images as &$result) {
  unset($result['email']);
}

$totalImages = count($images);
$totalPages = ceil($totalImages / $limit);
$displayImages = array_slice($images, 0, $limit);
?>

    <?php include('image_card_feeds_manga.php'); ?>