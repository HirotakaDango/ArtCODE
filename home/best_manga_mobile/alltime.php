<?php
// Adjust the query to sum views for all time
$queryManga = "
  SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(SUM(daily.views), 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id
  WHERE images.artwork_type = 'manga'
  GROUP BY images.id
  ORDER BY views DESC, images.id DESC
  LIMIT 12
";
$stmtManga = $db->prepare($queryManga);
$resultManga = $stmtManga->execute();

$imagesManga = array();
while ($rowManga = $resultManga->fetchArray(SQLITE3_ASSOC)) {
  $imagesManga[] = $rowManga;
}
?>

<?php include('image_card_best.php'); ?>