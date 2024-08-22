<?php
// Get the current date
$currentDate = date('Y-m-d');

// Get the start of the week (Monday) and end of the week (Sunday)
$startOfWeek = date('Y-m-d', strtotime('monday this week'));
$endOfWeek = date('Y-m-d', strtotime('sunday this week'));

// Adjust the query to sum views for the current week and handle pagination
$query = "
  SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(SUM(daily.views), 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id 
    AND daily.date BETWEEN :startOfWeek AND :endOfWeek
  GROUP BY images.id
  ORDER BY views DESC, images.id DESC
  LIMIT $offset, $limit
";

$stmt = $db->prepare($query);
$stmt->bindValue(':startOfWeek', $startOfWeek, SQLITE3_TEXT);
$stmt->bindValue(':endOfWeek', $endOfWeek, SQLITE3_TEXT);
$result = $stmt->execute();
?>

<?php include('image_card_gallerium.php'); ?>