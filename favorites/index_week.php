<?php
// Get the current date in YYYY-MM-DD format
$currentDate = new DateTime();
$currentDate->setISODate((int)$currentDate->format('Y'), (int)$currentDate->format('W')); // Set to Monday of the current week
$startOfWeek = $currentDate->format('Y-m-d');
$currentDate->modify('next Sunday'); // Move to the end of the week
$endOfWeek = $currentDate->format('Y-m-d');

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

// Prepare and execute the query to get the images for the current week
$query = $db->prepare("
  SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(SUM(daily.views), 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfWeek AND :endOfWeek
  GROUP BY images.id, users.artist, users.pic, users.id
  ORDER BY views DESC, images.id DESC
  LIMIT :limit OFFSET :offset
");

$query->bindParam(':startOfWeek', $startOfWeek);
$query->bindParam(':endOfWeek', $endOfWeek);
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
        <p class='text-secondary text-center fw-bold'>The one that makes sense is, this user hasn't favorited any image...</p>
        <img src='/icon/Empty.svg' style='width: 100%; height: 100%;'>
      </div>
    <?php endif; ?>