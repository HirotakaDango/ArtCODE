<?php include('header_profile_month.php'); ?>
<?php
// Get the current date in YYYY-MM-DD format
$currentDate = date('Y-m-d');

// Calculate the start and end of the current month
$startOfMonth = date('Y-m-01');
$endOfMonth = date('Y-m-t');

// Get the user's numpage
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindParam(':email', $email, PDO::PARAM_STR);
$queryNum->execute();
$user = $queryNum->fetch(PDO::FETCH_ASSOC);

$numpage = $user['numpage'];

// Set the limit of images per page
$limit = empty($numpage) ? 50 : $numpage;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Get the total number of images for the current user
$query = $db->prepare("SELECT COUNT(*) FROM images WHERE email = :email");
$query->bindValue(':email', $email);
if ($query->execute()) {
  $total = $query->fetchColumn();
} else {
  // Handle the query execution error
  echo "Error executing the query.";
}

// Get all of the images uploaded by the current user, sorted by monthly views
$query = $db->prepare("
  SELECT images.*, COALESCE(SUM(daily.views), 0) AS views
  FROM images
  LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfMonth AND :endOfMonth
  WHERE images.email = :email
  GROUP BY images.id
  ORDER BY views DESC, images.id DESC
  LIMIT :limit OFFSET :offset
");
$query->bindValue(':email', $email);
$query->bindParam(':startOfMonth', $startOfMonth);
$query->bindParam(':endOfMonth', $endOfMonth);
$query->bindValue(':limit', $limit, PDO::PARAM_INT);
$query->bindValue(':offset', $offset, PDO::PARAM_INT);
if ($query->execute()) {
  // Fetch the results as an associative array
  $results = $query->fetchAll(PDO::FETCH_ASSOC);
} else {
  // Handle the query execution error
  echo "Error executing the query.";
}
?>

    <?php include('image_card_pro_month.php'); ?>