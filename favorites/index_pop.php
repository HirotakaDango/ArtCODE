<?php
// Prepare the query to get the user's numpage
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

// Get favorite images with pagination, sorted by popularity (view_count)
$query = $db->prepare('SELECT images.* FROM images LEFT JOIN favorites ON images.id = favorites.image_id GROUP BY images.id ORDER BY COUNT(favorites.id) DESC, images.view_count DESC LIMIT :limit OFFSET :offset');
$query->bindParam(':limit', $limit, PDO::PARAM_INT);
$query->bindParam(':offset', $offset, PDO::PARAM_INT);
$query->execute();
$favorite_images = $query->fetchAll(PDO::FETCH_ASSOC);
?>

    <?php if (count($favorite_images) > 0): ?>
      <?php include('image_card_favorites.php')?>
    <?php else: ?>
      <div class='container'>
        <p class="text-secondary text-center fw-bold">Oops... sorry, no favorited images!</p>
        <p class='text-secondary text-center fw-bold'>The one that make sense is, this user hasn't favorited any image...</p>
        <img src='/icon/Empty.svg' style='width: 100%; height: 100%;'>
      </div>
    <?php endif; ?>