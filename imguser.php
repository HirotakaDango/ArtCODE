    <style>
      .hori {
        border-radius: 5px;
        width: 100px;
        height: 120px;
        object-fit: cover;
      }

      .media-scroller {
        display: grid;
        gap: 3px; /* Updated gap value */
        grid-auto-flow: column;
        overflow-x: auto;
        overscroll-behavior-inline: contain;
      }

      .snaps-inline {
        scroll-snap-type: inline mandatory;
        scroll-padding-inline: var(--_spacer, 1rem);
      }
  
      .snaps-inline > * {
        scroll-snap-align: start;
      }

      .shadowed-text {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);
      }
    </style>
    <?php
      // Get all images for the given user_email
      $stmt = $db->prepare("SELECT * FROM images WHERE email = :email ORDER BY id DESC");
      $stmt->bindParam(':email', $user_email);
      $stmt->execute();
      $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <p class="ms-2 mt-3 text-secondary fw-bold">
      <i class="bi bi-images"></i> Latest images by <?php echo $user['artist']; ?>
    </p>
    <div id="image-carousel" class="carousel slide carousel-fade mt-2" style="margin-bottom: 3px;">
      <div class="carousel-inner">
        <?php
          $current_image_id = isset($_GET['artworkid']) ? $_GET['artworkid'] : null;
          $active_index = 0;
        ?>
        <?php foreach ($images as $index => $imageU): ?>
          <?php
            $image_id = $imageU['id'];
            $user_email = $imageU['email'];
            $image_url = $imageU['filename'];
            $image_title = $imageU['title'];
            $active_class = ($image_id == $current_image_id) ? 'active' : '';

            if ($active_class === 'active') {
              $active_index = $index;
            }
          ?>
          <div class="carousel-item <?php echo $active_class; ?>">
            <a href="image.php?artworkid=<?php echo $image_id; ?>">
              <img class="lazy-load d-block w-100 rounded object-fit-cover" style="height: 200px; object-position: top;" data-src="thumbnails/<?php echo $image_url; ?>" alt="<?php echo $image_title; ?>">
              <div class="carousel-caption">
                <h5 class="fw-bold shadowed-text"><?php echo $image_title; ?></h5>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#image-carousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#image-carousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
    <div class="mb-3 media-scroller snaps-inline overflow-auto">
      <?php $count = 0; ?>
      <?php foreach ($images as $imageU): ?>
        <?php
          $image_id = $imageU['id'];
          $user_email = $imageU['email'];
          $image_url = $imageU['filename'];
          $image_title = $imageU['title'];
          $current_image_id = isset($_GET['artworkid']) ? $_GET['artworkid'] : null;
        ?>
        <div class="media-element d-inline-flex">
          <a href="image.php?artworkid=<?php echo $image_id; ?>">
            <img class="hori <?php echo ($image_id == $current_image_id) ? 'opacity-50' : ''; ?>" src="thumbnails/<?php echo $image_url; ?>" alt="<?php echo $image_title; ?>">
          </a>
        </div>
        <?php $count++; ?>
        <?php if ($count >= 10) break; ?>
      <?php endforeach; ?>

      <button id="loadMoreBtn" class="btn btn-secondary opacity-50"><i class="bi bi-plus-circle display-5 text-stroke"></i></button>
      
      <script>
        // Set the active item based on the current image ID
        var carousel = document.querySelector("#image-carousel");
        var carouselInstance = new bootstrap.Carousel(carousel, {
          ride: false // Disable automatic cycling
        });
        carouselInstance.to(<?php echo $active_index; ?>); // Set the active item manually
      </script>
      <script>
        var currentIndex = <?php echo $count; ?>;
        var images = <?php echo json_encode($images); ?>;
        var container = document.querySelector('.media-scroller');
        var loadMoreBtn = document.getElementById('loadMoreBtn');

        function loadMoreImages() {
          for (var i = currentIndex; i < currentIndex + 10 && i < images.length; i++) {
            var imageU = images[i];
            var image_id = imageU['id'];
            var user_email = imageU['email'];
            var image_url = imageU['filename'];
            var image_title = imageU['title'];
            var current_image_id = '<?php echo $current_image_id; ?>';

            var mediaElement = document.createElement('div');
            mediaElement.classList.add('media-element');
            mediaElement.classList.add('d-inline-flex');

            var link = document.createElement('a');
            link.href = 'image.php?artworkid=' + image_id;

            var image = document.createElement('img');
            image.classList.add('hori');
            if (image_id == current_image_id) {
              image.classList.add('opacity-50');
            }
            image.src = 'thumbnails/' + image_url;
            image.alt = image_title;

            link.appendChild(image);
            mediaElement.appendChild(link);
            container.insertBefore(mediaElement, loadMoreBtn);
          }

          currentIndex += 10;
          if (currentIndex >= images.length) {
            loadMoreBtn.style.display = 'none';
          }
        }

        loadMoreBtn.addEventListener('click', loadMoreImages);
      </script>
    </div>
