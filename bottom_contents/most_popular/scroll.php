<?php
require_once('../../auth.php');

// Connect to the SQLite database using parameterized query
$dbP = new SQLite3('../../database.sqlite');

$emailP = $_SESSION['email'];

// Retrieve offset from POST parameters
$offsetP = isset($_POST['offset']) ? intval($_POST['offset']) : 0;

// Prepare the query to get the user's numpage
$queryNumP = $dbP->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNumP->bindValue(':email', $emailP, SQLITE3_TEXT); // Assuming $email is the email you want to search for
$resultNumP = $queryNumP->execute();
$userP = $resultNumP->fetchArray(SQLITE3_ASSOC);

$numpageP = $userP['numpage'];

// Set the limit of images per page
$limitP = empty($numpageP) ? 50 : $numpageP;

// Prepare query to fetch images with LIMIT and OFFSET
$stmtP = $dbP->prepare("SELECT images.*, COUNT(favorites.id) AS favorite_count FROM images LEFT JOIN favorites ON images.id = favorites.image_id GROUP BY images.id ORDER BY favorite_count DESC LIMIT :limit OFFSET :offset");
$stmtP->bindValue(':limit', $limitP, SQLITE3_INTEGER);
$stmtP->bindValue(':offset', $offsetP, SQLITE3_INTEGER);
$resultP = $stmtP->execute();

// Generate HTML for the fetched images
ob_start();
while ($imageP = $resultP->fetchArray()) {
?>

    <div class="col">
      <div class="position-relative">
        <a class="rounded ratio ratio-1x1" href="/image.php?artworkid=<?= $imageP['id']; ?>">
          <img class="rounded shadow object-fit-cover" src="/thumbnails/<?= $imageP['filename']; ?>" alt="<?= $imageP['title']; ?>">
        </a>
        <?php
          $current_image_idP = $imageP['id'];
          
          // Query to count main image from the images table
          $stmtCountP = $dbP->prepare("SELECT COUNT(*) as image_count FROM images WHERE id = :id");
          $stmtCountP->bindValue(':id', $current_image_idP, SQLITE3_INTEGER);
          $imageCountQueryP = $stmtCountP->execute();
          if ($imageCountQueryP) {
            $imageCountRowP = $imageCountQueryP->fetchArray(SQLITE3_ASSOC);
            $imageCountP = $imageCountRowP ? $imageCountRowP['image_count'] : 0;
          } else {
            $imageCountP = 0;
          }
        
          // Query to count associated images from the image_child table
          $stmtChildP = $dbP->prepare("SELECT COUNT(*) as child_image_count FROM image_child WHERE image_id = :image_id");
          $stmtChildP->bindValue(':image_id', $current_image_idP, SQLITE3_INTEGER);
          $childImageCountQueryP = $stmtChildP->execute();
          if ($childImageCountQueryP) {
            $childImageCountRowP = $childImageCountQueryP->fetchArray(SQLITE3_ASSOC);
            $childImageCountP = $childImageCountRowP ? $childImageCountRowP['child_image_count'] : 0;
          } else {
            $childImageCountP = 0;
          }
        
          // Total count of main images and associated images
          $totalImagesCount = $imageCountP + $childImageCountP;
        ?>
        <?php include('../../rows_columns/image_counts_prev_artwork.php'); ?>
        <div class="position-absolute top-0 end-0">
          <div class="dropdown">
            <button class="btn border-0 p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-three-dots-vertical text-white link-body-emphasis fs-5" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); text-stroke: 2;"></i>
            </button>
            <ul class="dropdown-menu">
              <?php
                $is_favoritedP = $dbP->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$emailP' AND image_id = {$imageP['id']}");
                if ($is_favoritedP) {
              ?>
                <form class="favoriteFormP">
                  <input type="hidden" name="image_id" value="<?= $imageP['id']; ?>">
                  <input type="hidden" name="action" value="unfavorite">
                  <li><button type="submit" class="dropdown-item fw-bold unfavoriteBtnP"><i class="bi bi-heart-fill"></i> <small>unfavorite</small></button></li>
                </form>
              <?php } else { ?>
                <form class="favoriteFormP">
                  <input type="hidden" name="image_id" value="<?= $imageP['id']; ?>">
                  <input type="hidden" name="action" value="favorite">
                  <li><button type="submit" class="dropdown-item fw-bold favoriteBtnP"><i class="bi bi-heart"></i> <small>favorite</small></button></li>
                </form>
              <?php } ?>
              <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#shareImage<?php echo $imageP['id']; ?>"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
              <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $imageP['id']; ?>"><i class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
            </ul>
            <?php include('share_most_popular.php'); ?>
            
            <?php include('card_image_most_popular.php'); ?>

          </div>
        </div>
      </div>
    </div>
    <?php
    }
    $htmlP = ob_get_clean();
    
    // Output the generated HTML
    echo $htmlP;
    ?>