<?php
require_once('../../auth.php');

// Connect to the SQLite database using parameterized query
$dbL = new SQLite3('../../database.sqlite');

$email = $_SESSION['email'];

// Retrieve offset from POST parameters
$offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;

// Prepare the query to get the user's numpage
$queryNum = $dbL->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT); // Assuming $email is the email you want to search for
$resultNum = $queryNum->execute();
$user = $resultNum->fetchArray(SQLITE3_ASSOC);

$numpage = $user['numpage'];

// Set the limit of images per page
$limit = empty($numpage) ? 50 : $numpage;

// Prepare query to fetch images with LIMIT and OFFSET
$stmt = $dbL->prepare("SELECT images.*, users.artist, users.pic, users.id AS uid
  FROM images
  JOIN users ON images.email = users.email
  ORDER BY images.id DESC
  LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();

// Generate HTML for the fetched images
ob_start();
while ($imageL = $result->fetchArray()) {
?>

    <div class="col">
      <div class="position-relative">
        <a class="rounded ratio ratio-1x1" href="/image.php?artworkid=<?= $imageL['id']; ?>">
          <img class="rounded shadow object-fit-cover" src="/thumbnails/<?= $imageL['filename']; ?>" alt="<?= $imageL['title']; ?>">
        </a>
        <?php
          $current_image_id = $imageL['id'];
          
          // Query to count main image from the images table
          $stmt = $dbL->prepare("SELECT COUNT(*) as image_count FROM images WHERE id = :id");
          $stmt->bindValue(':id', $current_image_id, SQLITE3_INTEGER);
          $imageCountQuery = $stmt->execute();
          if ($imageCountQuery) {
            $imageCountRow = $imageCountQuery->fetchArray(SQLITE3_ASSOC);
            $imageCount = $imageCountRow ? $imageCountRow['image_count'] : 0;
          } else {
            $imageCount = 0;
          }
        
          // Query to count associated images from the image_child table
          $stmt = $dbL->prepare("SELECT COUNT(*) as child_image_count FROM image_child WHERE image_id = :image_id");
          $stmt->bindValue(':image_id', $current_image_id, SQLITE3_INTEGER);
          $childImageCountQuery = $stmt->execute();
          if ($childImageCountQuery) {
            $childImageCountRow = $childImageCountQuery->fetchArray(SQLITE3_ASSOC);
            $childImageCount = $childImageCountRow ? $childImageCountRow['child_image_count'] : 0;
          } else {
            $childImageCount = 0;
          }
        
          // Total count of main images and associated images
          $totalImagesCount = $imageCount + $childImageCount;
        ?>
        <?php include('../../rows_columns/image_counts.php'); ?>
        <div class="position-absolute top-0 start-0">
          <div class="dropdown">
            <button class="btn border-0 p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-three-dots-vertical text-white link-body-emphasis fs-5" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); text-stroke: 2;"></i>
            </button>
            <ul class="dropdown-menu">
              <?php
                $is_favorited = $dbL->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = {$imageL['id']}");
                if ($is_favorited) {
              ?>
                <form class="favoriteForm">
                  <input type="hidden" name="image_id" value="<?= $imageL['id']; ?>">
                  <input type="hidden" name="action" value="unfavorite">
                  <li><button type="submit" class="dropdown-item fw-bold unfavoriteBtn"><i class="bi bi-heart-fill"></i> <small>unfavorite</small></button></li>
                </form>
              <?php } else { ?>
                <form class="favoriteForm">
                  <input type="hidden" name="image_id" value="<?= $imageL['id']; ?>">
                  <input type="hidden" name="action" value="favorite">
                  <li><button type="submit" class="dropdown-item fw-bold favoriteBtn"><i class="bi bi-heart"></i> <small>favorite</small></button></li>
                </form>
              <?php } ?>
              <li><button class="dropdown-item fw-bold" onclick="shareImage(<?php echo $imageL['id']; ?>)"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
              <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $imageL['id']; ?>"><i class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <?php
    }
    $html = ob_get_clean();
    
    // Output the generated HTML
    echo $html;
    ?>