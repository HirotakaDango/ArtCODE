<?php
// Get the current date
$currentDateManga = date('Y-m-d');

// Calculate the first and last day of the current year
$startOfYearManga = date('Y-01-01'); // First day of the year
$endOfYearManga = date('Y-12-31');   // Last day of the year

// Adjust the query to sum views for the current year
$queryManga = "
  SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(SUM(daily.views), 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id 
  AND daily.date BETWEEN :startOfYearManga AND :endOfYearManga
  WHERE images.artwork_type = 'manga'
  GROUP BY images.id
  ORDER BY views DESC, images.id DESC
  LIMIT 12
";
$stmtManga = $db->prepare($queryManga);
$stmtManga->bindValue(':startOfYearManga', $startOfYearManga, SQLITE3_TEXT);
$stmtManga->bindValue(':endOfYearManga', $endOfYearManga, SQLITE3_TEXT);
$resultManga = $stmtManga->execute();

$imagesManga = array();
while ($rowManga = $resultManga->fetchArray(SQLITE3_ASSOC)) {
  $imagesManga[] = $rowManga;
}
?>

<?php include('image_card_best.php'); ?>