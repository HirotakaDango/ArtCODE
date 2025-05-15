<?php
// Prepare the query to get the user's numpage
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT); // Assuming $email is the email you want to search for
$resultNum = $queryNum->execute();
$user = $resultNum->fetchArray(SQLITE3_ASSOC);

$numpage = $user['numpage'];

// Set the limit of images per page
$limit = empty($numpage) ? 50 : $numpage;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Get the total number of images favorited by the user
$stmtTotal = $db->prepare("
    SELECT COUNT(images.id) as total
    FROM images
    LEFT JOIN favorites ON images.id = favorites.image_id
    WHERE favorites.email = :email
");

$stmtTotal->bindValue(':email', $email, SQLITE3_TEXT);
$resultTotal = $stmtTotal->execute();
$totalRow = $resultTotal->fetchArray(SQLITE3_ASSOC);

$total = $totalRow['total'];

// Get the images favorited by the user for the current page
$stmt = $db->prepare("
    SELECT images.*
    FROM images
    LEFT JOIN favorites ON images.id = favorites.image_id
    WHERE favorites.email = :email
    ORDER BY images.id DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();
?>

    <?php include('image_card_home.php')?>