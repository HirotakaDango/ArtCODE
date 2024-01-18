
    <div id="content">
      <main id="swup" class="transition-main">
        <?php if (empty($image['filename'])) : ?>
          <div class="position-absolute top-50 start-50 translate-middle text-nowrap">
            <h1 class="fw-bold">Image not found</h1>
            <div class="d-flex justify-content-center">
              <a class="btn btn-primary fw-bold" href="/">back to home</a>
            </div>
          </div>
        <?php else : ?>
          <?php if (empty($child_images)) : ?>
            <div class="w-100 h-100">
              <div class="d-flex justify-content-center vh-100">
                <img src="../../images/<?php echo $image['filename']; ?>" class="object-fit-contain w-100 h-100" alt="<?php echo $image['title']; ?>">
              </div>
            </div>
          <?php else : ?>
            <img src="../../images/<?php echo $image['filename']; ?>" class="w-100 h-100" alt="<?php echo $image['title']; ?>">
          <?php endif; ?>
        <?php endif; ?>
        <?php foreach ($child_images as $child_image) : ?>
          <?php if (empty($child_image['filename'])) : ?>
            <div class="position-absolute top-50 start-50 translate-middle text-nowrap">
              <h1 class="fw-bold">Image not found</h1>
              <div class="d-flex justify-content-center">
                <a class="btn btn-primary fw-bold" href="/">back to home</a>
              </div>
            </div>
          <?php else : ?>
            <img src="../../images/<?php echo $child_image['filename']; ?>" class="mt-1 w-100 h-100" alt="<?php echo $image['title']; ?>">
          <?php endif; ?>
        <?php endforeach; ?>
        <?php
          // Function to calculate the size of an image in MB
          function getImageSizeInMB($filename) {
            return round(filesize('../../images/' . $filename) / (1024 * 1024), 2);
          }

          // Get the total size of images from 'images' table
          $stmt = $db->prepare("SELECT * FROM images WHERE id = :filename");
          $stmt->bindParam(':filename', $filename);
          $stmt->execute();
          $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

          // Get the total size of images from 'image_child' table
          $stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :filename");
          $stmt->bindParam(':filename', $filename);
          $stmt->execute();
          $image_childs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
          $images_total_size = 0;
          foreach ($images as $image) {
            $images_total_size += getImageSizeInMB($image['filename']);
          }

          $image_child_total_size = 0;
          foreach ($image_childs as $image_child) {
            $image_child_total_size += getImageSizeInMB($image_child['filename']);
          }
        
          $total_size = $images_total_size + $image_child_total_size;
        ?>
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content rounded-4 border-0">
              <div class="modal-header border-0">
                <h1 class="modal-title fw-bold fs-5" id="exampleModalLabel"><?php echo $image['title']; ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <h6 class="fw-bold text-center"><?php echo $image['title']; ?></h6>
                <p class="text-start fw-bold mt-4" style="word-wrap: break-word;">
                  <?php
                    if (!empty($image['imgdesc'])) {
                      $messageText = $image['imgdesc'];
                      $messageTextWithoutTags = strip_tags($messageText);
                      $pattern = '/\bhttps?:\/\/\S+/i';

                      $formattedText = preg_replace_callback($pattern, function ($matches) {
                        $url = htmlspecialchars($matches[0]);
                        return '<a href="' . $url . '">' . $url . '</a>';
                      }, $messageTextWithoutTags);

                      $formattedTextWithLineBreaks = nl2br($formattedText);
                        echo $formattedTextWithLineBreaks;
                      } else {
                        echo "Image description is empty.";
                    }
                  ?>
                </p>
                <button class="btn btn-outline-light fw-bold w-100 mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDataImage" aria-expanded="false"aria-controls="collapseExample">
                  <i class="bi bi-caret-down-fill"></i> <small>more</small>
                </button> 
                <div class="collapse mt-2 fw-bold" id="collapseDataImage">
                  <?php
                    // Get the total size of images from 'images' table
                    $stmt = $db->prepare("SELECT * FROM images WHERE id = :filename");
                    $stmt->bindParam(':filename', $filename);
                    $stmt->execute();
                    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Get the total size of images from 'image_child' table
                    $stmt = $db->prepare("SELECT * FROM image_child WHERE image_id = :filename");
                    $stmt->bindParam(':filename', $filename);
                    $stmt->execute();
                    $image_childs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                  
                    // Function to format the date
                    function formatDate($date) {
                      return date('Y/F/l jS') ;
                    }
                  ?>
                  <?php foreach ($images as $index => $image) { ?>
                    <div class="mb-3 img-thumbnail border-light p-3">
                      <div class="mb-3 row">
                        <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">Filename</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php echo $image['filename']; ?>" readonly>
                        </div>
                      </div>
                      <div class="mb-3 row">
                        <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">Image data size</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php echo getImageSizeInMB($image['filename']); ?> MB" readonly>
                        </div>
                      </div>
                      <div class="mb-3 row">
                        <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">Image dimensions</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php list($width, $height) = getimagesize('../../images/' . $image['filename']); echo $width . 'x' . $height; ?>" readonly>
                        </div>
                      </div>
                      <div class="mb-3 row">
                        <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">MIME type</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php echo mime_content_type('../../images/' . $image['filename']); ?>" readonly>
                        </div>
                      </div>
                      <div class="mb-3 row">
                        <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">Image date</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php echo date('Y/m/d', strtotime($image['date'])); ?>" readonly>
                        </div>
                      </div>
                      <div class="mb-3 row">
                        <a class="text-decoration-none text-primary" href="../../images/<?php echo $image['filename']; ?>">
                          <p><i class='bi bi-arrows-fullscreen text-stroke'></i> View original image</p>
                        </a>
                      </div>
                      <div>
                        <a class="text-decoration-none text-primary" href="../../images/<?php echo $image['filename']; ?>" download>
                          <p><i class='bi bi-cloud-arrow-down-fill'></i> Download original image</p>
                        </a>
                      </div>
                    </div>
                  <?php } ?>
                  <?php foreach ($image_childs as $index => $image_child) { ?>
                    <div class="mt-3 mb-3 img-thumbnail border-light p-3">
                      <div class="mb-3 row">
                        <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">Filename</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php echo $image_child['filename']; ?>" readonly>
                        </div>
                      </div>
                      <div class="mb-3 row">
                        <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">Image data size</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php echo getImageSizeInMB($image_child['filename']); ?> MB" readonly>
                        </div>
                      </div>
                      <div class="mb-3 row">
                        <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">Image dimensions</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php list($width, $height) = getimagesize('../../images/' . $image_child['filename']); echo $width . 'x' . $height; ?>" readonly>
                        </div>
                      </div>
                      <div class="mb-3 row">
                        <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">MIME type</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php echo mime_content_type('../../images/' . $image_child['filename']); ?>" readonly>
                        </div>
                      </div>
                      <div class="mb-3 row">
                        <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">Image date</label>
                        <div class="col-sm-8">
                          <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php echo date('Y/m/d', strtotime($image['date'])); ?>" readonly>
                        </div>
                      </div>
                      <div class="mb-3 row">
                        <a class="text-decoration-none text-primary" href="../../images/<?php echo $image_child['filename']; ?>">
                          <p><i class='bi bi-arrows-fullscreen text-stroke'></i> View original image</p>
                        </a>
                      </div>
                      <div>
                        <a class="text-decoration-none text-primary" href="../../images/<?php echo $image_child['filename']; ?>" download>
                          <p><i class='bi bi-cloud-arrow-down-fill'></i> Download original image</p>
                        </a>
                      </div>
                    </div>
                  <?php } ?>
                  <div class="text-white mt-3 mb-3">
                    <ul class="list-unstyled m-0">
                      <li class="mb-2"><i class="bi bi-file-earmark-plus"></i> Total size of all images: <?php echo $total_size; ?> MB</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
    <div id="scrollButton">
      <div class="fixed-top pb-5" style="background: linear-gradient(to bottom, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0));">
        <main id="swup" class="transition-main">
          <div class="d-flex">
            <a class="me-auto border-0 btn rounded" id="option3" href="../../simple_view.php?artworkid=<?php echo $image['id']; ?>"><i class="fs-4 bi bi-x text-stroke-2"></i></a>
            <button class="ms-auto me-1 border-0 btn rounded fw-bold" onclick="sharePage()"><i class="bi bi-share-fill text-stroke"></i> <small>share</small></button>
          </div>
        </main>
        <div class="container-fluid mb-2 d-flex">
          <?php
            $stmt = $db->prepare("SELECT u.id, u.email, u.password, u.artist, u.pic, u.desc, u.bgpic, i.id AS image_id, i.filename, i.tags FROM users u INNER JOIN images i ON u.id = i.id WHERE u.id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
          ?>
          <div class="d-flex d-md-none d-lg-none">
            <a class="text-decoration-none text-light fw-bold rounded-pill" href="../../artist.php?id=<?php echo $user['id']; ?>">
              <?php if (!empty($user['pic'])): ?>
                <img class="object-fit-cover border border-1 rounded-circle" src="../../<?php echo $user['pic']; ?>" style="width: 32px; height: 32px;">
              <?php else: ?>
                <img class="object-fit-cover border border-1 rounded-circle" src="../../icon/profile.svg" style="width: 32px; height: 32px;">
              <?php endif; ?>
              <?php echo (mb_strlen($user['artist']) > 10) ? mb_substr($user['artist'], 0, 10) . '...' : $user['artist']; ?> <small class="badge rounded-pill bg-primary"><i class="bi bi-globe-asia-australia"></i> <?php echo $user['region']; ?></small>
            </a>
          </div>
          <div class="d-flex d-none d-md-block d-lg-block">
            <a class="text-decoration-none text-light fw-bold rounded-pill" href="../../artist.php?id=<?php echo $user['id']; ?>">
              <?php if (!empty($user['pic'])): ?>
                <img class="object-fit-cover border border-1 rounded-circle" src="../../<?php echo $user['pic']; ?>" style="width: 32px; height: 32px;">
              <?php else: ?>
                <img class="object-fit-cover border border-1 rounded-circle" src="../../icon/profile.svg" style="width: 32px; height: 32px;">
              <?php endif; ?>
              <?php echo $user['artist']; ?> <small class="badge rounded-pill bg-primary"><i class="bi bi-globe-asia-australia"></i> <?php echo $user['region']; ?></small>
            </a>
          </div>
          <div class="ms-auto">
            <form method="post">
              <?php if ($is_following): ?>
                <button class="btn btn-sm btn-outline-light rounded-pill fw-bold" type="submit" name="unfollow"><i class="bi bi-person-dash-fill"></i> unfollow</button>
              <?php else: ?>
                <button class="btn btn-sm btn-outline-light rounded-pill fw-bold" type="submit" name="follow"><i class="bi bi-person-fill-add"></i> follow</button>
              <?php endif; ?>
            </form>
          </div>
        </div>
        <main id="swup" class="transition-main">
          <button class="btn btn-sm btn-outline-light rounded-pill ms-2 fw-bold" onclick="window.location.href='?by=horizontal&artworkid=<?php echo $image['id']; ?>'">horizontal</button>
        </main>
      </div>
      <div class="w-100 fixed-bottom" style="background: linear-gradient(to top, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0));">
        <div class="container pt-3 d-flex justify-content-center">
          <div class="btn-group gap-3">
            <main id="swup" class="transition-main">
              <?php if ($next_image): ?>
                <a class="text-start fw-bold btn rounded" id="option5" href="?by=vertical&artworkid=<?= $next_image['id'] ?>">
                  <i class="fs-4 bi bi-chevron-left text-stroke-3"></i>
                </a>
              <?php endif; ?> 
            </main>
            <main id="swup" class="transition-main">
              <button class="btn rounded" onclick="window.location.href='../carousel/?artworkid=<?php echo $image['id']; ?>'"><i class="fs-4 bi bi-distribute-horizontal"></i></button>
            </main>
            <button class="btn rounded" id="option1"><i class="fs-4 bi bi-fullscreen text-stroke-2"></i></button>
            <button class="btn rounded" id="option2"><i class="fs-4 bi bi-file-image"></i></button>
            <main id="swup" class="transition-main">
              <?php
                $image_id = $image['id'];
                $stmt = $db->query("SELECT COUNT(*) FROM favorites WHERE image_id = $image_id");
                $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE email = :email AND image_id = :image_id");
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':image_id', $image_id);
                $stmt->execute();
                $is_favorited = $stmt->fetchColumn();
                if ($is_favorited) : ?>
                <form class="w-100" method="POST">
                  <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                  <button type="submit" class="text-start btn" name="unfavorite" id="unfavoriteButton">
                    <i class="fs-4 bi bi-heart-fill"></i>
                  </button>
                </form>
              <?php else : ?>
                <form class="w-100" method="POST">
                  <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                  <button type="submit" class="text-start btn" name="favorite" id="favoriteButton">
                    <i class="fs-4 bi bi-heart text-stroke"></i>
                  </button>
                </form>
              <?php endif; ?>
            </main>
            <main id="swup" class="transition-main">
              <button class="text-start fw-bold btn rounded" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="fs-4 bi bi-info-circle-fill"></i></button>
            </main>
            <main id="swup" class="transition-main">
              <?php if ($prev_image): ?>
                <a class="text-start fw-bold btn rounded" id="option6" href="?by=vertical&artworkid=<?= $prev_image['id'] ?>">
                  <i class="fs-4 bi bi-chevron-right text-stroke-3"></i>
                </a>
              <?php endif; ?>
            </main>
          </div>
        </div>
        <div class="container pb-2">
          <input type="range" class="form-range" id="customRange1" value="0">
        </div>
      </div>
    </div>
    <div class="modal fade" id="swipeModal" tabindex="-1" aria-labelledby="swipeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-body">
            <h5 class="fw-bold text-center mb-3">Information: </h5>
            <p class="fw-bold text-start">1. Swipe to left or right to navigate.</p>
            <p class="fw-bold text-start">2. Double tap to show or hide the navbar.</p>
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="dontRemindCheckbox">
              <label class="form-check-label fw-bold" for="dontRemindCheckbox">Don't remind me again!</label>
            </div>
            <button type="button" class="mt-3 btn btn-outline-light fw-bold w-100" data-bs-dismiss="modal">Okay, I understand!</button>
          </div>
        </div>
      </div>
    </div>
    <style>
      #scrollButton {
        transition: opacity 0.5s ease-in-out; /* Add smooth opacity transition */
        opacity: 1; /* Initially visible */
      }
        
      .text-stroke {
        -webkit-text-stroke: 1px;
      }

      .text-stroke-2 {
        -webkit-text-stroke: 2px;
      }
      
      .text-stroke-3 {
        -webkit-text-stroke: 3px;
      }
      
      .my-6 {
        margin-top: 150px;
        margin-bottom: 150px;
      }

      body {
        overflow: auto;
        scrollbar-width: thin;  /* For Firefox */
        -ms-overflow-style: none;  /* For Internet Explorer and Edge */
        scrollbar-color: transparent transparent;  /* For Chrome, Safari, and Opera */
      }

      body::-webkit-scrollbar {
        width: 0;
        background-color: transparent;
      }
      
      body::-webkit-scrollbar-thumb {
        background-color: transparent;
      }
    </style>
    <script>
      var isButtonVisible = true; // Set the initial state to visible
      var lastClickTime = 0;

      function toggleButtonVisibility(event) {
        var currentTime = new Date().getTime();
        var clickTimeDiff = currentTime - lastClickTime;

        if (clickTimeDiff < 300) {
          // Double-click detected
          toggleVisibility();
        }
        lastClickTime = currentTime;
      }

      function toggleVisibility() {
        var scrollButton = document.getElementById("scrollButton");
        if (isButtonVisible) {
          scrollButton.style.opacity = "0";
        } else {
          scrollButton.style.opacity = "1";
        }
        isButtonVisible = !isButtonVisible;
      }

      // Add an event listener for both touch and mouse click events
      document.addEventListener("click", toggleButtonVisibility);

      const option1Button = document.getElementById('option1');
      const option2Button = document.getElementById('option2');
      const contentDiv = document.getElementById('content');

      option1Button.style.display = 'none'; // Hide option1 by default

      option1Button.addEventListener('click', function () {
        contentDiv.classList.remove('container-sm', 'my-6', 'w-75');
        option1Button.style.display = 'none'; // Hide option1
        option2Button.style.display = 'block'; // Show option2
      });

      option2Button.addEventListener('click', function () {
        contentDiv.classList.add('container-sm', 'my-6', 'w-75');
        option1Button.style.display = 'block'; // Show option1
        option2Button.style.display = 'none'; // Hide option2
      });

      function sharePage() {
        if (navigator.share) {
          navigator.share({
            title: document.title,
            url: window.location.href
          }).then(() => {
            console.log('Page shared successfully.');
          }).catch((error) => {
            console.error('Error sharing page:', error);
          });
        } else {
          window.open("?artworkid=<?php echo $image['id']; ?>", "_blank");
        }
      }

      // Wait for the document to be fully loaded
      document.addEventListener("DOMContentLoaded", function () {
        // Check if the modal has been shown before
        var hasModalBeenShown = localStorage.getItem("hasModalBeenShown");

        if (!hasModalBeenShown || localStorage.getItem("dontRemindAgain") !== "true") {
          // Select the modal element by its ID
          var modal = document.getElementById("swipeModal");

          // Show the modal
          var modalInstance = new bootstrap.Modal(modal);
          modalInstance.show();

          // Set a flag in localStorage to indicate that the modal has been shown
          localStorage.setItem("hasModalBeenShown", "true");

          // Listen for changes to the "Don't remind me again!" checkbox
          var dontRemindCheckbox = document.getElementById("dontRemindCheckbox");
          dontRemindCheckbox.addEventListener("change", function () {
            if (dontRemindCheckbox.checked) {
              // If checked, set a flag in localStorage to not show the modal again
              localStorage.setItem("dontRemindAgain", "true");
            } else {
              // If unchecked, remove the flag
              localStorage.removeItem("dontRemindAgain");
            }
          });
        }
      });

      // Get a reference to the range input element
      const slider = document.getElementById("customRange1");

      // Function to update the slider value based on the current scroll position
      function updateSliderValue() {
        // Calculate the current scroll position as a percentage of the maximum scrollable height
        const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
        const currentScroll = (window.scrollY / maxScroll) * 100;

        // Set the slider's value to match the current scroll position
        slider.value = currentScroll;
      }

      // Function to handle slider input change
      function handleSliderChange() {
        // Calculate the scroll position based on the slider's value
        const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
        const targetScroll = (maxScroll * slider.value) / 100;

        // Smoothly scroll to the target position
        window.scrollTo({
          top: targetScroll,
          behavior: "smooth"
        });
      }

      // Add an event listener to respond to slider changes
      slider.addEventListener("change", handleSliderChange);

      // Synchronize the slider value with the current scroll position on page load
      window.addEventListener("load", function () {
        updateSliderValue();
      });

      // Add a scroll event listener to continuously update the slider value
      window.addEventListener("scroll", function () {
        updateSliderValue();
      });

      // Optional: Add a resize event listener to handle changes in window size
      window.addEventListener("resize", function () {
        updateSliderValue();
      });
    </script>
