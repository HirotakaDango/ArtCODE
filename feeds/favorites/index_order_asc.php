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
$query = ("SELECT images.* FROM images INNER JOIN favorites ON images.id = favorites.image_id WHERE favorites.email = '$email' ORDER BY images.title ASC LIMIT $limit OFFSET $offset");
$result = $db->query($query);

// Get the total count of favorite images for the current user
$total = $db->querySingle("SELECT COUNT(*) FROM images INNER JOIN favorites ON images.id = favorites.image_id WHERE favorites.email = '$email'");
?>

    <?php include('image_card_feeds_fav.php'); ?>