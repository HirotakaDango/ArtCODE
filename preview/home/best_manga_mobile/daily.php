<?php
// Get the current date in YYYY-MM-DD format
$currentDateManga = date('Y-m-d');

// Get all images and user details, joined with daily views, sorted by views for the current day
$queryManga = "
  SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(daily.views, 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id AND daily.date = :currentDateManga
  WHERE images.artwork_type = 'manga'
  GROUP BY images.id
  ORDER BY views DESC, images.id DESC
  LIMIT 12
";
$stmtManga = $db->prepare($queryManga);
$stmtManga->bindValue(':currentDateManga', $currentDateManga, SQLITE3_TEXT);
$resultManga = $stmtManga->execute();

$imagesManga = array();
while ($rowManga = $resultManga->fetchArray(SQLITE3_ASSOC)) {
  $imagesManga[] = $rowManga;
}
?>

<?php include('image_card_best.php'); ?>