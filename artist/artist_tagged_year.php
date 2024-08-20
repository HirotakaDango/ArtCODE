<?php include('header_artist_year.php'); ?>
<?php
// Get the current date in YYYY-MM-DD format
$currentDate = date('Y-m-d');

// Calculate the start and end of the current year
$startOfYear = date('Y-01-01');
$endOfYear = date('Y-12-31');

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

  // Count the total number of tagged images for the current year
  $query = $db->prepare("
    SELECT COUNT(DISTINCT images.id) 
    FROM images 
    JOIN users ON images.email = users.email 
    LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfYear AND :endOfYear 
    WHERE users.id = :id AND images.tags LIKE :tagPattern
  ");
  $query->bindParam(':id', $id);
  $query->bindValue(':tagPattern', "%$tag%", PDO::PARAM_STR);
  $query->bindParam(':startOfYear', $startOfYear);
  $query->bindParam(':endOfYear', $endOfYear);
  $query->execute();
  $total = $query->fetchColumn();

  // Retrieve tagged images for the current year, aggregated by views
  $stmt = $db->prepare("
    SELECT images.*, COALESCE(SUM(daily.views), 0) AS views 
    FROM images 
    JOIN users ON images.email = users.email 
    LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfYear AND :endOfYear 
    WHERE users.id = :id AND images.tags LIKE :tagPattern 
    GROUP BY images.id
    ORDER BY views DESC, images.id DESC 
    LIMIT :limit OFFSET :offset
  ");
  $stmt->bindParam(':id', $id);
  $stmt->bindValue(':tagPattern', "%$tag%", PDO::PARAM_STR);
  $stmt->bindParam(':startOfYear', $startOfYear);
  $stmt->bindParam(':endOfYear', $endOfYear);
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

} else {
  // If the 'tag' parameter is not present, retrieve all images for the current year
  $query = $db->prepare("
    SELECT COUNT(DISTINCT images.id)
    FROM images 
    JOIN users ON images.email = users.email 
    LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfYear AND :endOfYear 
    WHERE users.id = :id
  ");
  $query->bindParam(':id', $id);
  $query->bindParam(':startOfYear', $startOfYear);
  $query->bindParam(':endOfYear', $endOfYear);
  $query->execute();
  $total = $query->fetchColumn();

  // Retrieve all images for the current year, aggregated by views
  $stmt = $db->prepare("
    SELECT images.*, COALESCE(SUM(daily.views), 0) AS views 
    FROM images 
    JOIN users ON images.email = users.email 
    LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfYear AND :endOfYear 
    WHERE users.id = :id 
    GROUP BY images.id
    ORDER BY views DESC, images.id DESC 
    LIMIT :limit OFFSET :offset
  ");
  $stmt->bindParam(':id', $id);
  $stmt->bindParam(':startOfYear', $startOfYear);
  $stmt->bindParam(':endOfYear', $endOfYear);
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

    <?php include('image_card_art_tagged_year.php'); ?>