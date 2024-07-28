<?php
// Get the current date
$currentDateManga = date('Y-m-d');

// Get the start of the week (Monday) and end of the week (Sunday)
$startOfWeekManga = date('Y-m-d', strtotime('monday this week'));
$endOfWeekManga = date('Y-m-d', strtotime('sunday this week'));

// Adjust the query to sum views for the current week
$queryManga = "
  SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(SUM(daily.views), 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id 
  AND daily.date BETWEEN :startOfWeekManga AND :endOfWeekManga
  WHERE images.artwork_type = 'manga'
  GROUP BY images.id
  ORDER BY views DESC, images.id DESC
  LIMIT 24
";
$stmtManga = $db->prepare($queryManga);
$stmtManga->bindValue(':startOfWeekManga', $startOfWeekManga, SQLITE3_TEXT);
$stmtManga->bindValue(':endOfWeekManga', $endOfWeekManga, SQLITE3_TEXT);
$resultManga = $stmtManga->execute();

$imagesManga = array();
while ($rowManga = $resultManga->fetchArray(SQLITE3_ASSOC)) {
  $imagesManga[] = $rowManga;
}
?>

<?php include('image_card_best.php'); ?>