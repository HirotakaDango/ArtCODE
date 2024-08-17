<?php include('header_profile_like.php'); ?>
<?php
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

  // Modify your SQL queries to retrieve images with tags that contain the specified tag
  $query = $db->prepare("
    SELECT COUNT(DISTINCT images.id) 
    FROM images 
    INNER JOIN favorites ON images.id = favorites.image_id 
    WHERE favorites.email = :email AND tags LIKE :tagPattern
  ");
  $query->bindValue(':email', $email);
  $query->bindValue(':tagPattern', "%$tag%", PDO::PARAM_STR);
  if ($query->execute()) {
    $total = $query->fetchColumn();
  } else {
    // Handle the query execution error
    echo "Error executing the query.";
  }

  $stmt = $db->prepare("
      SELECT DISTINCT images.* 
      FROM images 
      LEFT JOIN favorites ON images.id = favorites.image_id AND favorites.email = :email 
      WHERE images.id IN (
        SELECT image_id
        FROM favorites
        WHERE email = :email
      ) 
      AND images.tags LIKE :tagPattern 
      ORDER BY images.id DESC 
      LIMIT :limit OFFSET :offset
  ");
  $stmt->bindValue(':email', $email);
  $stmt->bindValue(':tagPattern', "%$tag%", PDO::PARAM_STR);
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();

} else {
  // If the 'tag' parameter is not present, retrieve all images
  $query = $db->prepare("
    SELECT COUNT(DISTINCT images.id) 
    FROM images 
    INNER JOIN favorites ON images.id = favorites.image_id 
    WHERE favorites.email = :email
  ");
  $query->bindValue(':email', $email);
  if ($query->execute()) {
    $total = $query->fetchColumn();
  } else {
    // Handle the query execution error
    echo "Error executing the query.";
  }

  $stmt = $db->prepare("
    SELECT images.*, favorites.id AS favorite_id
    FROM images
    LEFT JOIN favorites ON images.id = favorites.image_id AND favorites.email = :email
    WHERE favorites.email IS NOT NULL
    ORDER BY images.id DESC
    LIMIT :limit OFFSET :offset
  ");
  $stmt->bindValue(':email', $email);
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

    <?php include('image_card_pro_tagged_like.php'); ?>