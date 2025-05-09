<?php
// Prepare the query to get the user's numpage
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT); // Assuming $email is the email you want to search for
$resultNum = $queryNum->execute();

if (!$resultNum) {
  die("Error executing query to get numpage: " . $db->lastErrorMsg());
}

$user = $resultNum->fetchArray(SQLITE3_ASSOC);

$numpage = $user['numpage'];

// Set the limit of images per page
$limit = empty($numpage) ? 50 : (int)$numpage; // Ensure limit is an integer

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Get the total number of images
$total = $db->querySingle("SELECT COUNT(*) FROM images");

// Get the current date in YYYY-MM-DD format
$currentDate = date('Y-m-d');

// Prepare and execute the query to get the images for the current page
$stmt = $db->prepare("SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(daily.views, 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id AND daily.date = :currentDate
  GROUP BY images.id
  ORDER BY views DESC, images.id DESC LIMIT :offset, :limit");

$stmt->bindValue(':currentDate', $currentDate, SQLITE3_TEXT);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);

$result = $stmt->execute();

if (!$result) {
  die("Error executing query to get images: " . $db->lastErrorMsg());
}
?>

<?php include('image_card_rankings.php'); ?>