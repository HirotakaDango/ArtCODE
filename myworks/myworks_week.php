<?php
// Get the current date in YYYY-MM-DD format
$currentDate = date('Y-m-d');

// Calculate the start and end of the current week
$startOfWeek = date('Y-m-d', strtotime('monday this week'));
$endOfWeek = date('Y-m-d', strtotime('sunday this week'));

// Get the user's numpage
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT);
$resultNum = $queryNum->execute();
$user = $resultNum->fetchArray(SQLITE3_ASSOC);

$numpage = $user['numpage'];

// Set the limit of images per page
$limit = empty($numpage) ? 50 : $numpage;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Check for the 'tag' parameter in the URL
if (isset($_GET['tag'])) {
  $tag = $_GET['tag'];

  // Count the total number of tagged images for the current week
  $query = $db->prepare("
    SELECT COUNT(DISTINCT images.id) 
    FROM images
    LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfWeek AND :endOfWeek
    WHERE images.email = :email AND images.tags LIKE :tagPattern
  ");
  $query->bindValue(':email', $email, SQLITE3_TEXT);
  $query->bindValue(':tagPattern', "%$tag%", SQLITE3_TEXT);
  $query->bindValue(':startOfWeek', $startOfWeek, SQLITE3_TEXT);
  $query->bindValue(':endOfWeek', $endOfWeek, SQLITE3_TEXT);
  $total = $query->execute()->fetchArray()[0];

  // Retrieve tagged images for the current week sorted by views
  $stmt = $db->prepare("
    SELECT images.*, COALESCE(SUM(daily.views), 0) AS views
    FROM images
    LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfWeek AND :endOfWeek
    WHERE images.email = :email AND images.tags LIKE :tagPattern
    GROUP BY images.id
    ORDER BY views DESC, images.id DESC
    LIMIT :limit OFFSET :offset
  ");
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':tagPattern', "%$tag%", SQLITE3_TEXT);
  $stmt->bindValue(':startOfWeek', $startOfWeek, SQLITE3_TEXT);
  $stmt->bindValue(':endOfWeek', $endOfWeek, SQLITE3_TEXT);
  $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
  $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);

} else {
  // Count the total number of images for the current week
  $query = $db->prepare("
    SELECT COUNT(DISTINCT images.id)
    FROM images
    LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfWeek AND :endOfWeek
    WHERE images.email = :email
  ");
  $query->bindValue(':email', $email, SQLITE3_TEXT);
  $query->bindValue(':startOfWeek', $startOfWeek, SQLITE3_TEXT);
  $query->bindValue(':endOfWeek', $endOfWeek, SQLITE3_TEXT);
  $total = $query->execute()->fetchArray()[0];

  // Retrieve all images for the current week sorted by views
  $stmt = $db->prepare("
    SELECT images.*, COALESCE(SUM(daily.views), 0) AS views
    FROM images
    LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfWeek AND :endOfWeek
    WHERE images.email = :email
    GROUP BY images.id
    ORDER BY views DESC, images.id DESC
    LIMIT :limit OFFSET :offset
  ");
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':startOfWeek', $startOfWeek, SQLITE3_TEXT);
  $stmt->bindValue(':endOfWeek', $endOfWeek, SQLITE3_TEXT);
  $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
  $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
}

$result = $stmt->execute();
?>

    <?php include('image_card_myworks.php'); ?>