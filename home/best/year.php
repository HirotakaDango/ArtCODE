<?php
// Get the current date
$currentDate = date('Y-m-d');

// Calculate the first and last day of the current year
$startOfYear = date('Y-01-01'); // First day of the year
$endOfYear = date('Y-12-31');   // Last day of the year

// Adjust the query to sum views for the current year
$query = "
  SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(SUM(daily.views), 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id 
  AND daily.date BETWEEN :startOfYear AND :endOfYear
  GROUP BY images.id
  ORDER BY views DESC, images.id DESC
  LIMIT 20
";
$stmt = $db->prepare($query);
$stmt->bindValue(':startOfYear', $startOfYear, SQLITE3_TEXT);
$stmt->bindValue(':endOfYear', $endOfYear, SQLITE3_TEXT);
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
    <div class="container px-0">
      <div id="imageCarouselUser" class="carousel slide" data-bs-ride="carousel">
        <h5 class="fw-bold text-white" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);">Popular Images This Year</h5>
        <h6 class="text-white fw-bold small" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4);">
          These images are displayed based on their view counts from this <?php echo isset($_GET['time']) ? $_GET['time'] : 'day'; ?>. The more views an image has, the higher its ranking in this list.
        </h6>
        <div class="carousel-inner">
          <?php
          $totalImages = count($images);
          $slidesCount = ceil($totalImages / 5);
          
          for ($i = 0; $i < $slidesCount; $i++) :
            $startIndex = $i * 5;
            $endIndex = min($startIndex + 5, $totalImages);
          ?>
            <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>" data-bs-interval="false">
              <div class="row row-cols-5 g-1">
                <?php for ($j = $startIndex; $j < $endIndex; $j++) :
                  $imageU = $images[$j];
                  $image_id = $imageU['id'];
                  $image_url = $imageU['filename'];
                  $image_title = $imageU['title'];
                  $artist_name = htmlspecialchars($imageU['artist']);
                  $user_id = $imageU['user_id'];
                  $userPic = $imageU['pic'];
                  $views = $imageU['views']; // Get the views count
                  $current_image_id = isset($_GET['artworkid']) ? $_GET['artworkid'] : null;
                  
                  $artist_name = substr($artist_name, 0, 10);
                  $image_title = substr($image_title, 0, 20);
                ?>
                  <div class="col position-relative">
                    <div class="position-relative">
                      <div class="position-relative">
                        <a href="/image.php?artworkid=<?php echo $image_id; ?>">
                          <div class="ratio ratio-1x1">
                            <img class="object-fit-cover rounded rounded-bottom-0" src="/thumbnails/<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($image_title); ?>" style="object-fit: cover;">
                          </div>
                        </a>
                        <span class="position-absolute bottom-0 end-0 m-2 badge" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4);"><?php echo $views; ?> views</span>
                      </div>
                      <div class="d-flex align-items-center p-2 bg-light rounded-bottom mx-0 text-dark">
                        <img class="rounded-circle object-fit-cover border border-1" width="40" height="40" src="/<?php echo !empty($userPic) ? $userPic : 'icon/profile.svg'; ?>" alt="Profile Picture" style="margin-top: -2px;">
                        <div class="ms-2">
                          <div class="fw-bold"><?php echo $image_title; ?></div>
                          <a class="fw-medium text-decoration-none text-dark small" href="#" type="button" data-bs-toggle="modal" data-bs-target="#userModalBest-<?php echo $user_id; ?>"><?php echo $artist_name; ?></a>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="modal fade" id="userModalBest-<?php echo $user_id; ?>" tabindex="-1" aria-labelledby="userModalLabelBest" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content bg-transparent border-0">
                        <div class="modal-body position-relative">
                          <a class="position-absolute top-0 end-0 m-4 text-white" href="/artist.php?id=<?php echo urlencode($user_id); ?>" target="_blank">
                            <i class="bi bi-box-arrow-up-right link-body-emphasis" style="-webkit-text-stroke: 1px; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); text-stroke: 2;"></i>
                          </a>
                          <iframe src="/rows_columns/user_preview.php?id=<?php echo urlencode($user_id); ?>" class="rounded-4 p-0 shadow" width="100%" height="275" style="border: none;"></iframe>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endfor; ?>
              </div>
            </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>
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