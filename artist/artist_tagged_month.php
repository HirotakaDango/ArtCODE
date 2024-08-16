<?php include('header_artist_month.php'); ?>
<?php
// Get the current date in YYYY-MM-DD format
$currentDate = date('Y-m-d');

// Calculate the start and end of the current month
$startOfMonth = date('Y-m-01'); // First day of the current month
$endOfMonth = date('Y-m-t');    // Last day of the current month

// Prepare the query to get the number of pages
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

// Check for the 'tag' parameter in the URL
if (isset($_GET['tag'])) {
  $tag = $_GET['tag'];

  // Modify the count query to consider the 'tag' parameter and the current week
  $query = $db->prepare("
    SELECT COUNT(*) 
    FROM images 
    JOIN users ON images.email = users.email 
    LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfMonth AND :endOfMonth 
    WHERE users.id = :id AND images.tags LIKE :tagPattern
  ");
  $query->bindParam(':id', $id);
  $query->bindValue(':tagPattern', "%$tag%", PDO::PARAM_STR);
  $query->bindParam(':startOfMonth', $startOfMonth);
  $query->bindParam(':endOfMonth', $endOfMonth);
  $query->execute();
  $total = $query->fetchColumn();

  // Modify the fetch query to sort by daily.views
  $stmt = $db->prepare("
    SELECT images.*, COALESCE(daily.views, 0) AS views 
    FROM images 
    JOIN users ON images.email = users.email 
    LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfMonth AND :endOfMonth 
    WHERE users.id = :id AND images.tags LIKE :tagPattern 
    ORDER BY views DESC, images.id DESC 
    LIMIT :limit OFFSET :offset
  ");
  $stmt->bindParam(':id', $id);
  $stmt->bindValue(':tagPattern', "%$tag%", PDO::PARAM_STR);
  $stmt->bindParam(':startOfMonth', $startOfMonth);
  $stmt->bindParam(':endOfMonth', $endOfMonth);
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
} else {
  // If the 'tag' parameter is not present, retrieve all images for the current week
  $query = $db->prepare("
    SELECT COUNT(*) 
    FROM images 
    JOIN users ON images.email = users.email 
    LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfMonth AND :endOfMonth 
    WHERE users.id = :id
  ");
  $query->bindParam(':id', $id);
  $query->bindParam(':startOfMonth', $startOfMonth);
  $query->bindParam(':endOfMonth', $endOfMonth);
  $query->execute();
  $total = $query->fetchColumn();

  // Fetch images and sort by daily.views
  $stmt = $db->prepare("
    SELECT images.*, COALESCE(daily.views, 0) AS views 
    FROM images 
    JOIN users ON images.email = users.email 
    LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfMonth AND :endOfMonth 
    WHERE users.id = :id 
    ORDER BY views DESC, images.id DESC 
    LIMIT :limit OFFSET :offset
  ");
  $stmt->bindParam(':id', $id);
  $stmt->bindParam(':startOfMonth', $startOfMonth);
  $stmt->bindParam(':endOfMonth', $endOfMonth);
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
}

if ($stmt->execute()) {
  // Fetch the results as an associative array
  $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  // Handle the query execution error
  echo "Error executing the query.";
}
?>

    <?php include('image_card_art_tagged_month.php'); ?>