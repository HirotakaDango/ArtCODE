<?php

// Connect to the SQLite database using a parameterized query
$dbP = new SQLite3('../database.sqlite');

// Get all of the images from the database using a parameterized query
$stmtP = $dbP->prepare("SELECT images.*, COUNT(favorites.id) AS favorite_count FROM images LEFT JOIN favorites ON images.id = favorites.image_id GROUP BY images.id ORDER BY favorite_count DESC");
$resultP = $stmtP->execute();

$images = array();
while ($imageP = $resultP->fetchArray()) {
  $imageData = array(
    'id' => $imageP['id'],
    'filename' => $imageP['filename'],
    'title' => $imageP['title'],
    // Add more fields as needed
  );
  $images[] = $imageData;
}
?>

    <div class="imagesMP w-100 px-1 my-2">
      <div class="<?php include('../rows_columns/row-cols.php'); echo $rows_columns; ?>">
        <?php 
        $count = 0; 
        $images = [];
        while ($imageP = $resultP->fetchArray()) {
          $images[] = $imageP;
          if ($count < 12) {
            $image_idP = $imageP['id'];
            $image_urlP = $imageP['filename'];
            $image_titleP = $imageP['title'];
            $current_image_idP = isset($_GET['artworkid']) ? $_GET['artworkid'] : null;
        ?>
        <div class="col">
          <div class="position-relative">
            <a class="rounded ratio ratio-1x1 imageMPA" href="image.php?artworkid=<?php echo $image_idP; ?>">
              <img class="rounded shadow object-fit-cover imageMPIP <?php echo ($imageP['type'] === 'nsfw') ? 'nsfw' : ''; ?>" src="/thumbnails/<?php echo $image_urlP; ?>" alt="<?php echo $image_titleP; ?>">
            </a>
          </div>
        </div>
        <?php 
            $count++;
          }
        } 
        ?>
      </div>
    </div>
    <div id="load-more-btn-container">
      <div class="w-100 px-1 mt-2">
        <button id="loadMoreBtnP" class="btn btn-outline-dark rounded-pill fw-bold w-100">Load more</button>
      </div>
    </div>
    <script>
      var currentIndexP = 12;
      var imagesP = <?php echo json_encode($images); ?>;
      var containerP = document.querySelector('.imagesMP .row');
      var loadMoreBtnP = document.getElementById('loadMoreBtnP');
    
      loadMoreBtnP.addEventListener('click', function() {
        var fragment = document.createDocumentFragment();
    
        for (var i = currentIndexP; i < currentIndexP + 12 && i < imagesP.length; i++) {
          var imageUP = imagesP[i];
          var image_idP = imageUP['id'];
          var image_urlP = imageUP['filename'];
          var image_titleP = imageUP['title'];
          var current_image_idP = '<?php echo $current_image_idP; ?>';
    
          var colDiv = document.createElement('div');
          colDiv.classList.add('col');
    
          var posRelDiv = document.createElement('div');
          posRelDiv.classList.add('position-relative');
    
          var linkP = document.createElement('a');
          linkP.href = 'image.php?artworkid=' + image_idP;
          linkP.classList.add('rounded', 'ratio', 'ratio-1x1', 'imageMPA');
    
          var imageP = document.createElement('img');
          imageP.classList.add('rounded', 'shadow', 'object-fit-cover', 'imageMPIP');
          if (imageUP['type'] === 'nsfw') {
            imageP.classList.add('nsfw');
          }
          if (image_idP == current_image_idP) {
            imageP.classList.add('opacity-50');
          }
          imageP.src = '/thumbnails/' + image_urlP;
          imageP.alt = image_titleP;
    
          linkP.appendChild(imageP);
          posRelDiv.appendChild(linkP);
          colDiv.appendChild(posRelDiv);
          fragment.appendChild(colDiv);
        }
    
        containerP.appendChild(fragment);
    
        currentIndexP += 12;
        if (currentIndexP >= imagesP.length) {
          loadMoreBtnP.style.display = 'none';
        }
      });
    </script>