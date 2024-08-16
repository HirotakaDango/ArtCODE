<?php include('header_artist_like.php'); ?>
<?php
// Set the limit of images per page
$limit = 12;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Check for the 'tag' parameter in the URL
if (isset($_GET['tag'])) {
  $tag = $_GET['tag'];

  // Query to count the total number of favorited images with the specified tag
  $query = $db->prepare("
    SELECT COUNT(DISTINCT images.id)
    FROM images
    JOIN users ON images.email = users.email
    JOIN favorites ON images.id = favorites.image_id AND favorites.email = :email
    WHERE users.id = :id AND images.tags LIKE :tagPattern
  ");
  $query->bindParam(':id', $id);
  $query->bindParam(':email', $email);
  $query->bindValue(':tagPattern', "%$tag%", PDO::PARAM_STR);
  if ($query->execute()) {
    $total = $query->fetchColumn();
  } else {
    // Handle the query execution error
    echo "Error executing the query.";
  }

  // Query to retrieve favorited images with the specified tag
  $stmt = $db->prepare("
    SELECT images.*
    FROM images
    JOIN users ON images.email = users.email
    JOIN favorites ON images.id = favorites.image_id AND favorites.email = :email
    WHERE users.id = :id AND images.tags LIKE :tagPattern
    ORDER BY images.id DESC
    LIMIT :limit OFFSET :offset
  ");
  $stmt->bindParam(':id', $id);
  $stmt->bindParam(':email', $email);
  $stmt->bindValue(':tagPattern', "%$tag%", PDO::PARAM_STR);
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
} else {
  // Query to count the total number of favorited images
  $query = $db->prepare("
    SELECT COUNT(DISTINCT images.id)
    FROM images
    JOIN users ON images.email = users.email
    JOIN favorites ON images.id = favorites.image_id AND favorites.email = :email
    WHERE users.id = :id
  ");
  $query->bindParam(':id', $id);
  $query->bindParam(':email', $email);
  if ($query->execute()) {
    $total = $query->fetchColumn();
  } else {
    // Handle the query execution error
    echo "Error executing the query.";
  }

  // Query to retrieve favorited images
  $stmt = $db->prepare("
    SELECT images.*
    FROM images
    JOIN users ON images.email = users.email
    JOIN favorites ON images.id = favorites.image_id AND favorites.email = :email
    WHERE users.id = :id
    ORDER BY images.id DESC
    LIMIT :limit OFFSET :offset
  ");
  $stmt->bindParam(':id', $id);
  $stmt->bindParam(':email', $email);
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

    <?php include('image_card_art_tagged_like.php'); ?>