<?php
// Get the current date
$currentDate = date('Y-m-d');

// Get the start of the week (Monday) and end of the week (Sunday)
$startOfWeek = date('Y-m-d', strtotime('monday this week'));
$endOfWeek = date('Y-m-d', strtotime('sunday this week'));

// Adjust the query to sum views for the current week
$query = "
  SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(SUM(daily.views), 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id 
  AND daily.date BETWEEN :startOfWeek AND :endOfWeek
  GROUP BY images.id
  ORDER BY views DESC, images.id DESC
  LIMIT 20
";
$stmt = $db->prepare($query);
$stmt->bindValue(':startOfWeek', $startOfWeek, SQLITE3_TEXT);
$stmt->bindValue(':endOfWeek', $endOfWeek, SQLITE3_TEXT);
$result = $stmt->execute();

$images = array();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  $images[] = $row;
}

// Get the latest image for the background
$latestImage = $images[0]['filename'];
$backgroundImageUrl = "/images/" . $latestImage; // Adjust this path if needed

// Get the artist information for the latest image
$latestArtistName = htmlspecialchars($images[0]['artist']);
$latestArtistId = htmlspecialchars($images[0]['user_id']);
?>

<div id="carouselWrapper" class="mb-3 position-relative" style="height: 92vh; width: 100%;">
  <!-- Background Image Div -->
  <div class="position-absolute top-0 start-0 w-100 h-100" style="background-image: url('<?php echo $backgroundImageUrl; ?>'); background-size: cover; background-position: center; filter: blur(8px); z-index: -1;"></div>
  
  <!-- Carousel Div -->
  <div class="position-relative w-100 h-100 d-flex align-items-center justify-content-center">
  
    <?php include('image_card_best.php'); ?>
    <!-- Custom Navigation Buttons -->
    <button class="carousel-control-prev position-absolute top-50 start-0 translate-middle-y ms-4" type="button" data-bs-target="#imageCarouselUser" data-bs-slide="prev" style="background: rgba(0, 0, 0, 0.5); border: none; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
      <i class="bi bi-chevron-left text-white" style="font-size: 24px;"></i>
    </button>
    <button class="carousel-control-next position-absolute top-50 end-0 translate-middle-y me-4" type="button" data-bs-target="#imageCarouselUser" data-bs-slide="next" style="background: rgba(0, 0, 0, 0.5); border: none; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
      <i class="bi bi-chevron-right text-white" style="font-size: 24px;"></i>
    </button>
  </div>
  <h6 class="position-absolute end-0 bottom-0 m-3 text-white" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); text-stroke: 2;">
    image by <a class="text-white text-decoration-none" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);" href="/artist.php?id=<?php echo urlencode($latestArtistId); ?>"><?php echo $latestArtistName; ?></a>
  </h6>
</div>