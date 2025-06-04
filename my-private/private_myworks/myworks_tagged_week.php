<?php
// Get the current date in YYYY-MM-DD format
$currentDate = date('Y-m-d');

// Calculate the start and end dates of the current week
$startOfWeek = date('Y-m-d', strtotime('monday this week'));
$endOfWeek = date('Y-m-d', strtotime('sunday this week'));

// Get the user's numpage
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT);
$resultNum = $queryNum->execute();
$user = $resultNum->fetchArray(SQLITE3_ASSOC);

$numpage = $user['numpage'];

// Set the limit of private_images per page
$limit = empty($numpage) ? 50 : $numpage;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Check for the 'tag' parameter in the URL
if (isset($_GET['tag'])) {
  $tag = $_GET['tag'];

  // Count the total number of tagged private_images for the current year
  $query = $db->prepare("
    SELECT COUNT(DISTINCT private_images.id) 
    FROM private_images
    LEFT JOIN private_daily ON private_images.id = private_daily.image_id AND private_daily.date BETWEEN :startOfWeek AND :endOfWeek
    WHERE private_images.email = :email AND private_images.tags LIKE :tagPattern
  ");
  $query->bindValue(':email', $email, SQLITE3_TEXT);
  $query->bindValue(':tagPattern', "%$tag%", SQLITE3_TEXT);
  $query->bindValue(':startOfWeek', $startOfWeek, SQLITE3_TEXT);
  $query->bindValue(':endOfWeek', $endOfWeek, SQLITE3_TEXT);
  $total = $query->execute()->fetchArray()[0];

  // Retrieve tagged private_images for the current year sorted by views
  $stmt = $db->prepare("
    SELECT private_images.*, COALESCE(SUM(private_daily.views), 0) AS views
    FROM private_images
    LEFT JOIN private_daily ON private_images.id = private_daily.image_id AND private_daily.date BETWEEN :startOfWeek AND :endOfWeek
    WHERE private_images.email = :email AND private_images.tags LIKE :tagPattern
    GROUP BY private_images.id
    ORDER BY views DESC, private_images.id DESC
    LIMIT :limit OFFSET :offset
  ");
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':tagPattern', "%$tag%", SQLITE3_TEXT);
  $stmt->bindValue(':startOfWeek', $startOfWeek, SQLITE3_TEXT);
  $stmt->bindValue(':endOfWeek', $endOfWeek, SQLITE3_TEXT);
  $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
  $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);

} else {
  // Count the total number of private_images for the current year
  $query = $db->prepare("
    SELECT COUNT(DISTINCT private_images.id)
    FROM private_images
    LEFT JOIN private_daily ON private_images.id = private_daily.image_id AND private_daily.date BETWEEN :startOfWeek AND :endOfWeek
    WHERE private_images.email = :email
  ");
  $query->bindValue(':email', $email, SQLITE3_TEXT);
  $query->bindValue(':startOfWeek', $startOfWeek, SQLITE3_TEXT);
  $query->bindValue(':endOfWeek', $endOfWeek, SQLITE3_TEXT);
  $total = $query->execute()->fetchArray()[0];

  // Retrieve all private_images for the current year sorted by views
  $stmt = $db->prepare("
    SELECT private_images.*, COALESCE(SUM(private_daily.views), 0) AS views
    FROM private_images
    LEFT JOIN private_daily ON private_images.id = private_daily.image_id AND private_daily.date BETWEEN :startOfWeek AND :endOfWeek
    WHERE private_images.email = :email
    GROUP BY private_images.id
    ORDER BY views DESC, private_images.id DESC
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