<?php
// Get the current date in YYYY-MM-DD format
$currentDate = date('Y-m-d');

// Calculate the start and end of the current year
$startOfYear = date('Y-01-01');
$endOfYear = date('Y-12-31');

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

// Build the query to count the total number of images based on the filter
$countSql = "
  SELECT COUNT(DISTINCT images.id)
  FROM images
  LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfYear AND :endOfYear
";
if ($filter && $filterValue) {
  $countSql .= " WHERE $filter LIKE :filterValue";
}

try {
  // Get the total count
  $countStmt = $db->prepare($countSql);
  $countStmt->bindValue(':startOfYear', $startOfYear);
  $countStmt->bindValue(':endOfYear', $endOfYear);
  if ($filter && $filterValue) {
    $countStmt->bindValue(':filterValue', "%$filterValue%");
  }
  $countStmt->execute();
  $total = $countStmt->fetchColumn();

  // Build the query to retrieve images based on the filter
  $sql = "
    SELECT images.*, COALESCE(SUM(daily.views), 0) AS views
    FROM images
    LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfYear AND :endOfYear
  ";
  if ($filter && $filterValue) {
    $sql .= " WHERE $filter LIKE :filterValue";
  }
  $sql .= " GROUP BY images.id ORDER BY views DESC, images.id DESC LIMIT :limit OFFSET :offset";

  // Get the images for the current page
  $stmt = $db->prepare($sql);
  $stmt->bindValue(':startOfYear', $startOfYear);
  $stmt->bindValue(':endOfYear', $endOfYear);
  if ($filter && $filterValue) {
    $stmt->bindValue(':filterValue', "%$filterValue%");
  }
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();
  $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Query failed: " . $e->getMessage());
}
?>

    <?php include('image_card_admin.php')?>