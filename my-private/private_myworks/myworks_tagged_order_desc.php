<?php
// Prepare the query to get the user's numpage
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT); // Assuming $email is the email you want to search for
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

  // Count the total number of private_images with the specified tag
  $query = $db->prepare("SELECT COUNT(*) FROM private_images WHERE email = :email AND tags LIKE :tagPattern");
  $query->bindValue(':email', $email, SQLITE3_TEXT);
  $query->bindValue(':tagPattern', "%$tag%", SQLITE3_TEXT);
  $total = $query->execute()->fetchArray()[0];

  // Retrieve private_images with the specified tag
  $stmt = $db->prepare("SELECT * FROM private_images WHERE email = :email AND tags LIKE :tagPattern ORDER BY title DESC LIMIT :limit OFFSET :offset");
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':tagPattern', "%$tag%", SQLITE3_TEXT);
  $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
  $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);

} else {
  // Count the total number of private_images without tag filter
  $query = $db->prepare("SELECT COUNT(*) FROM private_images WHERE email = :email");
  $query->bindValue(':email', $email, SQLITE3_TEXT);
  $total = $query->execute()->fetchArray()[0];

  // Retrieve all private_images without tag filter
  $stmt = $db->prepare("SELECT * FROM private_images WHERE email = :email ORDER BY title DESC LIMIT :limit OFFSET :offset");
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
  $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
}

$result = $stmt->execute();
?>

    <?php include('image_card_myworks.php'); ?>