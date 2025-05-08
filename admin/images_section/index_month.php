<?php
// Get the current date in YYYY-MM-DD format
$currentDate = date('Y-m-d');

// Get the first and last day of the current month
$startOfMonth = date('Y-m-01');
$endOfMonth = date('Y-m-t');

// Retrieve the email from the session
$email = $_SESSION['admin']['email'];

// Prepare the query to get the user's numpage
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, PDO::PARAM_STR);

try {
  $queryNum->execute();
  $user = $queryNum->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Query failed: " . $e->getMessage());
}

$numpage = isset($user['numpage']) ? $user['numpage'] : null;

// Set the limit of images per page
$limit = empty($numpage) ? 50 : $numpage;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Get the total number of images
try {
  $total = $db->query("SELECT COUNT(*) FROM images")->fetchColumn();
} catch (PDOException $e) {
  die("Query failed: " . $e->getMessage());
}

// Query to get images ranked by monthly views
$stmt = $db->prepare("
  SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(SUM(daily.views), 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfMonth AND :endOfMonth
  GROUP BY images.id
  ORDER BY views DESC, images.id DESC
  LIMIT :limit OFFSET :offset
");

$stmt->bindValue(':startOfMonth', $startOfMonth, PDO::PARAM_STR);
$stmt->bindValue(':endOfMonth', $endOfMonth, PDO::PARAM_STR);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

try {
  $stmt->execute();
  $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Query failed: " . $e->getMessage());
}
?>

    <?php include('image_card_admin.php')?>