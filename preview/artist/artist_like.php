<?php include('header_artist_like.php'); ?>
<?php
// Set the limit of images per page
$limit = 12;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Get the total number of images for the selected user
$query = $db->prepare('
  SELECT COUNT(*) 
  FROM images 
  JOIN users ON images.email = users.email 
  LEFT JOIN favorites ON images.id = favorites.image_id AND favorites.email = :email
  WHERE users.id = :id AND favorites.id IS NOT NULL
');
$query->bindParam(':email', $email);
$query->bindParam(':id', $id);
$query->execute();
$total = $query->fetchColumn();

// Get all images for the selected user from the images table
$query = $db->prepare('
  SELECT images.* 
  FROM images 
  JOIN users ON images.email = users.email 
  LEFT JOIN favorites ON images.id = favorites.image_id AND favorites.email = :email
  WHERE users.id = :id AND favorites.id IS NOT NULL
  ORDER BY images.id DESC 
  LIMIT :limit OFFSET :offset
');
$query->bindParam(':id', $id);
$query->bindParam(':email', $email);
$query->bindValue(':limit', $limit, PDO::PARAM_INT);
$query->bindValue(':offset', $offset, PDO::PARAM_INT);
$query->execute();
$images = $query->fetchAll(PDO::FETCH_ASSOC);
?>

    <?php include('image_card_art_like.php'); ?>