<?php
// Connect to the SQLite database using parameterized query
$dbL = new SQLite3('database.sqlite');

$emailL = $_SESSION['email'];

// Lrocess any favorite/unfavorite requests
if ($_SERVER['REQUEST_METHOD'] === 'LOST') {
  if (isset($_LOST['action'])) {
    $actionL = $_LOST['action'];
    $image_idL = $_LOST['image_id'];

    if ($actionL === 'favorite') {
      $existing_favL = $dbL->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$emailL' AND image_id = $image_idL");

      if ($existing_favL == 0) {
        $dbL->exec("INSERT INTO favorites (email, image_id) VALUES ('$emailL', $image_idL)");
        echo json_encode(['success' => true]);
        exit();
      }
    } elseif ($actionL === 'unfavorite') {
      $dbL->exec("DELETE FROM favorites WHERE email = '$emailL' AND image_id = $image_idL");
      echo json_encode(['success' => true]);
      exit();
    }
  }
}

// Lrepare the query to get the user's numpage
$queryNumL = $dbL->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNumL->bindValue(':email', $emailL, SQLITE3_TEXT); // Assuming $emailL is the email you want to search for
$resultNumL = $queryNumL->execute();
$userL = $resultNumL->fetchArray(SQLITE3_ASSOC);

$numpageL = $userL['numpage'];

// Set the limit of images per page
$limitL = empty($numpageL) ? 50 : $numpageL;

// Get all images from the database
$stmtL = $dbL->prepare("
  SELECT images.* FROM images
  ORDER BY images.id DESC
  LIMIT $limitL
");
$resultL = $stmtL->execute();
?>

    <div class="w-100 px-1">
      <div class="<?php include('rows_columns/row-cols.php'); echo $rows_columns; ?>" id="mainContentL">
        <?php while ($imageL = $resultL->fetchArray()): ?>
        <div class="col">
          <div class="position-relative">
            <a class="rounded ratio ratio-1x1" href="/image.php?artworkid=<?= $imageL['id']; ?>">
              <img class="rounded shadow object-fit-cover" src="/thumbnails/<?= $imageL['filename']; ?>" alt="<?= $imageL['title']; ?>">
            </a>
            <?php
            $current_image_idL = $imageL['id'];
    
            // Query to count main image from the images table
            $stmtImageCountL = $dbL->prepare("SELECT COUNT(*) as image_count FROM images WHERE id = :id");
            $stmtImageCountL->bindValue(':id', $current_image_idL, SQLITE3_INTEGER);
            $imageCountQueryL = $stmtImageCountL->execute();
            if ($imageCountQueryL) {
              $imageCountRowL = $imageCountQueryL->fetchArray(SQLITE3_ASSOC);
              $imageCountL = $imageCountRowL ? $imageCountRowL['image_count'] : 0;
            } else {
              $imageCountL = 0;
            }
    
            // Query to count associated images from the image_child table
            $stmtChildImageCountL = $dbL->prepare("SELECT COUNT(*) as child_image_count FROM image_child WHERE image_id = :image_id");
            $stmtChildImageCountL->bindValue(':image_id', $current_image_idL, SQLITE3_INTEGER);
            $childImageCountQueryL = $stmtChildImageCountL->execute();
            if ($childImageCountQueryL) {
              $childImageCountRowL = $childImageCountQueryL->fetchArray(SQLITE3_ASSOC);
              $childImageCountL = $childImageCountRowL ? $childImageCountRowL['child_image_count'] : 0;
            } else {
              $childImageCountL = 0;
            }
    
            // Total count of main images and associated images
            $totalImagesCount = $imageCountL + $childImageCountL;
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
                  $is_favoritedL = $dbL->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$emailL' AND image_id = {$imageL['id']}");
                  if ($is_favoritedL) {
                  ?>
                  <form class="favoriteFormL">
                    <input type="hidden" name="image_id" value="<?= $imageL['id']; ?>">
                    <input type="hidden" name="action" value="unfavorite">
                    <li><button type="button" class="dropdown-item fw-bold unfavoriteBtnL"><i class="bi bi-heart-fill"></i> <small>unfavorite</small></button></li>
                  </form>
                  <?php } else { ?>
                  <form class="favoriteFormL">
                    <input type="hidden" name="image_id" value="<?= $imageL['id']; ?>">
                    <input type="hidden" name="action" value="favorite">
                    <li><button type="button" class="dropdown-item fw-bold favoriteBtnL"><i class="bi bi-heart"></i> <small>favorite</small></button></li>
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
        <?php endwhile; ?>
      </div>
    </div>
    <div class="w-100 px-1 my-1">
      <div class="<?php include('rows_columns/row-cols.php'); echo $rows_columns; ?>" id="loadMoreContainerL"></div>
    </div>
    <div id="loadMoreBtnContainerL">
      <div class="w-100 px-1 mt-2">
        <button id="loadMoreBtnL" class="btn btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-pill fw-bold w-100">Load More</button>
      </div>
    </div>
    <div class="mt-5"></div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Function to handle favorite/unfavorite actions
      function handleFavoriteActionL(formL) {
        var formDataL = new FormData(formL);
        var buttonL = formL.querySelector('button');
        var xhrL = new XMLHttpRequest();
        xhrL.open('POST', 'bottom_contents/latest/favorite.php', true); // Correct path to favorite.php
        xhrL.onload = function() {
          if (xhrL.status === 200) {
            var responseL = JSON.parse(xhrL.responseText);
            if (responseL.success) {
              var actionL = formL.querySelector('input[name="action"]').value;
              if (actionL === 'favorite') {
                buttonL.innerHTML = '<i class="bi bi-heart-fill"></i> <small>unfavorite</small>';
                buttonL.classList.remove('favoriteBtnL');
                buttonL.classList.add('unfavoriteBtnL');
                formL.querySelector('input[name="action"]').value = 'unfavorite';
              } else if (actionL === 'unfavorite') {
                buttonL.innerHTML = '<i class="bi bi-heart"></i> <small>favorite</small>';
                buttonL.classList.remove('unfavoriteBtnL');
                buttonL.classList.add('favoriteBtnL');
                formL.querySelector('input[name="action"]').value = 'favorite';
              }
            } else {
              console.error('Failed to update favorite status.');
            }
          }
        };
        xhrL.send(formDataL);
      }
    
      // Event delegation for favorite/unfavorite buttons
      document.addEventListener('click', function(eventL) {
        var buttonL = eventL.target.closest('.favoriteBtnL, .unfavoriteBtnL');
        if (buttonL) {
          eventL.preventDefault();
          handleFavoriteActionL(buttonL.closest('form'));
        }
      });
    
      // Load more images functionality
      var loadMoreBtnL = document.getElementById('loadMoreBtnL');
      var loadMoreContainerL = document.getElementById('loadMoreContainerL');
      var offsetL = 12; // Initial offset
    
      loadMoreBtnL.addEventListener('click', function() {
        var xhrL = new XMLHttpRequest();
        xhrL.open('POST', 'bottom_contents/latest/scroll.php', true); // Correct path to scroll.php
        xhrL.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhrL.onload = function() {
          if (xhrL.status === 200) {
            loadMoreContainerL.innerHTML += xhrL.responseText;
            offsetL += 12; // Increase offset by 12
          } else {
            console.error('Failed to load more images.');
          }
        };
        xhrL.send('offset=' + offsetL);
      });
    });
    </script>
