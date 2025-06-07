        <?php
          // Function to calculate the size of an image in MB
          function getImageSizeInMB($filename) {
            return round(filesize('../private_images/' . $filename) / (1024 * 1024), 2);
          }

          // Get the total size of private_images from 'images' table
          $stmt = $db->prepare("SELECT * FROM private_images WHERE id = :artworkid");
          $stmt->bindParam(':artworkid', $artworkId);
          $stmt->execute();
          $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

          // Get the total size of private_images from 'image_child' table
          $stmt = $db->prepare("SELECT * FROM private_image_child WHERE image_id = :artworkid");
          $stmt->bindParam(':artworkid', $artworkId);
          $stmt->execute();
          $image_childs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                              
          // Function to format the date
          function formatDate($date) {
            return date('Y/F/l jS') ;
          }

          $images_total_size = 0;
          foreach ($images as $image) {
            $images_total_size += getImageSizeInMB($image['filename']);
          }

          $image_child_total_size = 0;
          foreach ($image_childs as $image_child) {
            $image_child_total_size += getImageSizeInMB($image_child['filename']);
          }
                            
          $total_size = $images_total_size + $image_child_total_size;

          $image_id = $image['id'];
          $stmt = $db->query("SELECT COUNT(*) FROM private_favorites WHERE image_id = $image_id");
          $fav_count = $stmt->fetchColumn();
          if ($fav_count >= 1000000000) {
            $fav_count = round($fav_count / 1000000000, 1) . 'b';
          } elseif ($fav_count >= 1000000) {
            $fav_count = round($fav_count / 1000000, 1) . 'm';
          } elseif ($fav_count >= 1000) {
            $fav_count = round($fav_count / 1000, 1) . 'k';
          }
          $stmt = $db->prepare("SELECT COUNT(*) FROM private_favorites WHERE email = :email AND image_id = :image_id");
          $stmt->bindParam(':email', $email);
          $stmt->bindParam(':image_id', $image_id);
          $stmt->execute();
          $is_favorited = $stmt->fetchColumn();
        ?>
        <!-- Second Section -->
        <div class="cool-6">
          <div class="caard border-md-lg">
            <div class="container-fluid mb-4 d-none d-md-flex d-lg-flex">
              <?php
                $stmt = $db->prepare("SELECT u.id, u.email, u.password, u.region, u.artist, u.pic, u.desc, u.bgpic, i.id AS image_id, i.filename, i.tags FROM users u INNER JOIN private_images i ON u.id = i.id WHERE u.id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
              ?>
              <div class="d-flex">
                <a class="text-decoration-none text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold rounded-pill" href="#" data-bs-toggle="modal" data-bs-target="#userModal">
                 <?php if (!empty($user['pic'])): ?>
                   <img class="object-fit-cover border border-1 rounded-circle" src="<?php echo $user['pic']; ?>" style="width: 32px; height: 32px;">
                  <?php else: ?>
                    <img class="object-fit-cover border border-1 rounded-circle" src="/icon/profile.svg" style="width: 32px; height: 32px;">
                  <?php endif; ?>
                  <?php echo (mb_strlen($user['artist']) > 20) ? mb_substr($user['artist'], 0, 20) . '...' : $user['artist']; ?> <small class="badge rounded-pill text-bg-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>"><i class="bi bi-globe-asia-australia"></i> <?php echo $user['region']; ?></small>
                </a>
              </div>
            </div>
            <div class="me-2 ms-2 rounded fw-bold">
              <div class="d-flex d-md-none d-lg-none gap-2">
                <?php if (basename($_SERVER['PHP_SELF']) !== 'private_simplest_view.php'): ?>
                  <?php if ($next_image): ?>
                    <a class="image-containerA shadow rounded" href="?<?= http_build_query(array_merge($_GET, ['artworkid' => $next_image['id']])) ?>">
                      <div class="position-relative">
                        <div class="ratio ratio-1x1">
                          <img class="img-blur object-fit-cover rounded opacity-75" src="/private_thumbnails/<?php echo $next_image['filename']; ?>" alt="<?php echo $next_image['title']; ?>">
                        </div>
                        <h6 class="fw-bold shadowed-text position-absolute text-white top-50 start-50 translate-middle">
                          <i class="bi bi-arrow-left-circle text-stroke"></i> Next
                        </h6>
                      </div>
                    </a>
                  <?php else: ?>
                    <a class="image-containerA shadow rounded" href="/artist.php?<?= http_build_query(array_merge($_GET, ['by' => 'newest', 'id' => $user['id']])) ?>">
                      <div class="position-relative">
                        <?php if (!empty($user['pic'])): ?>
                          <div class="ratio ratio-1x1">
                            <img class="img-blur object-fit-cover rounded opacity-75" alt="<?php echo $user['artist']; ?>" src="<?php echo $user['pic']; ?>">
                          </div>
                        <?php else: ?>
                          <div class="ratio ratio-1x1">
                            <img class="img-blur object-fit-cover rounded opacity-75" alt="<?php echo $user['artist']; ?>" src="/icon/profile.svg">
                          </div>
                        <?php endif; ?>
                        <h6 class="fw-bold shadowed-text position-absolute text-white top-50 start-50 translate-middle">
                          <i class="bi bi-box-arrow-in-up-left text-stroke"></i> All
                        </h6>
                      </div>
                    </a>
                  <?php endif; ?>
                  <a class="image-containerA shadow rounded" href="?<?= http_build_query(array_merge($_GET, ['artworkid' => $image['id']])) ?>">
                    <div class="ratio ratio-1x1">
                      <img class="object-fit-cover opacity-50 rounded" src="/private_thumbnails/<?= $image['filename'] ?>" alt="<?php echo $image['title']; ?>">
                    </div>
                  </a>
                  <?php if ($prev_image): ?>
                    <a class="image-containerA shadow rounded" href="?<?= http_build_query(array_merge($_GET, ['artworkid' => $prev_image['id']])) ?>">
                      <div class="position-relative">
                        <div class="ratio ratio-1x1">
                          <img class="img-blur object-fit-cover rounded opacity-75" src="/private_thumbnails/<?php echo $prev_image['filename']; ?>" alt="<?php echo $prev_image['title']; ?>">
                        </div>
                        <h6 class="fw-bold shadowed-text position-absolute text-white top-50 start-50 translate-middle">
                          Prev <i class="bi bi-arrow-right-circle text-stroke"></i>
                        </h6>
                      </div>
                    </a>
                  <?php else: ?>
                    <a class="image-containerA shadow rounded" href="/artist.php?<?= http_build_query(array_merge($_GET, ['by' => 'newest', 'id' => $user['id']])) ?>">
                      <div class="position-relative">
                        <?php if (!empty($user['pic'])): ?>
                          <div class="ratio ratio-1x1">
                            <img class="img-blur object-fit-cover rounded opacity-75" alt="<?php echo $user['artist']; ?>" src="<?php echo $user['pic']; ?>">
                          </div>
                        <?php else: ?>
                          <div class="ratio ratio-1x1">
                            <img class="img-blur object-fit-cover rounded opacity-75" alt="<?php echo $user['artist']; ?>" src="/icon/profile.svg">
                          </div>
                        <?php endif; ?>
                        <h6 class="fw-bold shadowed-text position-absolute text-white top-50 start-50 translate-middle">
                          All <i class="bi bi-box-arrow-in-up-right text-stroke"></i>
                        </h6>
                      </div>
                    </a>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
              <h5 class="text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold text-center mt-3"><?php echo $image['title']; ?></h5>
              <div style="word-break: break-word;" data-lazyload>
                <p class="text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> small fw-medium my-4" style="word-break: break-word;">
                  <?php
                    if (!empty($image['imgdesc'])) {
                      $messageText = $image['imgdesc'];
                      $messageTextWithoutTags = strip_tags($messageText);
                      $pattern = '/\bhttps?:\/\/\S+/i';

                      $formattedText = preg_replace_callback($pattern, function ($matches) {
                        $url = htmlspecialchars($matches[0]);
                        return '<a href="' . $url . '">' . $url . '</a>';
                      }, $messageTextWithoutTags);

                      $charLimit = 400; // Set your character limit

                      if (strlen($formattedText) > $charLimit) {
                        $limitedText = substr($formattedText, 0, $charLimit);
                        echo '<span id="limitedText1">' . nl2br($limitedText) . '...</span>'; // Display the capped text with line breaks and "..."
                        echo '<span id="more1" style="display: none;">' . nl2br($formattedText) . '</span>'; // Display the full text initially hidden with line breaks
                        echo '</br><button class="btn btn-sm mt-2 fw-medium p-0 border-0" onclick="myFunction1()" id="myBtn1"><small>read more</small></button>';
                      } else {
                        // If the text is within the character limit, just display it with line breaks.
                        echo nl2br($formattedText);
                      }
                    } else {
                      echo "User description is empty.";
                    }
                  ?>
                  <script>
                    function initializeReadMore() {
                      function myFunction1() {
                        var dots1 = document.getElementById("limitedText1");
                        var moreText1 = document.getElementById("more1");
                        var btnText1 = document.getElementById("myBtn1");

                        if (moreText1.style.display === "none") {
                          dots1.style.display = "none";
                          moreText1.style.display = "inline";
                          btnText1.innerHTML = "read less";
                        } else {
                          dots1.style.display = "inline";
                          moreText1.style.display = "none";
                          btnText1.innerHTML = "read more";
                        }
                      }

                      // Attach the function to the button
                      const btn = document.getElementById("myBtn1");
                      if (btn) {
                        btn.onclick = myFunction1;
                      }
                    }

                    // Initialize functionality on page load
                    document.addEventListener('DOMContentLoaded', initializeReadMore);

                    // Reinitialize functionality after swup.js replaces content
                    document.addEventListener('swup:contentReplaced', initializeReadMore);
                  </script>
                </p>
              </div>
              <p class="text-secondary" style="word-wrap: break-word;">
                <a class="text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" href="<?php echo $image['link']; ?>">
                  <small>
                    <?php echo (strlen($image['link']) > 40) ? substr($image['link'], 0, 40) . '...' : $image['link']; ?>
                  </small>
                </a>
              </p>

              <div class="container-fluid bg-body-tertiary p-2 mt-2 mb-2 rounded-4 text-center align-items-center d-flex justify-content-center">
                <div class="dropdown-center">
                  <button class="btn border-0 fw-medium" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <small>
                      <?php echo date('Y/m/d', strtotime($image['date'])); ?>
                    </small
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item fw-medium text-center" href="#">
                        uploaded at <?php echo date('F j, Y', strtotime($image['date'])); ?>
                      </a>
                    </li>
                  </ul>
                </div>
                <div class="dropdown-center">
                  <button class="btn border-0 fw-medium" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-heart-fill text-sm"></i> <small><?php echo $fav_count; ?></small>
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item fw-medium text-center" href="#">
                        total <?php echo $fav_count; ?> private_favorites
                      </a>
                    </li>
                  </ul>
                </div>
                <div class="dropdown-center">
                  <button class="btn border-0 fw-medium" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-eye-fill"></i> <small><?php echo $viewCount; ?></small>
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item fw-medium text-center" href="#">
                        total <?php echo $viewCount; ?> views
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="w-100 my-2">
                <a class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-4 fw-bold w-100" href="/similar_image_search/?image=/../private_images/<?php echo urlencode($image['filename']); ?>">
                  <small>find similar image</small>
                </a>
              </div>
              <?php if (basename($_SERVER['PHP_SELF']) !== 'private_simplest_view.php'): ?>
                <?php if (isset($image['episode_name']) && !empty($image['episode_name'])): ?>
                  <a class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold rounded-4 mb-2 w-100" href="/episode/?title=<?php echo urlencode($image['episode_name']); ?>&uid=<?php echo $user['id']; ?>">
                    <small>all episodes from <?php echo $image['episode_name']; ?></small>
                  </a>
                  <div class="btn-group gap-2 w-100 mb-2">
                    <a class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold rounded-4 w-50" target="_blank" href="/feeds/manga/title.php?title=<?php echo urlencode($image['episode_name']); ?>&uid=<?php echo $user['id']; ?>">
                      <small>go to manga</small>
                    </a>
                    <a class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold rounded-4 w-50" target="_blank" href="/view/manga/?artworkid=<?php echo $image['id']; ?>&page=1">
                      <small>read in manga mode</small>
                    </a>
                  </div>
                <?php endif; ?>
                <div class="btn-group w-100 gap-2 mb-2">
                  <button type="button" class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold rounded-4 w-50" data-bs-toggle="modal" data-bs-target="#previewMangaModal">
                    <small>all previews</small>
                  </button>
                  <a class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold rounded-4 w-50" href="#" id="originalImageLink" data-bs-toggle="modal" data-bs-target="#originalImageModal">
                    <small>modal preview</small>
                  </a>
                </div>
              <?php endif; ?>
              <?php if (!in_array(basename($_SERVER['PHP_SELF']), ['simple_view.php', 'simplest_view.php', 'view.php']) && basename($_SERVER['PHP_SELF']) === 'full_view.php'): ?>
                <button type="button" class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold rounded-4 w-100 mb-2" data-bs-toggle="modal" data-bs-target="#previewLatestPopularModal">
                  <small>latest and popular</small>
                </button>
                <?php include('modal_latest_popular.php'); ?>
              <?php endif; ?>
              <div class="btn-group w-100" role="group" aria-label="Basic example">
                <button class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold rounded-start-4" data-bs-toggle="modal" data-bs-target="#shareLink">
                  <i class="bi bi-share-fill"></i> <small>share</small>
                </button>
                <a class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold" data-bs-toggle="modal" data-bs-target="#downloadOption">
                  <i class="bi bi-cloud-arrow-down-fill"></i> <small>download</small>
                </a>
                <button class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> dropdown-toggle fw-bold rounded-end-4" type="button" class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" data-bs-toggle="modal" data-bs-target="#dataModal">
                  <i class="bi bi-info-circle-fill"></i> <small>info</small>
                </button>

                <!-- Original Image Modal -->
                <div class="modal fade" id="originalImageModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-swup-reload>
                  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl modal-fullscreen-sm-down">
                    <div class="modal-content border-0 p-0 rounded-min-4">
                      <div class="d-flex align-items-center justify-content-between p-2">
                        <h5 class="fw-medium text-truncate ms-2">Preview <?php echo $image['title']; ?></h5>
                        <button type="button" class="btn border-0" data-bs-dismiss="modal">
                          <i class="bi bi-chevron-down fs-5" style="-webkit-text-stroke: 3px;"></i>
                        </button>
                      </div>
                      <iframe id="modalIframe" class="vh-100 w-100" sandbox="allow-scripts allow-same-origin"></iframe>
                    </div>
                  </div>
                </div>
                <script>
                  function initializeModal() {
                    const modalElement = document.getElementById('originalImageModal');
                    const iframeElement = document.getElementById('modalIframe');
                    const iframeSrc = 'private_artworkid.php?artworkid=<?php echo $image["id"]; ?>';

                    if (modalElement) {
                      modalElement.addEventListener('show.bs.modal', function () {
                        iframeElement.src = iframeSrc; // Always reload the iframe when the modal is opened
                      });

                      modalElement.addEventListener('hidden.bs.modal', function () {
                        iframeElement.src = ''; // Unload content when modal is closed
                      });
                    }
                  }

                  // Initialize modal functionality on page load
                  document.addEventListener('DOMContentLoaded', initializeModal);

                  // Reinitialize modal functionality after swup.js replaces the content
                  document.addEventListener('swup:contentReplaced', initializeModal);
                </script>
                <!-- End of Original Image Modal -->
            
                <!-- Data Modal -->
                <div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                    <div class="modal-content rounded-4 border-0">
                      <div class="modal-header border-0">
                        <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">All Data from <?php echo $image['title']; ?></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <div>
                          <div class="text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> text-center mt-2 mb-4">
                            <h6 class="fw-bold"><i class="bi bi-file-earmark-plus"></i> Total size of all private_images: <?php echo $total_size; ?> MB</h6>
                          </div>
                          <button class="btn btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold w-100 mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDataImage1" aria-expanded="false" aria-controls="collapseExample">
                            <small>show more</small>
                          </button>
                          <div class="collapse mt-2" id="collapseDataImage1">
                            <?php foreach ($images as $index => $image) { ?>
                              <div class="mb-3 img-thumbnail bg-body-tertiary shadow border-0 p-3">
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
                                    <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php list($width, $height) = getimagesize('../private_images/' . $image['filename']); echo $width . 'x' . $height; ?>" readonly>
                                  </div>
                                </div>
                                <div class="mb-3 row">
                                  <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">MIME type</label>
                                  <div class="col-sm-8">
                                    <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php echo mime_content_type('../private_images/' . $image['filename']); ?>" readonly>
                                  </div>
                                </div>
                                <div class="mb-3 row">
                                  <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">Image date</label>
                                  <div class="col-sm-8">
                                    <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php echo date('Y/m/d', strtotime($image['date'])); ?>" readonly>
                                  </div>
                                </div>
                                <div class="mb-3 row">
                                  <a class="text-decoration-none text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" href="../private_images/<?php echo $image['filename']; ?>">
                                    <p><i class='bi bi-arrows-fullscreen text-stroke'></i> View original image</p>
                                  </a>
                                </div>
                                <div>
                                  <a class="text-decoration-none text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" href="../private_images/<?php echo $image['filename']; ?>" download>
                                    <p><i class='bi bi-cloud-arrow-down-fill'></i> Download original image</p>
                                  </a>
                                </div>
                              </div>
                            <?php } ?>
                            <?php foreach ($image_childs as $index => $image_child) { ?>
                              <div class="mt-3 mb-3 img-thumbnail bg-body-tertiary shadow border-0 p-3">
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
                                    <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php list($width, $height) = getimagesize('../private_images/' . $image_child['filename']); echo $width . 'x' . $height; ?>" readonly>
                                  </div>
                                </div>
                                <div class="mb-3 row">
                                  <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">MIME type</label>
                                  <div class="col-sm-8">
                                    <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php echo mime_content_type('../private_images/' . $image_child['filename']); ?>" readonly>
                                  </div>
                                </div>
                                <div class="mb-3 row">
                                  <label for="" class="col-sm-4 col-form-label text-nowrap fw-medium">Image date</label>
                                  <div class="col-sm-8">
                                    <input type="text" class="form-control-plaintext fw-bold" id="" value="<?php echo date('Y/m/d', strtotime($image['date'])); ?>" readonly>
                                  </div>
                                </div>
                                <div class="mb-3 row">
                                  <a class="text-decoration-none text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" href="../private_images/<?php echo $image_child['filename']; ?>">
                                    <p><i class='bi bi-arrows-fullscreen text-stroke'></i> View original image</p>
                                  </a>
                                </div>
                                <div>
                                  <a class="text-decoration-none text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" href="../private_images/<?php echo $image_child['filename']; ?>" download>
                                    <p><i class='bi bi-cloud-arrow-down-fill'></i> Download original image</p>
                                  </a>
                                </div>
                              </div>
                            <?php } ?>
                            <a class="btn btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold w-100" href="#downloadOption" data-bs-toggle="modal">
                              <i class="bi bi-cloud-arrow-down-fill"></i> download all
                            </a>
                            <?php
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
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- End of Data Modal -->

                <!-- Download Option Modal -->
                <div class="modal fade" id="downloadOption" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content rounded-4 border-0">
                      <div class="modal-header border-0">
                        <h1 class="modal-title fw-bold fs-5" id="exampleModalToggleLabel">Download Option</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body scrollable-div">
                        <a class="btn btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold w-100 mb-2 text-center rounded-3" href="../private_images/<?php echo $image['filename']; ?>" download>
                          <i class="bi bi-cloud-arrow-down-fill"></i> Download first image (<?php echo getImageSizeInMB($image['filename']); ?> MB)
                        </a>
                        <?php if ($total_size > 10): ?>
                          <a type="button" class="btn btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold w-100 mb-2 text-center rounded-3" href="/download_images.php?artworkid=<?php echo $image['id']; ?>">
                            <p><i class="bi bi-file-earmark-zip-fill"></i> Download all private_images (<?php echo $total_size; ?> MB)</p>
                            <p><small>This file is too big. The total size is <?php echo $total_size; ?> MB.</small></p>
                          </a>
                        <?php else: ?>
                          <a class="btn btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold w-100 mb-2 text-center rounded-3" href="/download_images.php?artworkid=<?php echo $image['id']; ?>">
                            <i class="bi bi-file-earmark-zip-fill"></i> Download all private_images (<?php echo $total_size; ?> MB)
                          </a>
                        <?php endif; ?>
                        <div class="progress fw-bold" style="height: 30px; display: none;">
                          <div class="progress-bar progress-bar progress-bar-animated fw-bold" style="width: 0; height: 30px;" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="progress-bar1">0%</div>
                        </div>
                        <h5 class="fw-bold text-center mt-2">Please Note!</h5>
                        <p class="fw-bold text-center container">
                          <small>1. Download can take a really long time, wait until progress bar reach 100% or appear download pop up in the notification.</small>
                        </p>
                        <p class="fw-bold text-center container">
                          <small>2. If you found download error or failed, <a class="text-decoration-none" href="download_batch.php?artworkid=<?= $image_id; ?>">click this link</a> for third option if download all private_images error or failed.</small>
                        </p>
                        <p class="fw-bold text-center container">
                          <small>3. If you found problem where the zip contain empty file or 0b, download the private_images manually.</small>
                        </p>
                        <p class="fw-bold text-center container">
                          <small>4. Server sometimes have problem with file and folder path, download manually is the best option if this happening.</small>
                        </p>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold w-100 text-center rounded-3" data-bs-dismiss="modal">cancel</button>
                      </div>
                    </div>
                  </div>
                </div>
                <!--  End of Download Option Modal -->

                <!-- Share Modal -->
                <div class="modal fade" id="shareLink" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4 border-0">
                      <div class="card rounded-4 p-4">
                        <p class="text-start fw-bold">share to:</p>
                        <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
                          <!-- Twitter -->
                          <a class="btn border-0" href="https://twitter.com/intent/tweet?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/private_image.php?artworkid=' . $image['id']; ?>">
                            <i class="bi bi-twitter"></i>
                          </a>
                            
                          <!-- Line -->
                          <a class="btn border-0" href="https://social-plugins.line.me/lineit/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/private_image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-line"></i>
                          </a>
                            
                          <!-- Email -->
                          <a class="btn border-0" href="mailto:?body=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/private_image.php?artworkid=' . $image['id']; ?>">
                            <i class="bi bi-envelope-fill"></i>
                          </a>
                            
                          <!-- Reddit -->
                          <a class="btn border-0" href="https://www.reddit.com/submit?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/private_image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-reddit"></i>
                          </a>
                            
                          <!-- Instagram -->
                          <a class="btn border-0" href="https://www.instagram.com/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/private_image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-instagram"></i>
                          </a>
                            
                          <!-- Facebook -->
                          <a class="btn border-0" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/private_image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-facebook"></i>
                          </a>
                        </div>
                        <!-- Second Social Media Section -->
                        <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
                          <!-- WhatsApp -->
                          <a class="btn border-0" href="https://wa.me/?text=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/private_image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-whatsapp"></i>
                          </a>
              
                          <!-- Pinterest -->
                          <a class="btn border-0" href="https://pinterest.com/pin/create/button/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/private_image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-pinterest"></i>
                          </a>
              
                          <!-- LinkedIn -->
                          <a class="btn border-0" href="https://www.linkedin.com/shareArticle?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/private_image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-linkedin"></i>
                          </a>
              
                          <!-- Messenger -->
                          <a class="btn border-0" href="https://www.facebook.com/dialog/send?link=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/private_image.php?artworkid=' . $image['id']; ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-messenger"></i>
                          </a>
              
                          <!-- Telegram -->
                          <a class="btn border-0" href="https://telegram.me/share/url?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/private_image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-telegram"></i>
                          </a>
              
                          <!-- Snapchat -->
                          <a class="btn border-0" href="https://www.snapchat.com/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/private_image.php?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-snapchat"></i>
                          </a>
                        </div>
                        <!-- End -->
                        <div class="input-group mb-2">
                          <input type="text" id="urlInput1" value="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/private_image.php?artworkid=' . $image['id']; ?>" class="form-control border-2 fw-bold" readonly>
                          <button class="btn btn-secondary opacity-50 fw-bold" onclick="copyToClipboard1()">
                            <i class="bi bi-clipboard-fill"></i>
                          </button>
                        </div>
                        <h6 class="small fw-medium mt-3">Note: private artwork can't be shared with anyone, you can keep the link as your favorite list.</h6>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- End of Share Modal -->

              </div>

              <!-- Preview Modal -->
              <div class="modal fade" id="previewMangaModal" tabindex="-1" aria-labelledby="previewMangaModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen m-0 p-0">
                  <div class="modal-content m-0 p-0 border-0 rounded-0">
                    <iframe class="w-100 h-100 border-0" src="<?php echo 'private_preview.php?artworkid=' . $_GET['artworkid']; ?>"></iframe>
                    <button type="button" class="btn btn-small btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium position-fixed bottom-0 end-0 m-2 rounded-pill" data-bs-dismiss="modal">close</button>
                  </div>
                </div>
              </div>
              <!-- End of Preview Modal -->
              
              <?php if (basename($_SERVER['PHP_SELF']) !== 'private_simplest_view.php'): ?>
                <div class="d-none d-md-flex d-lg-flex mt-2 mb-0 gap-2">
                  <?php if ($next_image): ?>
                    <a class="image-containerA shadow rounded" href="?<?= http_build_query(array_merge($_GET, ['artworkid' => $next_image['id']])) ?>">
                      <div class="position-relative">
                        <div class="ratio ratio-1x1">
                          <img class="img-blur object-fit-cover rounded opacity-75" src="/private_thumbnails/<?php echo $next_image['filename']; ?>" alt="<?php echo $next_image['title']; ?>">
                        </div>
                        <h6 class="fw-bold shadowed-text position-absolute text-white top-50 start-50 translate-middle">
                          <i class="bi bi-arrow-left-circle text-stroke"></i> Next
                        </h6>
                      </div>
                    </a>
                  <?php else: ?>
                    <a class="image-containerA shadow rounded" href="/artist.php?<?= http_build_query(array_merge($_GET, ['by' => 'newest', 'id' => $user['id']])) ?>">
                      <div class="position-relative">
                        <?php if (!empty($user['pic'])): ?>
                          <div class="ratio ratio-1x1">
                            <img class="img-blur object-fit-cover rounded opacity-75" alt="<?php echo $user['artist']; ?>" src="<?php echo $user['pic']; ?>">
                          </div>
                        <?php else: ?>
                          <div class="ratio ratio-1x1">
                            <img class="img-blur object-fit-cover rounded opacity-75" alt="<?php echo $user['artist']; ?>" src="/icon/profile.svg">
                          </div>
                        <?php endif; ?>
                        <h6 class="fw-bold shadowed-text position-absolute text-white top-50 start-50 translate-middle">
                          <i class="bi bi-box-arrow-in-up-left text-stroke"></i> All
                        </h6>
                      </div>
                    </a>
                  <?php endif; ?>
                  <a class="image-containerA shadow rounded" href="?<?= http_build_query(array_merge($_GET, ['artworkid' => $image['id']])) ?>">
                    <div class="ratio ratio-1x1">
                      <img class="object-fit-cover opacity-50 rounded" src="/private_thumbnails/<?= $image['filename'] ?>" alt="<?php echo $image['title']; ?>">
                    </div>
                  </a>
                  <?php if ($prev_image): ?>
                    <a class="image-containerA shadow rounded" href="?<?= http_build_query(array_merge($_GET, ['artworkid' => $prev_image['id']])) ?>">
                      <div class="position-relative">
                        <div class="ratio ratio-1x1">
                          <img class="img-blur object-fit-cover rounded opacity-75" src="/private_thumbnails/<?php echo $prev_image['filename']; ?>" alt="<?php echo $prev_image['title']; ?>">
                        </div>
                        <h6 class="fw-bold shadowed-text position-absolute text-white top-50 start-50 translate-middle">
                          Prev <i class="bi bi-arrow-right-circle text-stroke"></i>
                        </h6>
                      </div>
                    </a>
                  <?php else: ?>
                    <a class="image-containerA shadow rounded" href="/artist.php?<?= http_build_query(array_merge($_GET, ['by' => 'newest', 'id' => $user['id']])) ?>">
                      <div class="position-relative">
                        <?php if (!empty($user['pic'])): ?>
                          <div class="ratio ratio-1x1">
                            <img class="img-blur object-fit-cover rounded opacity-75" alt="<?php echo $user['artist']; ?>" src="<?php echo $user['pic']; ?>">
                          </div>
                        <?php else: ?>
                          <div class="ratio ratio-1x1">
                            <img class="img-blur object-fit-cover rounded opacity-75" alt="<?php echo $user['artist']; ?>" src="/icon/profile.svg">
                          </div>
                        <?php endif; ?>
                        <h6 class="fw-bold shadowed-text position-absolute text-white top-50 start-50 translate-middle">
                          All <i class="bi bi-box-arrow-in-up-right text-stroke"></i>
                        </h6>
                      </div>
                    </a>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
              <a class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-4 fw-bold w-100 mt-2" style="word-wrap: break-word;" href="/artist.php?id=<?php echo $user['id']; ?>">
                <small>
                  view all <?php echo $user['artist']; ?>'s images
                </small>
              </a>
              <?php
                if (basename($_SERVER['PHP_SELF']) !== 'private_simplest_view.php' && basename($_SERVER['PHP_SELF']) !== 'private_simple_view.php') {
                  include('imguser.php');
                }
              ?>
              <div class="card shadow border-0 rounded-4 bg-body-tertiary mt-3">
                <div class="card-body">
                  <!-- Tags -->
                  <h6 class="card-subtitle mb-2 fw-bold"><i class="bi bi-tags-fill"></i> Tags</h6>
                  <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php
                    $tagCount = 0;
                    
                    if (!empty($image['tags'])) {
                      $tags = explode(',', $image['tags']);
                      foreach ($tags as $tag) {
                        $tag = trim($tag);
                        if (!empty($tag)) {
                          $query = "SELECT COUNT(*) FROM private_images WHERE tags LIKE :tag";
                          $tagParam = '%' . $tag . '%';
                          $stmt = $db->prepare($query);
                          $stmt->bindParam(':tag', $tagParam);
                          $stmt->execute();
                          
                          $tagCount = $stmt->fetchColumn();
                          ?>
                          <a href="tagged_images.php?tag=<?php echo urlencode($tag); ?>" class="badge bg-dark text-decoration-none rounded-4 py-2">
                            <i class="bi bi-tag-fill"></i> <?php echo $tag; ?> <span class="badge bg-light text-dark"><?php echo $tagCount; ?></span>
                          </a>
                          <?php
                        }
                      }
                    } else {
                      echo "<p class='text-muted'>No tags available.</p>";
                    }
                    ?>
                  </div>
            
                  <!-- Characters -->
                  <?php if (isset($image['characters']) && !empty($image['characters'])): ?>
                    <h6 class="card-subtitle mb-2 fw-bold"><i class="bi bi-people-fill"></i> Characters</h6>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                      <?php
                      if (!empty($image['characters'])) {
                        $characters = explode(',', $image['characters']);
                        foreach ($characters as $character) {
                          $character = trim($character);
                          if (!empty($character)) {
                            $query = "SELECT COUNT(*) FROM private_images WHERE characters LIKE :character";
                            $characterParam = '%' . $character . '%';
                            $stmt = $db->prepare($query);
                            $stmt->bindParam(':character', $characterParam);
                            $stmt->execute();
                            
                            $characterCount = $stmt->fetchColumn();
                            ?>
                            <a href="character/?character=<?php echo urlencode($character); ?>" class="badge bg-dark text-decoration-none rounded-4 py-2">
                              <i class="bi bi-person-fill"></i> <?php echo htmlspecialchars($character); ?> <span class="badge bg-light text-dark"><?php echo $characterCount; ?></span>
                            </a>
                            <?php
                          }
                        }
                      } else {
                        echo "<p class='text-muted'>No characters available.</p>";
                      }
                      ?>
                    </div>
                  <?php endif; ?>
            
                  <!-- Parodies -->
                  <?php if (isset($image['parodies']) && !empty($image['parodies'])): ?>
                    <h6 class="card-subtitle mb-2 fw-bold"><i class="bi bi-journals"></i> Parodies</h6>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                      <?php
                      if (!empty($image['parodies'])) {
                        $parodies = explode(',', $image['parodies']);
                        foreach ($parodies as $parody) {
                          $parody = trim($parody);
                          if (!empty($parody)) {
                            $query = "SELECT COUNT(*) FROM private_images WHERE parodies LIKE :parody";
                            $parodyParam = '%' . $parody . '%';
                            $stmt = $db->prepare($query);
                            $stmt->bindParam(':parody', $parodyParam);
                            $stmt->execute();
                            
                            $parodyCount = $stmt->fetchColumn();
                            ?>
                            <a href="parody/?parody=<?php echo urlencode($parody); ?>" class="badge bg-dark text-decoration-none rounded-4 py-2">
                              <i class="bi bi-journal"></i> <?php echo htmlspecialchars($parody); ?> <span class="badge bg-light text-dark"><?php echo $parodyCount; ?></span>
                            </a>
                            <?php
                          }
                        }
                      } else {
                        echo "<p class='text-muted'>No parodies available.</p>";
                      }
                      ?>
                    </div>
                  <?php endif; ?>
            
                  <!-- Group -->
                  <?php if (isset($image['group']) && !empty($image['group'])): ?>
                    <h6 class="card-subtitle mb-2 fw-bold"><i class="bi bi-person-fill"></i> group</h6>
                    <div class="d-flex flex-wrap gap-2">
                      <?php
                      if (!empty($image['group'])) {
                        $group = explode(',', $image['group']);
                        foreach ($group as $group) {
                          $group = trim($group);
                          if (!empty($group)) {
                            $query = "SELECT COUNT(*) FROM private_images WHERE `group` LIKE :group";
                            $groupParam = '%' . $group . '%';
                            $stmt = $db->prepare($query);
                            $stmt->bindParam(':group', $groupParam);
                            $stmt->execute();
                            
                            $groupCount = $stmt->fetchColumn();
                            ?>
                            <a href="group/?group=<?php echo urlencode($group); ?>" class="badge bg-dark text-decoration-none rounded-4 py-2">
                              <i class="bi bi-person-fill"></i> <?php echo htmlspecialchars($group); ?> <span class="badge bg-light text-dark"><?php echo $groupCount; ?></span>
                            </a>
                            <?php
                          }
                        }
                      } else {
                        echo "<p class='text-muted'>No group available.</p>";
                      }
                      ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div> 
        </div>
        <script>
          function copyToClipboard() {
            var urlInput = document.getElementById('urlInput');
            urlInput.select();
            urlInput.setSelectionRange(0, 99999); // For mobile devices
    
            document.execCommand('copy');
          }
    
          function copyToClipboard1() {
            var urlInput1 = document.getElementById('urlInput1');
            urlInput1.select();
            urlInput1.setSelectionRange(0, 99999); // For mobile devices
    
            document.execCommand('copy');
          }
    
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
              console.log('Web Share API not supported.');
            }
          }
    
          function shareArtist(userId) {
            // Compose the share URL
            var shareUrl = '/artist.php?by=newest&id=' + userId;
    
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
        <style>
          .shadowed-text {
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);
          }
    
          .text-stroke {
            -webkit-text-stroke: 1px;
          }
          
          .hide-scrollbar::-webkit-scrollbar {
            display: none;
          }
    
          .hide-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
          }
    
          .img-pointer {
            transition: opacity 0.3s ease-in-out;
          }
        
          .img-pointer:hover {
            opacity: 0.8;
            cursor: pointer;
          }
          
          .img-blur {
            filter: blur(2px);
          }
          
          .text-stroke-2 {
            -webkit-text-stroke: 3px;
          }
          
          .media-scrollerF {
            display: grid;
            gap: 3px; /* Updated gap value */
            grid-auto-flow: column;
            overflow-x: auto;
            overscroll-behavior-inline: contain;
          }
    
          .snaps-inlineF {
            scroll-snap-type: inline mandatory;
            scroll-padding-inline: var(--_spacer, 1rem);
          }
    
          .snaps-inlineF > * {
            scroll-snap-align: start;
          }
      
          .scroll-container {
            scrollbar-width: none;  /* Firefox */
            -ms-overflow-style: none;  /* Internet Explorer 10+ */
            margin-left: auto;
            margin-right: auto;
          }
          
          .w-98 {
            width: 98%;
          }
    
          .scroll-container::-webkit-scrollbar {
            width: 0;  /* Safari and Chrome */
            height: 0;
          }
          
          .scrollable-div::-webkit-scrollbar-track {
            border-radius: 0;
          }
          
          .scrollable-div::-webkit-scrollbar {
            width: 0;
            height: 0;
            border-radius: 10px;
          }
          
          .scrollable-div::-webkit-scrollbar-thumb {
            background-color: transparent;
          }
    
          .image-containerA {
            width: 33.33%;
            flex-grow: 1;
          }
      
          .text-sm {
            font-size: 13px;
          }
          
          .display-f {
            font-size: 33px;
          } 
    
          .roow {
            display: flex;
            flex-wrap: wrap;
          }
    
          .cool-6 {
            width: 50%;
            padding: 0 15px;
            box-sizing: border-box;
          }
    
          .caard {
            margin-bottom: 15px;
          }
          
          .rounded-r {
            border-radius: 15px;
          }
    
          .scrollable-title::-webkit-scrollbar {
            width: 0;
            height: 0;
          }
      
          @media (max-width: 767px) {
            .cool-6 {
              width: 100%;
              padding: 0;
            }
            
            .display-small-none {
              display: none;
            }
            
            .rounded-r {
              border-radius: 0;
            }
    
            .img-UF {
              width: 100%;
              height: 200px;
            }
          }
          
          @media (min-width: 768px) {
            .img-UF {
              width: 100%;
              height: 300px;
            }
          }
          
          .overlay {
            position: relative;
            display: flex;
            flex-direction: column; /* Change to column layout */
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Adjust background color and opacity */
            text-align: center;
            position: absolute;
            top: 0;
            left: 0;
          }
    
          .overlay i {
            font-size: 48px; /* Adjust icon size */
          }
    
          .overlay span {
            font-size: 18px; /* Adjust text size */
            margin-top: 8px; /* Add spacing between icon and text */
          }
        </style>
        <script>
          document.addEventListener('DOMContentLoaded', () => {
            // Disable all buttons by blocking pointer events and keyboard focus, without setting disabled attribute
            document.querySelectorAll('button, input[type="button"], input[type="submit"], input[type="reset"]').forEach(btn => {
              // Do NOT set disabled attribute to avoid default browser greying
              btn.style.pointerEvents = 'none'; // block clicks
              btn.setAttribute('tabindex', '-1'); // remove from tab order
              btn.setAttribute('aria-disabled', 'true'); // accessibility
            });
        
            // Disable all links (<a>)
            document.querySelectorAll('a').forEach(link => {
              link.style.pointerEvents = 'none'; // disable click
              link.setAttribute('tabindex', '-1'); // remove from tab order
              link.setAttribute('aria-disabled', 'true'); // accessibility
            });
        
            // Disable all other interactive elements (inputs, selects, textareas) by disabling them
            document.querySelectorAll('input, select, textarea').forEach(el => {
              el.disabled = true; // inputs usually have no styling issues when disabled
              el.style.pointerEvents = 'none';
              el.setAttribute('aria-disabled', 'true');
            });
        
            // Block all user interaction globally
            function stopAllInteractions(e) {
              e.preventDefault();
              e.stopPropagation();
              return false;
            }
        
            ['click', 'dblclick', 'mousedown', 'mouseup', 'keydown', 'keyup', 'keypress', 'touchstart', 'touchend'].forEach(evt => {
              document.addEventListener(evt, stopAllInteractions, true);
            });
          });
        </script>