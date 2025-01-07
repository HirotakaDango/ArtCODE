<?php
require_once('../../auth.php');

// Connect to the SQLite database using parameterized query
$dbL = new SQLite3('../../database.sqlite');

$emailL = $_SESSION['email'];

// Retrieve offset from POST parameters
$offsetL = isset($_POST['offset']) ? intval($_POST['offset']) : 0;

// Prepare the query to get the user's numpage
$queryNumL = $dbL->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNumL->bindValue(':email', $emailL, SQLITE3_TEXT); // Assuming $email is the email you want to search for
$resultNumL = $queryNumL->execute();
$userL = $resultNumL->fetchArray(SQLITE3_ASSOC);

$numpageL = $userL['numpage'];

// Set the limit of images per page
$limitL = empty($numpageL) ? 50 : $numpageL;

// Prepare query to fetch images with LIMIT and OFFSET
$stmtL = $dbL->prepare("SELECT images.* FROM images ORDER BY images.id DESC LIMIT :limit OFFSET :offset");
$stmtL->bindValue(':limit', $limitL, SQLITE3_INTEGER);
$stmtL->bindValue(':offset', $offsetL, SQLITE3_INTEGER);
$resultL = $stmtL->execute();

// Generate HTML for the fetched images
ob_start();
while ($imageL = $resultL->fetchArray()) {
?>

    <div class="col">
      <div class="position-relative">
        <a class="rounded ratio ratio-1x1" href="/image.php?artworkid=<?= $imageL['id']; ?>">
          <img class="rounded shadow object-fit-cover" src="/thumbnails/<?= $imageL['filename']; ?>" alt="<?= $imageL['title']; ?>">
        </a>
        <?php
          $current_image_idL = $imageL['id'];
          
          // Query to count main image from the images table
          $stmtCountL = $dbL->prepare("SELECT COUNT(*) as image_count FROM images WHERE id = :id");
          $stmtCountL->bindValue(':id', $current_image_idL, SQLITE3_INTEGER);
          $imageCountQueryL = $stmtCountL->execute();
          if ($imageCountQueryL) {
            $imageCountRowL = $imageCountQueryL->fetchArray(SQLITE3_ASSOC);
            $imageCountL = $imageCountRowL ? $imageCountRowL['image_count'] : 0;
          } else {
            $imageCountL = 0;
          }
        
          // Query to count associated images from the image_child table
          $stmtChildL = $dbL->prepare("SELECT COUNT(*) as child_image_count FROM image_child WHERE image_id = :image_id");
          $stmtChildL->bindValue(':image_id', $current_image_idL, SQLITE3_INTEGER);
          $childImageCountQueryL = $stmtChildL->execute();
          if ($childImageCountQueryL) {
            $childImageCountRowL = $childImageCountQueryL->fetchArray(SQLITE3_ASSOC);
            $childImageCountL = $childImageCountRowL ? $childImageCountRowL['child_image_count'] : 0;
          } else {
            $childImageCountL = 0;
          }
        
          // Total count of main images and associated images
          $totalImagesCount = $imageCountL + $childImageCountL;
        ?>
        <?php include('../../rows_columns/image_counts_prev.php'); ?>
        <div class="position-absolute top-0 start-0">
          <div class="dropdown">
            <button class="btn border-0 p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-three-dots-vertical text-white link-body-emphasis fs-5" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); text-stroke: 2;"></i>
            </button>
            <ul class="dropdown-menu">
              <?php
                $is_favoritedL = $dbL->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$emailL' AND image_id = {$imageL['id']}");
                if ($is_favoritedL) {
              ?>
                <form class="favoriteFormL">
                  <input type="hidden" name="image_id" value="<?= $imageL['id']; ?>">
                  <input type="hidden" name="action" value="unfavorite">
                  <li><button type="submit" class="dropdown-item fw-bold unfavoriteBtnL"><i class="bi bi-heart-fill"></i> <small>unfavorite</small></button></li>
                </form>
              <?php } else { ?>
                <form class="favoriteFormL">
                  <input type="hidden" name="image_id" value="<?= $imageL['id']; ?>">
                  <input type="hidden" name="action" value="favorite">
                  <li><button type="submit" class="dropdown-item fw-bold favoriteBtnL"><i class="bi bi-heart"></i> <small>favorite</small></button></li>
                </form>
              <?php } ?>
              <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#shareImage<?php echo $imageL['id']; ?>"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
              <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $imageL['id']; ?>"><i class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
            </ul>
            <?php include('share_latest.php'); ?>
            
            <?php include('card_image_latest.php'); ?>

          </div>
        </div>
      </div>
    </div>
    <?php
    }
    $htmlL = ob_get_clean();
    
    // Output the generated HTML
    echo $htmlL;
    ?>