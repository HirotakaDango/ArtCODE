<?php include('header_profile_desc.php'); ?>
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

// Get the total number of images for the current user
$query = $db->prepare("SELECT COUNT(*) FROM images WHERE email = :email");
$query->bindValue(':email', $email);
if ($query->execute()) {
  $total = $query->fetchColumn();
} else {
  // Handle the query execution error
  echo "Error executing the query.";
}

// Get all of the images uploaded by the current user
$stmt = $db->prepare("SELECT * FROM images WHERE email = :email ORDER BY id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':email', $email);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT); // Use PDO constant for integer
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT); // Use PDO constant for integer
if ($stmt->execute()) {
  // Fetch the results as an associative array
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  // Handle the query execution error
  echo "Error executing the query.";
}
?>

    <?php include('image_card_pro_desc.php'); ?>