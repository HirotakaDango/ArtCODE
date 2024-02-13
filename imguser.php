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
      
      .blurred {
        filter: blur(4px);
      }
    </style>
    <?php
      // Get all images for the given user_email
      $stmt = $db->prepare("SELECT id, filename, tags, title, imgdesc, type FROM images WHERE email = :email ORDER BY id DESC");
      $stmt->bindParam(':email', $user_email);
      $stmt->execute();
      $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <p class="ms-2 mt-3 text-dark fw-bold">
      <i class="bi bi-images"></i> Latest images by <?php echo $user['artist']; ?>
    </p>
    <div class="modal fade" id="imgcarousel" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content p-0 hide-scrollbar bg-transparent border-0">
          <div class="modal-body rounded-4 border-0 shadow p-0 bg-dark">
            <div id="image-carousel" class="carousel slide carousel-fade">
              <div class="carousel-inner rounded-4">
                <?php
                $current_image_id = isset($_GET['artworkid']) ? $_GET['artworkid'] : null;
                $active_index = 0;
                ?>
                <?php foreach ($images as $index => $imageU): ?>
                  <?php
                  $image_id = $imageU['id'];
                  $image_url = $imageU['filename'];
                  $image_title = $imageU['title'];
                  $image_desc = $imageU['imgdesc'];
                  $image_tags = $imageU['tags'];
                  $active_class = ($image_id == $current_image_id) ? 'active' : '';

                  if ($active_class === 'active') {
                    $active_index = $index;
                  }
                  ?>
                  <div class="carousel-item ratio ratio-16x9 <?php echo $active_class; ?>">
                    <div class="row g-0">
                      <div class="col-md-6">
                        <a href="image.php?artworkid=<?php echo $image_id; ?>">
                          <div class="position-relative h-100">
                            <img class="lazy-load w-100 h-100 object-fit-cover" data-src="thumbnails/<?php echo $image_url; ?>" alt="<?php echo $image_title; ?>">
                          </div>
                        </a>
                      </div>
                      <div class="col-md-6 overflow-auto vh-100">
                        <div class="text-white p-3 my-4">
                          <h5 class="text-center fw-bold shadowed-text">
                            <?php echo $image_title; ?>
                          </h5>
                          <div class="my-3">
                            <?php
                              if (!empty($image_desc)) {
                                $messageText3 = $image_desc;
                                $messageTextWithoutTags3 = strip_tags($messageText3);
                                $pattern3 = '/\bhttps?:\/\/\S+/i';

                                $formattedText3 = preg_replace_callback($pattern3, function ($matches3) {
                                  $url3 = htmlspecialchars($matches3[0]);
                                  return '<a href="' . $url3 . '">' . $url3 . '</a>';
                                }, $messageTextWithoutTags3);

                                echo nl2br($formattedText3); // Display the text with line breaks
                              } else {
                                echo "User description is empty.";
                              }
                            ?>
                          </div>
                          <div class="w-100 mt-4 z-3">
                            <?php
                              if (!empty($image_tags)) {
                                $tags2 = explode(',', $image_tags);
                                foreach ($tags2 as $tag2) {
                                  $tag2 = trim($tag2);
                                    if (!empty($tag2)) {
                                  ?>
                                    <a href="tagged_images.php?tag=<?php echo urlencode($tag2); ?>"
                                      class="btn btn-sm border-0 text-white link-body-emphasis fw-bold">
                                      <i class="bi bi-tags-fill"></i> <?php echo $tag2; ?>
                                    </a>
                                  <?php
                                  }
                                }
                              } else {
                                echo "No tags available.";
                              }
                            ?>
                            <a class="btn btn-sm border-0 text-white link-body-emphasis fw-bold" href="tags.php">
                              <i class="bi bi-tags-fill"></i> all tags
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
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
          </div>
        </div>
      </div>
    </div>
    <div class="mb-3 media-scroller snaps-inline overflow-auto">
      <?php $count = 0; ?>
      <?php foreach ($images as $imageU): ?>
        <?php
          $image_id = $imageU['id'];
          $image_url = $imageU['filename'];
          $image_title = $imageU['title'];
          $current_image_id = isset($_GET['artworkid']) ? $_GET['artworkid'] : null;
        ?>
        <div class="media-element d-inline-flex">
          <a href="image.php?artworkid=<?php echo $image_id; ?>">
            <div class="position-relative overflow-hidden d-inline-block rounded">
              <img class="hori <?php echo ($imageU['type'] === 'nsfw') ? 'blurred' : ''; ?> <?php echo ($image_id == $current_image_id) ? 'opacity-50' : ''; ?>" src="thumbnails/<?php echo $image_url; ?>" alt="<?php echo $image_title; ?>">
            </div>
          </a>
        </div>
        <?php $count++; ?>
        <?php if ($count >= 10) break; ?>
      <?php endforeach; ?>

      <button id="loadMoreBtn" class="btn btn-secondary hori opacity-25"><i class="bi bi-plus-circle display-5 text-stroke"></i></button>
      
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
