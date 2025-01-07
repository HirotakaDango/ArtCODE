<?php
// Get the current date in YYYY-MM-DD format
$currentDate = date('Y-m-d');

// Get all images and user details, joined with daily views, sorted by views for the current day
$query = "
  SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(daily.views, 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id AND daily.date = :currentDate
  WHERE images.artwork_type = 'illustration'
  GROUP BY images.id
  ORDER BY views DESC, images.id DESC
  LIMIT 12
";
$stmt = $db->prepare($query);
$stmt->bindValue(':currentDate', $currentDate, SQLITE3_TEXT);
$result = $stmt->execute();

$images = array();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  $images[] = $row;
}

// Get the latest image for the background
$latestImage = $images[0]['filename'];
$backgroundImageUrl = "/images/" . $latestImage; // Adjust this path if needed

// Get the artist information for the latest image
$latestArtistName = $images[0]['artist'];
$latestArtistId = $images[0]['user_id'];
?>

<?php include('image_card_best.php'); ?>