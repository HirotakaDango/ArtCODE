<?php
// Get the current date in YYYY-MM-DD format
$currentDate = date('Y-m-d');

// Prepare the query to get the user's numpage
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT); // Assuming $email is the email you want to search for
$resultNum = $queryNum->execute();
$user = $resultNum->fetchArray(SQLITE3_ASSOC);

$numpage = $user['numpage'];

// Set the limit of private_images per page
$limit = empty($numpage) ? 50 : $numpage;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Get the total number of private_images for the current user
$query = $db->prepare("SELECT COUNT(*) FROM private_images WHERE email = :email");
$query->bindValue(':email', $email);
$total = $query->execute()->fetchArray()[0];

// Get all of the private_images uploaded by the current user for the current day
$stmt = $db->prepare("
  SELECT private_images.*, COALESCE(private_daily.views, 0) AS views
  FROM private_images
  LEFT JOIN private_daily ON private_images.id = private_daily.image_id AND private_daily.date = :currentDate
  WHERE private_images.email = :email
  ORDER BY views DESC, private_images.id DESC
  LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':currentDate', $currentDate);
$stmt->bindValue(':email', $email);
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();
?>

    <?php include('image_card_myworks.php'); ?>