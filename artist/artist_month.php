<?php include('header_artist_month.php'); ?>
<?php
// Get the current date in YYYY-MM-DD format
$currentDate = date('Y-m-d');

// Calculate the start and end of the current month
$startOfMonth = date('Y-m-01'); // First day of the current month
$endOfMonth = date('Y-m-t');    // Last day of the current month

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

// Get the total number of images for the selected user
$query = $db->prepare('SELECT COUNT(*) FROM images JOIN users ON images.email = users.email WHERE users.id = :id');
$query->bindParam(':id', $id);
$query->execute();
$total = $query->fetchColumn();

// Get all images for the selected user, sorted by monthly views
$query = $db->prepare("
  SELECT images.*, COALESCE(SUM(daily.views), 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfMonth AND :endOfMonth
  WHERE users.id = :id
  GROUP BY images.id
  ORDER BY views DESC, images.id DESC
  LIMIT :limit OFFSET :offset
");
$query->bindParam(':id', $id);
$query->bindParam(':startOfMonth', $startOfMonth);
$query->bindParam(':endOfMonth', $endOfMonth);
$query->bindValue(':limit', $limit, PDO::PARAM_INT);
$query->bindValue(':offset', $offset, PDO::PARAM_INT);
$query->execute();
$images = $query->fetchAll(PDO::FETCH_ASSOC);
?>

    <?php include('image_card_art_month.php'); ?>