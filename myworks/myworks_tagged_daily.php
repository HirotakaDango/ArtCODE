<?php
// Get the current date in YYYY-MM-DD format
$currentDate = date('Y-m-d');

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

  // Count the total number of tagged images
  $query = $db->prepare("
    SELECT COUNT(*) 
    FROM images 
    WHERE email = :email AND tags LIKE :tagPattern
  ");
  $query->bindValue(':email', $email, SQLITE3_TEXT);
  $query->bindValue(':tagPattern', "%$tag%", SQLITE3_TEXT);
  $total = $query->execute()->fetchArray()[0];

  // Retrieve tagged images sorted by daily views
  $stmt = $db->prepare("
    SELECT images.*, COALESCE(daily.views, 0) AS views
    FROM images
    LEFT JOIN daily ON images.id = daily.image_id AND daily.date = :currentDate
    WHERE images.email = :email AND images.tags LIKE :tagPattern
    ORDER BY views DESC, images.id DESC
    LIMIT :limit OFFSET :offset
  ");
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':tagPattern', "%$tag%", SQLITE3_TEXT);
  $stmt->bindValue(':currentDate', $currentDate, SQLITE3_TEXT);
  $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
  $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);

} else {
  // Count the total number of images
  $query = $db->prepare("
    SELECT COUNT(*)
    FROM images
    WHERE email = :email
  ");
  $query->bindValue(':email', $email, SQLITE3_TEXT);
  $total = $query->execute()->fetchArray()[0];

  // Retrieve all images sorted by daily views
  $stmt = $db->prepare("
    SELECT images.*, COALESCE(daily.views, 0) AS views
    FROM images
    LEFT JOIN daily ON images.id = daily.image_id AND daily.date = :currentDate
    WHERE images.email = :email
    ORDER BY views DESC, images.id DESC
    LIMIT :limit OFFSET :offset
  ");
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':currentDate', $currentDate, SQLITE3_TEXT);
  $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
  $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
}

$result = $stmt->execute();
?>

    <?php include('image_card_myworks.php'); ?>