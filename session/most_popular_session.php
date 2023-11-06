<?php
// Connect to the SQLite database using a parameterized query
$dbP = new SQLite3('../database.sqlite');

// Get all of the images from the database using a parameterized query
$stmtP = $dbP->prepare("SELECT images.*, COUNT(favorites.id) AS favorite_count FROM images LEFT JOIN favorites ON images.id = favorites.image_id GROUP BY images.id ORDER BY favorite_count DESC LIMIT 70");
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

    <div class="imagesC mb-2 mt-2">
      <?php $count = 0; ?>
      <?php while ($imageP = $resultP->fetchArray()): ?>
        <?php
          $image_idP = $imageP['id'];
          $image_urlP = $imageP['filename'];
          $image_titleP = $imageP['title'];
          $current_image_idP = isset($_GET['artworkid']) ? $_GET['artworkid'] : null;
        ?>
        <div class="image-container">
          <div class="position-relative">
            <a class="shadow rounded imageA" href="image.php?artworkid=<?php echo $image_idP; ?>">
              <img class="imageI <?php echo ($imageP['type'] === 'nsfw') ? 'nsfw' : ''; ?> <?php echo ($image_idP == $current_image_idP) ? 'opacity-50' : ''; ?>" src="../thumbnails/<?php echo $image_urlP; ?>" alt="<?php echo $image_titleP; ?>">
            </a>
          </div>
        </div>
        <?php $count++; ?>
        <?php if ($count >= 10) break; ?>
      <?php endwhile; ?>
    </div>
    <div class="container-fluid mt-4"><button id="loadMoreBtnP" class="btn btn-outline-dark rounded-pill fw-bold w-100">load more</button></div>
    <script>
      var currentIndexP = <?php echo $count; ?>;
      var imagesP = <?php echo json_encode($images); ?>;
      var containerP = $('.imagesC');
      var loadMoreBtnP = $('#loadMoreBtnP');

      loadMoreBtnP.click(function () {
        var fragment = document.createDocumentFragment();

        for (var i = currentIndexP; i < currentIndexP + 10 && i < imagesP.length; i++) {
          var imageUP = imagesP[i];
          var image_idP = imageUP['id'];
          var image_urlP = imageUP['filename'];
          var image_titleP = imageUP['title'];
          var current_image_idP = '<?php echo $current_image_idD; ?>';

          var mediaElementP = document.createElement('div');
          mediaElementP.classList.add('image-container');

          var linkP = document.createElement('a');
          linkP.href = 'image.php?artworkid=' + image_idP;
          linkP.classList.add('imageAD', 'rounded', 'shadow');

          var imageP = document.createElement('img');
          imageP.classList.add('imageI');
          if (image_idP == current_image_idP) {
            imageP.classList.add('opacity-50');
          }
          imageP.src = '../thumbnails/' + image_urlP; // Corrected variable name
          imageP.alt = image_titleP; // Corrected variable name

          linkP.appendChild(imageP);
          mediaElementP.appendChild(linkP);
          fragment.appendChild(mediaElementP);
        }

        containerP.append(fragment);

        currentIndexP += 10;
        if (currentIndexP >= imagesP.length) {
          loadMoreBtnP.hide();
        }
      });
    </script>
    <style>
      .imagesC {
        display: grid;
        grid-template-columns: repeat(2, 1fr); /* Two columns in mobile view */
        grid-gap: 3px;
        justify-content: center;
        margin-right: 3px;
        margin-left: 3px;
      }

      .imageA  {
        display: block;
        border-radius: 4px;
        overflow: hidden;
      }

      .imageI {
        width: 100%;
        height: auto;
        object-fit: cover;
        height: 200px;
        transition: transform 0.5s ease-in-out;
      }

      @media (min-width: 768px) {
        /* For desktop view, change the grid layout */
        .imagesC {
          grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
      }
    </style>
    <script>
      function shareImageP(userId) {
        // Compose the share URL
        var shareUrl = '?artworkid=' + userId;

        // Check if the Share API is supported by the browser
        if (navigator.share) {
          navigator.share({
          url: shareUrl
        })
          .then(() => console.log('Shared successfully.'))
          .catch((error) => console.error('Error sharing:', error));
        } else {
          console.log('Share API is not supported in this browser.');
          // Provide an alternative action for browsers that do not support the Share API
          // For example, you can open a new window with the share URL
          window.open(shareUrl, '_blank');
        }
      }
    </script>