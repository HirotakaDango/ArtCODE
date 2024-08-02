<?php
// Get the current date
$currentDate = date('Y-m-d');

// Calculate the first and last day of the current year
$startOfYear = date('Y-01-01'); // First day of the year
$endOfYear = date('Y-12-31');   // Last day of the year

// Adjust the query to sum views for the current year and handle pagination
$query = "
  SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(SUM(daily.views), 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id 
    AND daily.date BETWEEN :startOfYear AND :endOfYear
  GROUP BY images.id
  ORDER BY views DESC, images.id DESC
  LIMIT :offset, :limit
";

$stmt = $db->prepare($query);
$stmt->bindValue(':startOfYear', $startOfYear, SQLITE3_TEXT);
$stmt->bindValue(':endOfYear', $endOfYear, SQLITE3_TEXT);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$result = $stmt->execute();
?>

<?php include('image_card_gallerium.php'); ?>