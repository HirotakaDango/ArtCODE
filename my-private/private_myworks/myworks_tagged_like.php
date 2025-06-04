<?php
// Prepare the query to get the user's numpage
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT);
$resultNum = $queryNum->execute();
$user = $resultNum->fetchArray(SQLITE3_ASSOC);

$numpage = $user['numpage'];

// Set the limit of private_images per page
$limit = empty($numpage) ? 50 : $numpage;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Check for the 'tag' parameter in the URL
if (isset($_GET['tag'])) {
  $tag = $_GET['tag'];

  // Count the number of distinct private_images with the specified tag in private_favorites
  $query = $db->prepare("
    SELECT COUNT(DISTINCT private_images.id) 
    FROM private_images 
    INNER JOIN private_favorites ON private_images.id = private_favorites.image_id 
    WHERE private_favorites.email = :email AND tags LIKE :tagPattern
  ");
  $query->bindValue(':email', $email, SQLITE3_TEXT);
  $query->bindValue(':tagPattern', "%$tag%", SQLITE3_TEXT);
  $total = $query->execute()->fetchArray()[0];

  // Retrieve distinct private_images with the specified tag and in private_favorites
  $stmt = $db->prepare("
    SELECT DISTINCT private_images.* 
    FROM private_images 
    LEFT JOIN private_favorites ON private_images.id = private_favorites.image_id AND private_favorites.email = :email 
    WHERE private_images.id IN (
      SELECT image_id
      FROM private_favorites
      WHERE email = :email
    ) 
    AND private_images.tags LIKE :tagPattern 
    ORDER BY private_images.id DESC 
    LIMIT :limit OFFSET :offset
  ");
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':tagPattern', "%$tag%", SQLITE3_TEXT);
  $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
  $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);

} else {
  // Count the number of distinct private_images in private_favorites
  $query = $db->prepare("
    SELECT COUNT(DISTINCT private_images.id) 
    FROM private_images 
    INNER JOIN private_favorites ON private_images.id = private_favorites.image_id 
    WHERE private_favorites.email = :email
  ");
  $query->bindValue(':email', $email, SQLITE3_TEXT);
  $total = $query->execute()->fetchArray()[0];

  // Retrieve private_images in private_favorites
  $stmt = $db->prepare("
    SELECT private_images.*, private_favorites.id AS favorite_id
    FROM private_images
    LEFT JOIN private_favorites ON private_images.id = private_favorites.image_id AND private_favorites.email = :email
    WHERE private_favorites.email IS NOT NULL
    ORDER BY private_images.id DESC
    LIMIT :limit OFFSET :offset
  ");
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
  $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
}

$result = $stmt->execute();
?>

    <?php include('image_card_myworks.php'); ?>