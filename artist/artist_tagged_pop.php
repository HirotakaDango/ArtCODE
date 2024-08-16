<?php include('header_artist_pop.php'); ?>
<?php
// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Check for the 'tag' parameter in the URL
if (isset($_GET['tag'])) {
  $tag = $_GET['tag'];

  // Modify your SQL queries to retrieve images with tags that contain the specified tag
  $query = $db->prepare("SELECT COUNT(*) FROM images JOIN users ON images.email = users.email WHERE users.id = :id AND images.tags LIKE :tagPattern");
  $query->bindParam(':id', $id);
  $query->bindValue(':tagPattern', "%$tag%", PDO::PARAM_STR);
} else {
  // If the 'tag' parameter is not present, retrieve all images
  $query = $db->prepare("SELECT COUNT(*) FROM images JOIN users ON images.email = users.email WHERE users.id = :id");
  $query->bindParam(':id', $id);
}

if ($query->execute()) {
  $total = $query->fetchColumn();
} else {
  // Handle the query execution error
  echo "Error executing the query.";
}

$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindParam(':email', $email, PDO::PARAM_STR);
$queryNum->execute();
$user = $queryNum->fetch(PDO::FETCH_ASSOC);

$numpage = $user['numpage'];

// Set the limit of images per page
$limit = empty($numpage) ? 50 : $numpage;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

if (isset($_GET['tag'])) {
  $stmt = $db->prepare("SELECT images.*, COUNT(favorites.id) AS favorite_count FROM images JOIN users ON images.email = users.email LEFT JOIN favorites ON images.id = favorites.image_id WHERE users.id = :id AND images.tags LIKE :tagPattern GROUP BY images.id ORDER BY favorite_count DESC LIMIT :limit OFFSET :offset");
  $stmt->bindParam(':id', $id);
  $stmt->bindValue(':tagPattern', "%$tag%", PDO::PARAM_STR);
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
} else {
  $stmt = $db->prepare("SELECT images.*, COUNT(favorites.id) AS favorite_count FROM images JOIN users ON images.email = users.email LEFT JOIN favorites ON images.id = favorites.image_id WHERE users.id = :id GROUP BY images.id ORDER BY favorite_count DESC LIMIT :limit OFFSET :offset");
  $stmt->bindParam(':id', $id);
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

    <?php include('image_card_art_tagged_pop.php'); ?>