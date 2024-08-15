<?php
// Get the current date
$currentDate = new DateTime();

// Calculate the start and end dates of the current year
$startOfYear = $currentDate->modify('first day of January this year')->format('Y-m-d');
$endOfYear = $currentDate->modify('last day of December this year')->format('Y-m-d');

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

// Prepare and execute the query to get the favorited images for the current year
$query = $db->prepare("
  SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(SUM(daily.views), 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  JOIN favorites ON images.id = favorites.image_id
  LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfYear AND :endOfYear
  WHERE favorites.email = :email
  GROUP BY images.id, users.artist, users.pic, users.id
  ORDER BY views DESC, images.id DESC
  LIMIT :limit OFFSET :offset
");

$query->bindParam(':startOfYear', $startOfYear);
$query->bindParam(':endOfYear', $endOfYear);
$query->bindParam(':email', $email);
$query->bindParam(':limit', $limit, PDO::PARAM_INT);
$query->bindParam(':offset', $offset, PDO::PARAM_INT);
$query->execute();
$favorite_images = $query->fetchAll(PDO::FETCH_ASSOC);
?>

    <?php if (count($favorite_images) > 0): ?>
      <?php include('image_card_favorites.php') ?>
    <?php else: ?>
      <div class='container'>
        <p class="text-secondary text-center fw-bold">Oops... sorry, no favorited images!</p>
        <p class='text-secondary text-center fw-bold'>The one that makes sense is, this user hasn't favorited any image...</p>
        <img src='/icon/Empty.svg' style='width: 100%; height: 100%;'>
      </div>
    <?php endif; ?>