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

// Get the total number of images for the selected user
$query = $db->prepare('SELECT COUNT(*) FROM images JOIN users ON images.email = users.email WHERE users.id = :id');
$query->bindParam(':id', $id);
$query->execute();
$total = $query->fetchColumn();

// Get all images for the selected user, sorted by yearly views
$query = $db->prepare("
  SELECT images.*, COALESCE(SUM(daily.views), 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfYear AND :endOfYear
  WHERE users.id = :id
  GROUP BY images.id
  ORDER BY views DESC, images.id DESC
  LIMIT :limit OFFSET :offset
");
$query->bindParam(':id', $id);
$query->bindParam(':startOfYear', $startOfYear);
$query->bindParam(':endOfYear', $endOfYear);
$query->bindValue(':limit', $limit, PDO::PARAM_INT);
$query->bindValue(':offset', $offset, PDO::PARAM_INT);
$query->execute();
$images = $query->fetchAll(PDO::FETCH_ASSOC);
?>

    <?php include('image_card_art_year.php'); ?>