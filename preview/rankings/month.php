<?php
// Set the limit of images per page
$limit = 12;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Get the total number of images
$total = $db->querySingle("SELECT COUNT(*) FROM images");

// Get the current date
$currentDate = date('Y-m-d');

// Calculate the first and last day of the current month
$startOfMonth = date('Y-m-01'); // First day of the month
$endOfMonth = date('Y-m-t');   // Last day of the month

// Adjust the query to sum views for the current month and handle pagination
$query = "
  SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(SUM(daily.views), 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id 
    AND daily.date BETWEEN :startOfMonth AND :endOfMonth
  GROUP BY images.id
  ORDER BY views DESC, images.id DESC
  LIMIT :offset, :limit
";

// Prepare and execute the query
$stmt = $db->prepare($query);
$stmt->bindValue(':startOfMonth', $startOfMonth, SQLITE3_TEXT);
$stmt->bindValue(':endOfMonth', $endOfMonth, SQLITE3_TEXT);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);

$result = $stmt->execute();

if (!$result) {
  die("Error executing query to get images: " . $db->lastErrorMsg());
}
?>

<?php include('image_card_rankings.php'); ?>