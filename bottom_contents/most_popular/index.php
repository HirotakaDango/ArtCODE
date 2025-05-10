<?php
// Connect to the SQLite database using parameterized query
$dbP = new SQLite3('database.sqlite');

$emailP = $_SESSION['email'];

// Process any favorite/unfavorite requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action'])) {
    $actionP = $_POST['action'];
    $image_idP = $_POST['image_id'];

    if ($actionP === 'favorite') {
      $existing_favP = $dbP->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$emailP' AND image_id = $image_idP");

      if ($existing_favP == 0) {
        $dbP->exec("INSERT INTO favorites (email, image_id) VALUES ('$emailP', $image_idP)");
        echo json_encode(['success' => true]);
        exit();
      }
    } elseif ($actionP === 'unfavorite') {
      $dbP->exec("DELETE FROM favorites WHERE email = '$emailP' AND image_id = $image_idP");
      echo json_encode(['success' => true]);
      exit();
    }
  }
}

// Prepare the query to get the user's numpage
$queryNumP = $dbP->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNumP->bindValue(':email', $emailP, SQLITE3_TEXT); // Assuming $emailP is the email you want to search for
$resultNumP = $queryNumP->execute();
$userP = $resultNumP->fetchArray(SQLITE3_ASSOC);

$numpageP = $userP['numpage'];

// Set the limit of images per page
$limitP = empty($numpageP) ? 50 : $numpageP;

// Get all images from the database
$stmtP = $dbP->prepare("
  SELECT images.*, COUNT(favorites.id) AS favorite_count FROM images LEFT JOIN favorites ON images.id = favorites.image_id GROUP BY images.id ORDER BY favorite_count DESC
  LIMIT $limitP
");
$resultP = $stmtP->execute();
?>

    <div class="w-100 px-1">
      <div class="<?php include('rows_columns/row-cols.php'); echo $rows_columns; ?>" id="mainContentP">
        <?php while ($imageP = $resultP->fetchArray()): ?>
        <div class="col">
          <div class="position-relative">
            <a class="rounded ratio ratio-1x1" href="/image.php?artworkid=<?= $imageP['id']; ?>">
              <img class="rounded shadow object-fit-cover" src="/thumbnails/<?= $imageP['filename']; ?>" alt="<?= $imageP['title']; ?>">
            </a>
            <?php
            $current_image_idP = $imageP['id'];
    
            // Query to count main image from the images table
            $stmtImageCountP = $dbP->prepare("SELECT COUNT(*) as image_count FROM images WHERE id = :id");
            $stmtImageCountP->bindValue(':id', $current_image_idP, SQLITE3_INTEGER);
            $imageCountQueryP = $stmtImageCountP->execute();
            if ($imageCountQueryP) {
              $imageCountRowP = $imageCountQueryP->fetchArray(SQLITE3_ASSOC);
              $imageCountP = $imageCountRowP ? $imageCountRowP['image_count'] : 0;
            } else {
              $imageCountP = 0;
            }
    
            // Query to count associated images from the image_child table
            $stmtChildImageCountP = $dbP->prepare("SELECT COUNT(*) as child_image_count FROM image_child WHERE image_id = :image_id");
            $stmtChildImageCountP->bindValue(':image_id', $current_image_idP, SQLITE3_INTEGER);
            $childImageCountQueryP = $stmtChildImageCountP->execute();
            if ($childImageCountQueryP) {
              $childImageCountRowP = $childImageCountQueryP->fetchArray(SQLITE3_ASSOC);
              $childImageCountP = $childImageCountRowP ? $childImageCountRowP['child_image_count'] : 0;
            } else {
              $childImageCountP = 0;
            }
    
            // Total count of main images and associated images
            $totalImagesCount = $imageCountP + $childImageCountP;
            ?>
            <?php include('rows_columns/image_counts_prev_artwork.php'); ?>
            <div class="position-absolute top-0 end-0">
              <div class="dropdown">
                <button class="btn border-0 p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical text-white link-body-emphasis fs-5"
                    style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); text-stroke: 2;"></i>
                </button>
                <ul class="dropdown-menu">
                  <?php
                  $is_favoritedP = $dbP->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$emailP' AND image_id = {$imageP['id']}");
                  if ($is_favoritedP) {
                  ?>
                  <form class="favoriteFormP">
                    <input type="hidden" name="image_id" value="<?= $imageP['id']; ?>">
                    <input type="hidden" name="action" value="unfavorite">
                    <li><button type="button" class="dropdown-item fw-bold unfavoriteBtnP"><i class="bi bi-heart-fill"></i> <small>unfavorite</small></button></li>
                  </form>
                  <?php } else { ?>
                  <form class="favoriteFormP">
                    <input type="hidden" name="image_id" value="<?= $imageP['id']; ?>">
                    <input type="hidden" name="action" value="favorite">
                    <li><button type="button" class="dropdown-item fw-bold favoriteBtnP"><i class="bi bi-heart"></i> <small>favorite</small></button></li>
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
        <?php endwhile; ?>
      </div>
    </div>
    <div class="w-100 px-1 my-1">
      <div class="<?php include('rows_columns/row-cols.php'); echo $rows_columns; ?>" id="loadMoreContainerP"></div>
    </div>
    <div id="loadMoreBtnContainerP">
      <div class="w-100 px-1 mt-2">
        <button id="loadMoreBtnP" class="btn btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-pill fw-bold w-100">Load More</button>
      </div>
    </div>
    <div class="mt-5"></div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Function to handle favorite/unfavorite actions
      function handleFavoriteActionP(formP) {
        var formDataP = new FormData(formP);
        var buttonP = formP.querySelector('button');
        var xhrP = new XMLHttpRequest();
        xhrP.open('POST', 'bottom_contents/most_popular/favorite.php', true); // Correct path to favorite.php
        xhrP.onload = function() {
          if (xhrP.status === 200) {
            var responseP = JSON.parse(xhrP.responseText);
            if (responseP.success) {
              var actionP = formP.querySelector('input[name="action"]').value;
              if (actionP === 'favorite') {
                buttonP.innerHTML = '<i class="bi bi-heart-fill"></i> <small>unfavorite</small>';
                buttonP.classList.remove('favoriteBtnP');
                buttonP.classList.add('unfavoriteBtnP');
                formP.querySelector('input[name="action"]').value = 'unfavorite';
              } else if (actionP === 'unfavorite') {
                buttonP.innerHTML = '<i class="bi bi-heart"></i> <small>favorite</small>';
                buttonP.classList.remove('unfavoriteBtnP');
                buttonP.classList.add('favoriteBtnP');
                formP.querySelector('input[name="action"]').value = 'favorite';
              }
            } else {
              console.error('Failed to update favorite status.');
            }
          }
        };
        xhrP.send(formDataP);
      }
    
      // Event delegation for favorite/unfavorite buttons
      document.addEventListener('click', function(eventP) {
        var buttonP = eventP.target.closest('.favoriteBtnP, .unfavoriteBtnP');
        if (buttonP) {
          eventP.preventDefault();
          handleFavoriteActionP(buttonP.closest('form'));
        }
      });
    
      // Load more images functionality
      var loadMoreBtnP = document.getElementById('loadMoreBtnP');
      var loadMoreContainerP = document.getElementById('loadMoreContainerP');
      var offsetP = 12; // Initial offset
    
      loadMoreBtnP.addEventListener('click', function() {
        var xhrP = new XMLHttpRequest();
        xhrP.open('POST', 'bottom_contents/most_popular/scroll.php', true); // Correct path to scroll.php
        xhrP.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhrP.onload = function() {
          if (xhrP.status === 200) {
            loadMoreContainerP.innerHTML += xhrP.responseText;
            offsetP += 12; // Increase offset by 12
          } else {
            console.error('Failed to load more images.');
          }
        };
        xhrP.send('offset=' + offsetP);
      });
    });
    </script>
