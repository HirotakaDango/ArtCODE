<?php
$email = $_SESSION['email'];

// Connect to the SQLite database using parameterized query
$dbD = new SQLite3('database.sqlite');

// Get all of the images from the database using parameterized query
$stmtD = $dbD->prepare("SELECT * FROM images ORDER BY id DESC LIMIT 70");
$resultD = $stmtD->execute();

$images = array();
while ($imageD = $resultD->fetchArray()) {
  $imageData = array(
    'id' => $imageD['id'],
    'filename' => $imageD['filename'],
    'title' => $imageD['title'],
    // Add more fields as needed
  );
  $images[] = $imageData;
}
?>
  
    <div class="imagesCD mb-2 mt-2">
      <?php $count = 0; ?>
      <?php while ($imageD = $resultD->fetchArray()): ?>
        <?php
          $image_idD = $imageD['id'];
          $image_urlD = $imageD['filename'];
          $image_titleD = $imageD['title'];
          $current_image_idD = isset($_GET['artworkid']) ? $_GET['artworkid'] : null;
        ?>
        <div class="image-containerD">
          <div class="position-relative">
            <a class="shadow rounded imageAD" href="image.php?artworkid=<?php echo $image_idD; ?>">
              <img class="imageID <?php echo ($imageD['type'] === 'nsfw') ? 'nsfw' : ''; ?> <?php echo ($image_idD == $current_image_idD) ? 'opacity-50' : ''; ?>" src="/thumbnails/<?php echo $image_urlD; ?>" alt="<?php echo $image_titleD; ?>">
            </a>
            <div class="position-absolute top-0 start-0 d-none"> <!-- Future Update Possible -->
              <div class="dropdown">
                <button class="btn btn-sm btn-dark ms-1 mt-1 rounded-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <?php
                  $is_favorited = $dbD->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = $image_idD");

                  if ($is_favorited) {
                  ?>
                    <form method="POST">
                      <input type="hidden" name="image_id" value="<?php echo $image_idD; ?>">
                      <li><button type="submit" class="dropdown-item fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i> <small>unfavorite</small></button></li>
                    </form>
                  <?php } else { ?>
                    <form method="POST">
                      <input type hidden="hidden" name="image_id" value="<?php echo $image_idD; ?>">
                      <li><button type="submit" class="dropdown-item fw-bold" name="favorite"><i class="bi bi-heart"></i> <small>favorite</small></button></li>
                    </form>
                  <?php } ?>
                  <li><button class="dropdown-item fw-bold" onclick="shareImageL(<?php echo $image_idD; ?>)"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
                  <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $image_idD; ?>"><i class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
                </ul>

                <?php include('contents/images_contents/card_image_last.php'); ?>

              </div>
            </div>
          </div>
        </div>
        <?php $count++; ?>
        <?php if ($count >= 10) break; ?>
      <?php endwhile; ?>
    </div>
    <div class="container-fluid mt-4"><button id="loadMoreBtnD" class="btn btn-outline-dark rounded-pill fw-bold w-100">load more</button></div>
    <script>
      var currentIndexD = <?php echo $count; ?>;
      var imagesD = <?php echo json_encode($images); ?>;
      var containerD = $('.imagesCD');
      var loadMoreBtnD = $('#loadMoreBtnD');

      loadMoreBtnD.click(function () {
        var fragment = document.createDocumentFragment();

        for (var i = currentIndexD; i < currentIndexD + 10 && i < imagesD.length; i++) {
          var imageUD = imagesD[i];
          var image_idD = imageUD['id'];
          var image_urlD = imageUD['filename'];
          var image_titleD = imageUD['title'];
          var current_image_idD = '<?php echo $current_image_idD; ?>';

          var mediaElementD = document.createElement('div');
          mediaElementD.classList.add('image-containerD');

          var linkD = document.createElement('a');
          linkD.href = 'image.php?artworkid=' + image_idD;
          linkD.classList.add('imageAD', 'rounded', 'shadow');

          var imageD = document.createElement('img');
          imageD.classList.add('imageID');
          if (image_idD == current_image_idD) {
            imageD.classList.add('opacity-50');
          }
          imageD.src = 'thumbnails/' + image_urlD; // Corrected variable name
          imageD.alt = image_titleD; // Corrected variable name

          linkD.appendChild(imageD);
          mediaElementD.appendChild(linkD);
          fragment.appendChild(mediaElementD);
        }

        containerD.append(fragment);

        currentIndexD += 10;
        if (currentIndexD >= imagesD.length) {
          loadMoreBtnD.hide();
        }
      });
    </script>
    <style>
      .imagesCD {
        display: grid;
        grid-template-columns: repeat(2, 1fr); /* Two columns in mobile view */
        grid-gap: 3px;
        justify-content: center;
        margin-right: 3px;
        margin-left: 3px;
      }

      .imageAD  {
        display: block;
        border-radius: 4px;
        overflow: hidden;
      }

      .imageID {
        width: 100%;
        height: auto;
        object-fit: cover;
        height: 200px;
        transition: transform 0.5s ease-in-out;
      }
      
      .text-stroke {
        -webkit-text-stroke: 1px;
      }

      @media (min-width: 768px) {
        /* For desktop view, change the grid layout */
        .imagesCD {
          grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
      }
    </style>
    <script>
      function shareImageL(userId) {
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