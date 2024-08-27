<?php
// Prepare the query to get the user's numpage
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT);
$resultNum = $queryNum->execute();
$user = $resultNum->fetchArray(SQLITE3_ASSOC);

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

  // Count the number of distinct images with the specified tag in favorites
  $query = $db->prepare("
    SELECT COUNT(DISTINCT images.id) 
    FROM images 
    INNER JOIN favorites ON images.id = favorites.image_id 
    WHERE favorites.email = :email AND tags LIKE :tagPattern
  ");
  $query->bindValue(':email', $email, SQLITE3_TEXT);
  $query->bindValue(':tagPattern', "%$tag%", SQLITE3_TEXT);
  $total = $query->execute()->fetchArray()[0];

  // Retrieve distinct images with the specified tag and in favorites
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
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':tagPattern', "%$tag%", SQLITE3_TEXT);
  $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
  $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);

} else {
  // Count the number of distinct images in favorites
  $query = $db->prepare("
    SELECT COUNT(DISTINCT images.id) 
    FROM images 
    INNER JOIN favorites ON images.id = favorites.image_id 
    WHERE favorites.email = :email
  ");
  $query->bindValue(':email', $email, SQLITE3_TEXT);
  $total = $query->execute()->fetchArray()[0];

  // Retrieve images in favorites
  $stmt = $db->prepare("
    SELECT images.*, favorites.id AS favorite_id
    FROM images
    LEFT JOIN favorites ON images.id = favorites.image_id AND favorites.email = :email
    WHERE favorites.email IS NOT NULL
    ORDER BY images.id DESC
    LIMIT :limit OFFSET :offset
  ");
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
  $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
}

$result = $stmt->execute();
?>

    <?php include('image_card_myworks.php'); ?>