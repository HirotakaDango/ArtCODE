<?php
// Set the limit of images per page
$limit = 12;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Get the total number of images
$total = $db->querySingle("SELECT COUNT(*) FROM images");

// Adjust the query to sum views for all time and handle pagination
$query = "
  SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(SUM(daily.views), 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id 
  GROUP BY images.id
  ORDER BY views DESC, images.id DESC
  LIMIT $offset, $limit
";

$stmt = $db->prepare($query);
$result = $stmt->execute();

if (!$result) {
  die("Error executing query to get images: " . $db->lastErrorMsg());
}
?>

<?php include('image_card_rankings.php') ?>