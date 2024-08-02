<?php
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
?>

<?php include('image_card_gallerium.php'); ?>