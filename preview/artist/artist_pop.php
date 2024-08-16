<?php include('header_artist_pop.php'); ?>
<?php
// Set the limit of images per page
$limit = 12;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Execute the initial query to get images with favorite counts
$query = $db->prepare('SELECT images.*, COUNT(favorites.id) AS favorite_count FROM images JOIN users ON images.email = users.email LEFT JOIN favorites ON images.id = favorites.image_id WHERE users.id = :id GROUP BY images.id ORDER BY favorite_count DESC');
$query->bindParam(':id', $id);
$query->execute();
$imagesWithFavorites = $query->fetchAll(PDO::FETCH_ASSOC);

// Get the total number of images for the selected user from the images with favorites result
$total = count($imagesWithFavorites);

// Get a subset of images based on the offset and limit
$images = array_slice($imagesWithFavorites, $offset, $limit);
?>

    <?php include('image_card_art_pop.php'); ?>