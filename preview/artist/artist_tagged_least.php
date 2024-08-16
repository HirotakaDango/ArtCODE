<?php include('header_artist_least.php'); ?>
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

  // Modify your SQL queries to retrieve images with tags that contain the specified tag
  $query = $db->prepare("SELECT COUNT(*) FROM images JOIN users ON images.email = users.email WHERE users.id = :id AND images.tags LIKE :tagPattern");
  $query->bindParam(':id', $id);
  $query->bindValue(':tagPattern', "%$tag%", PDO::PARAM_STR);
  if ($query->execute()) {
    $total = $query->fetchColumn();
  } else {
    // Handle the query execution error
    echo "Error executing the query.";
  }

  $stmt = $db->prepare("SELECT images.* FROM images JOIN users ON images.email = users.email WHERE users.id = :id AND images.tags LIKE :tagPattern ORDER BY images.view_count ASC LIMIT :limit OFFSET :offset");
  $stmt->bindParam(':id', $id);
  $stmt->bindValue(':tagPattern', "%$tag%", PDO::PARAM_STR);
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
} else {
  // If the 'tag' parameter is not present, retrieve all images
  $query = $db->prepare("SELECT COUNT(*) FROM images JOIN users ON images.email = users.email WHERE users.id = :id");
  $query->bindParam(':id', $id);
  if ($query->execute()) {
    $total = $query->fetchColumn();
  } else {
    // Handle the query execution error
    echo "Error executing the query.";
  }

  $stmt = $db->prepare("SELECT images.* FROM images JOIN users ON images.email = users.email WHERE users.id = :id ORDER BY images.view_count ASC LIMIT :limit OFFSET :offset");
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

    <?php include('image_card_art_tagged_least.php'); ?>