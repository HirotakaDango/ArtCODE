<?php
// Get the current date in YYYY-MM-DD format
$currentDate = date('Y-m-d');

// Get the images for the current page
$stmt = $db->prepare("SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(daily.views, 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id AND daily.date = :currentDate
  ORDER BY views DESC, images.id DESC LIMIT :offset, :limit");
$stmt->bindValue(':currentDate', $currentDate, SQLITE3_TEXT);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$result = $stmt->execute();
?>

<?php include('image_card_gallerium.php'); ?>