<?php
// Connect to the SQLite database using parameterized query
$dbL = new SQLite3('database.sqlite');

$email = $_SESSION['email'];

// Process any favorite/unfavorite requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $image_id = $_POST['image_id'];

    if ($action === 'favorite') {
      $existing_fav = $dbL->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = $image_id");

      if ($existing_fav == 0) {
        $dbL->exec("INSERT INTO favorites (email, image_id) VALUES ('$email', $image_id)");
        echo json_encode(['success' => true]);
        exit();
      }
    } elseif ($action === 'unfavorite') {
      $dbL->exec("DELETE FROM favorites WHERE email = '$email' AND image_id = $image_id");
      echo json_encode(['success' => true]);
      exit();
    }
  }
}

// Prepare the query to get the user's numpage
$queryNum = $dbL->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT); // Assuming $email is the email you want to search for
$resultNum = $queryNum->execute();
$user = $resultNum->fetchArray(SQLITE3_ASSOC);

$numpage = $user['numpage'];

// Set the limit of images per page
$limit = empty($numpage) ? 50 : $numpage;

// Get all images from the database
$stmt = $dbL->prepare("
  SELECT images.*, users.artist, users.pic, users.id AS uid
  FROM images
  JOIN users ON images.email = users.email
  ORDER BY images.id DESC
  LIMIT $numpage
");
$result = $stmt->execute();
?>

    <div class="w-100 px-1">
      <div class="<?php include('rows_columns/row-cols.php'); echo $rows_columns; ?>" id="mainContent">
        <?php while ($imageL = $result->fetchArray()): ?>
        <div class="col">
          <div class="position-relative">
            <a class="rounded ratio ratio-1x1" href="/image.php?artworkid=<?= $imageL['id']; ?>">
              <img class="rounded shadow object-fit-cover" src="/thumbnails/<?= $imageL['filename']; ?>"
                alt="<?= $imageL['title']; ?>">
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
            <?php include('rows_columns/image_counts.php'); ?>
            <div class="position-absolute top-0 start-0">
              <div class="dropdown">
                <button class="btn border-0 p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical text-white link-body-emphasis fs-5"
                    style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); text-stroke: 2;"></i>
                </button>
                <ul class="dropdown-menu">
                  <?php
                $is_favorited = $dbL->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = {$imageL['id']}");
                if ($is_favorited) {
              ?>
                  <form class="favoriteForm">
                    <input type="hidden" name="image_id" value="<?= $imageL['id']; ?>">
                    <input type="hidden" name="action" value="unfavorite">
                    <li><button type="submit" class="dropdown-item fw-bold unfavoriteBtn"><i
                          class="bi bi-heart-fill"></i> <small>unfavorite</small></button></li>
                  </form>
                  <?php } else { ?>
                  <form class="favoriteForm">
                    <input type="hidden" name="image_id" value="<?= $imageL['id']; ?>">
                    <input type="hidden" name="action" value="favorite">
                    <li><button type="submit" class="dropdown-item fw-bold favoriteBtn"><i
                          class="bi bi-heart"></i> <small>favorite</small></button></li>
                  </form>
                  <?php } ?>
                  <li><button class="dropdown-item fw-bold" onclick="shareImageL(<?php echo $imageL['id']; ?>)"><i
                        class="bi bi-share-fill"></i> <small>share</small></button></li>
                  <li><button class="dropdown-item fw-bold" data-bs-toggle="modal"
                      data-bs-target="#infoImage_<?php echo $imageL['id']; ?>"><i
                        class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
    <div class="w-100 px-1 my-1">
      <div class="<?php include('rows_columns/row-cols.php'); echo $rows_columns; ?>" id="load-more-container"></div>
    </div>
    <div id="load-more-btn-container">
      <div class="w-100 px-1 mt-2">
        <button id="load-more-btn" class="btn btn-outline-dark rounded-pill fw-bold w-100">Load More</button>
      </div>
    </div>
    <div class="mt-5"></div>
    
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Function to handle favorite/unfavorite actions
        function handleFavoriteAction(button) {
          var formData = new FormData(button.closest('.favoriteForm'));
          var xhr = new XMLHttpRequest();
          xhr.open('POST', 'bottom_contents/latest/favorite.php', true);
          xhr.onload = function() {
            if (xhr.status === 200) {
              var response = JSON.parse(xhr.responseText);
              if (response.success) {
                var form = button.closest('.favoriteForm');
                var action = form.querySelector('input[name="action"]').value;
                if (action === 'favorite') {
                  button.innerHTML = '<i class="bi bi-heart-fill"></i> <small>unfavorite</small>';
                  button.classList.remove('favoriteBtn');
                  button.classList.add('unfavoriteBtn');
                  form.querySelector('input[name="action"]').value = 'unfavorite';
                } else if (action === 'unfavorite') {
                  button.innerHTML = '<i class="bi bi-heart"></i> <small>favorite</small>';
                  button.classList.remove('unfavoriteBtn');
                  button.classList.add('favoriteBtn');
                  form.querySelector('input[name="action"]').value = 'favorite';
                }
              } else {
                console.error('Failed to update favorite status.');
              }
            }
          };
          xhr.send(formData);
        }
      
        // Event delegation for favorite/unfavorite buttons
        document.addEventListener('click', function(event) {
          if (event.target.closest('.favoriteBtn') || event.target.closest('.unfavoriteBtn')) {
            event.preventDefault();
            handleFavoriteAction(event.target.closest('button'));
          }
        });
      
        // Load more images functionality
        var loadMoreBtn = document.getElementById('load-more-btn');
        var loadMoreContainer = document.getElementById('load-more-container');
        var offset = 12; // Initial offset
      
        loadMoreBtn.addEventListener('click', function() {
          var xhr = new XMLHttpRequest();
          xhr.open('POST', 'bottom_contents/latest/scroll.php', true);
          xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
          xhr.onload = function() {
            if (xhr.status === 200) {
              loadMoreContainer.innerHTML += xhr.responseText;
              offset += 12; // Update offset
            } else {
              console.error('Failed to load more images.');
            }
          };
          xhr.send('offset=' + offset);
        });
      });
    </script>