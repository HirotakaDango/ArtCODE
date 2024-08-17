<?php include('header_profile_daily.php'); ?>
<?php
// Get the current date in YYYY-MM-DD format
$currentDate = date('Y-m-d');

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

// Check for the 'tag' parameter in the URL
if (isset($_GET['tag'])) {
  $tag = $_GET['tag'];

  // Count the total number of tagged images
  $query = $db->prepare("
    SELECT COUNT(*) 
    FROM images 
    WHERE email = :email AND tags LIKE :tagPattern
  ");
  $query->bindValue(':email', $email);
  $query->bindValue(':tagPattern', "%$tag%", PDO::PARAM_STR);
  if ($query->execute()) {
    $total = $query->fetchColumn();
  } else {
    // Handle the query execution error
    echo "Error executing the query.";
  }

  // Retrieve tagged images sorted by daily views
  $stmt = $db->prepare("
    SELECT images.*, COALESCE(daily.views, 0) AS views
    FROM images
    LEFT JOIN daily ON images.id = daily.image_id AND daily.date = :currentDate
    WHERE images.email = :email AND images.tags LIKE :tagPattern
    ORDER BY views DESC, images.id DESC
    LIMIT :limit OFFSET :offset
  ");
  $stmt->bindValue(':email', $email);
  $stmt->bindValue(':tagPattern', "%$tag%", PDO::PARAM_STR);
  $stmt->bindParam(':currentDate', $currentDate);
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

} else {
  // Count the total number of images
  $query = $db->prepare("
    SELECT COUNT(*)
    FROM images
    WHERE email = :email
  ");
  $query->bindValue(':email', $email);
  if ($query->execute()) {
    $total = $query->fetchColumn();
  } else {
    // Handle the query execution error
    echo "Error executing the query.";
  }

  // Retrieve all images sorted by daily views
  $stmt = $db->prepare("
    SELECT images.*, COALESCE(daily.views, 0) AS views
    FROM images
    LEFT JOIN daily ON images.id = daily.image_id AND daily.date = :currentDate
    WHERE images.email = :email
    ORDER BY views DESC, images.id DESC
    LIMIT :limit OFFSET :offset
  ");
  $stmt->bindValue(':email', $email);
  $stmt->bindParam(':currentDate', $currentDate);
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
}

if ($stmt->execute()) {
  // Fetch the results as an associative array
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  // Handle the query execution error
  echo "Error executing the query.";
}
?>

    <?php include('image_card_pro_tagged_daily.php'); ?>