<?php
// Prepare the query to get the user's numpage
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT); // Assuming $email is the email you want to search for
$resultNum = $queryNum->execute();
$user = $resultNum->fetchArray(SQLITE3_ASSOC);

$numpage = $user['numpage'];

// Set the limit of images per page
$limit = empty($numpage) ? 50 : $numpage;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Get all of the favorite images for the current user with pagination
$query = $db->prepare("SELECT images.*, COUNT(favorites.id) AS favorite_count  FROM images  LEFT JOIN favorites ON images.id = favorites.image_id  GROUP BY images.id  ORDER BY favorite_count DESC  LIMIT :limit OFFSET :offset");
$query->bindValue(':limit', $numpage, SQLITE3_INTEGER);
$query->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $query->execute();

// Get the total count of favorite images for the current user
$total = $db->querySingle("SELECT COUNT(DISTINCT images.id)  FROM images  LEFT JOIN favorites ON images.id = favorites.image_id");
?>

    <?php include('image_card_feeds_fav.php'); ?>