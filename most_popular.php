<?php

// Connect to the SQLite database using a parameterized query
$dbP = new SQLite3('database.sqlite');

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

    <div class="imagesCP mb-2 mt-2">
      <?php $count = 0; ?>
      <?php while ($imageP = $resultP->fetchArray()): ?>
        <?php
          $image_idP = $imageP['id'];
          $image_urlP = $imageP['filename'];
          $image_titleP = $imageP['title'];
          $current_image_idP = isset($_GET['artworkid']) ? $_GET['artworkid'] : null;
        ?>
        <div class="image-containerP">
          <div class="position-relative">
            <a class="shadow rounded imageAP" href="image.php?artworkid=<?php echo $image_idP; ?>">
              <img class="imageIP <?php echo ($imageP['type'] === 'nsfw') ? 'nsfw' : ''; ?> <?php echo ($image_idP == $current_image_idP) ? 'opacity-50' : ''; ?>" src="/thumbnails/<?php echo $image_urlP; ?>" alt="<?php echo $image_titleP; ?>">
            </a>
            <div class="position-absolute top-0 start-0 d-none"> <!-- Future Update Possible -->
              <div class="dropdown">
                <button class="btn btn-sm btn-dark ms-1 mt-1 rounded-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <?php
                  $is_favorited = $dbP->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = $image_idD");

                  if ($is_favorited) {
                  ?>
                    <form method="POST">
                      <input type="hidden" name="image_id" value="<?php echo $image_idP; ?>">
                      <li><button type="submit" class="dropdown-item fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i> <small>unfavorite</small></button></li>
                    </form>
                  <?php } else { ?>
                    <form method="POST">
                      <input type hidden="hidden" name="image_id" value="<?php echo $image_idP; ?>">
                      <li><button type="submit" class="dropdown-item fw-bold" name="favorite"><i class="bi bi-heart"></i> <small>favorite</small></button></li>
                    </form>
                  <?php } ?>
                  <li><button class="dropdown-item fw-bold" onclick="shareImageL(<?php echo $image_idP; ?>)"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
                  <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $image_idP; ?>"><i class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
                </ul>

                <?php include('contents/images_contents/card_image_most_popular.php'); ?>

              </div>
            </div>
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
      var containerP = $('.imagesCP');
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
          mediaElementP.classList.add('image-containerP');

          var linkP = document.createElement('a');
          linkP.href = 'image.php?artworkid=' + image_idP;
          linkP.classList.add('imageAD', 'rounded', 'shadow');

          var imageP = document.createElement('img');
          imageP.classList.add('imageIP');
          if (image_idP == current_image_idP) {
            imageP.classList.add('opacity-50');
          }
          imageP.src = '/thumbnails/' + image_urlP; // Corrected variable name
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
      .imagesCP {
        display: grid;
        grid-template-columns: repeat(2, 1fr); /* Two columns in mobile view */
        grid-gap: 3px;
        justify-content: center;
        margin-right: 3px;
        margin-left: 3px;
      }

      .imageAP  {
        display: block;
        border-radius: 4px;
        overflow: hidden;
      }

      .imageIP {
        width: 100%;
        height: auto;
        object-fit: cover;
        height: 200px;
        transition: transform 0.5s ease-in-out;
      }

      @media (min-width: 768px) {
        /* For desktop view, change the grid layout */
        .imagesCP {
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