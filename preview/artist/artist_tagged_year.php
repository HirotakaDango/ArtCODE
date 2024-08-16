<?php include('header_artist_year.php'); ?>
<?php
// Get the current date in YYYY-MM-DD format
$currentDate = date('Y-m-d');

// Calculate the start and end of the current year
$startOfYear = date('Y-01-01'); // First day of the current year
$endOfYear = date('Y-12-31');   // Last day of the current year

// Set the limit of images per page
$limit = 12;

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
    LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfYear AND :endOfYear 
    WHERE users.id = :id AND images.tags LIKE :tagPattern
  ");
  $query->bindParam(':id', $id);
  $query->bindValue(':tagPattern', "%$tag%", PDO::PARAM_STR);
  $query->bindParam(':startOfYear', $startOfYear);
  $query->bindParam(':endOfYear', $endOfYear);
  $query->execute();
  $total = $query->fetchColumn();

  // Modify the fetch query to sort by daily.views
  $stmt = $db->prepare("
    SELECT images.*, COALESCE(daily.views, 0) AS views 
    FROM images 
    JOIN users ON images.email = users.email 
    LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfYear AND :endOfYear 
    WHERE users.id = :id AND images.tags LIKE :tagPattern 
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
  // If the 'tag' parameter is not present, retrieve all images for the current week
  $query = $db->prepare("
    SELECT COUNT(*) 
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

  // Fetch images and sort by daily.views
  $stmt = $db->prepare("
    SELECT images.*, COALESCE(daily.views, 0) AS views 
    FROM images 
    JOIN users ON images.email = users.email 
    LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfYear AND :endOfYear 
    WHERE users.id = :id 
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