        <div class="cool-6">
          <div class="bg-body-tertiary d-flex justify-content-center d-md-none d-lg-none">
            <?php if ($next_image): ?>
              <button class="img-pointer btn me-auto border-0" onclick="location.href='?artworkid=<?= $next_image['id'] ?>'">
                <i class="bi bi-chevron-left text-stroke-2"></i>
              </button>
            <?php else: ?>
              <button class="img-pointer btn me-auto border-0" onclick="location.href='../artist.php?id=<?php echo $user['id']; ?>'">
                <i class="bi bi-box-arrow-in-up-left text-stroke"></i>
              </button>
            <?php endif; ?>
            <h6 class="mx-auto img-pointer user-select-none text-center fw-bold scrollable-title mt-2" style="overflow-x: auto; white-space: nowrap; margin: 0 auto;">
              <?php echo $image['title']; ?>
            </h6>
            <?php if ($prev_image): ?>
              <button class="img-pointer btn ms-auto border-0" onclick="location.href='?artworkid=<?= $prev_image['id'] ?>'">
                <i class="bi bi-chevron-right text-stroke-2"></i>
              </button>
            <?php else: ?>
              <button class="img-pointer btn ms-auto border-0" onclick="location.href='../artist.php?id=<?php echo $user['id']; ?>'">
                <i class="bi bi-box-arrow-in-up-right text-stroke"></i>
              </button>
            <?php endif; ?>
          </div>
          <div class="caard position-relative">
            <a href="#" id="originalImageLink" data-bs-toggle="modal" data-bs-target="#originalImageModal" data-original-src="../images/<?php echo $image['filename']; ?>">
              <img class="img-pointer rounded-r h-100 w-100" src="../thumbnails/<?= $image['filename'] ?>" alt="<?php echo $image['title']; ?>">
            </a>
            <!-- Original Image Modal -->
            <div class="modal fade" id="originalImageModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered modal-fullscreen modal-dialog-scrollable"> <!-- Add the modal-dialog-scrollable class -->
                <div class="modal-content border-0 bg-dark">
                  <div class="modal-body scrollable-div d-flex align-items-center justify-content-center p-0">
                    <div class="position-relative h-100 w-100 align-items-center">
                      <div class="position-relative">
                        <img id="originalImage" class="img-fluid mb-1" src="" alt="Original Image" style="width: 100%; height: auto;">
                        <div class="card-img-overlay position-absolute bottom-0 start-0 m-3">
                          <h6 class="card-title text-white fw-bold shadowed-text">
                            <?php echo $image['title']; ?>          
                          </h6>
                        </div>
                        <div class="position-absolute bottom-0 start-0 m-3">
                          <h6 class="card-title text-white fw-bold shadowed-text">by           
                            <a class="text-decoration-none text-white shadowed-text fw-bold rounded-pill" href="../artist.php?id=<?= $user['id'] ?>">
                              <?php if (!empty($user['pic'])): ?>
                                <img class="object-fit-cover border border-1 rounded-circle" src="<?php echo $user['pic']; ?>" style="width: 24px; height: 24px;">
                              <?php else: ?>
                                <img class="object-fit-cover border bg-secondary border-1 rounded-circle" src="icon/profile.svg" style="width: 24px; height: 24px;">
                              <?php endif; ?>
                              <?php echo (mb_strlen($user['artist']) > 25) ? mb_substr($user['artist'], 0, 25) . '...' : $user['artist']; ?>
                            </a> 
                          </h6>
                        </div>
                      </div>
                      <div class="image-container position-relative">
                        <div class="btn-group position-absolute bottom-0 end-0 m-3">
                          <a href="../images/<?php echo $image['filename']; ?>" class="btn btn-sm btn-dark rounded-3 rounded-end-0 opacity-75 fw-bold" download>
                            <i class="bi bi-cloud-arrow-down-fill"></i> <small>download</small>
                          </a>
                          <a href="../images/<?php echo $image['filename']; ?>" class="btn btn-sm btn-dark rounded-3 rounded-start-0 opacity-75 fw-bold">
                            <i class="bi bi-arrows-fullscreen text-stroke"></i>
                          </a>
                        </div>
                      </div>
                      <?php foreach ($child_images as $child_image) : ?>
                        <div class="image-container position-relative">
                          <img data-src="../images/<?php echo $child_image['filename']; ?>" class="mb-1 lazy-load" style="height: 100%; width: 100%;" alt="<?php echo $image['title']; ?>">
                          <div class="card-img-overlay position-absolute bottom-0 start-0 m-3">
                            <h6 class="card-title text-white fw-bold shadowed-text">
                              <?php echo $image['title']; ?>          
                            </h6>
                          </div>
                          <button type="button" class="btn position-absolute border-0 top-0 end-0 m-2" data-bs-dismiss="modal">
                            <i class="bi bi-chevron-down text-white shadowed-text text-stroke fs-5"></i>
                          </button>
                          <div class="position-absolute bottom-0 start-0 m-3">
                            <h6 class="card-title text-white fw-bold shadowed-text">by           
                              <a class="text-decoration-none text-white shadowed-text fw-bold rounded-pill" href="../artist.php?id=<?= $user['id'] ?>">
                                <?php if (!empty($user['pic'])): ?>
                                  <img class="object-fit-cover border border-1 rounded-circle" src="<?php echo $user['pic']; ?>" style="width: 24px; height: 24px;">
                                <?php else: ?>
                                  <img class="object-fit-cover border bg-secondary border-1 rounded-circle" src="icon/profile.svg" style="width: 24px; height: 24px;">
                                <?php endif; ?>
                                <?php echo (mb_strlen($user['artist']) > 25) ? mb_substr($user['artist'], 0, 25) . '...' : $user['artist']; ?>
                              </a> 
                            </h6>
                          </div>
                          <div class="btn-group position-absolute bottom-0 end-0 m-3">
                            <a href="../images/<?php echo $child_image['filename']; ?>" class="btn btn-sm btn-dark rounded-3 rounded-end-0 opacity-75 fw-bold" download>
                              <i class="bi bi-cloud-arrow-down-fill"></i> <small>download</small>
                            </a>
                            <a href="../images/<?php echo $child_image['filename']; ?>" class="btn btn-sm btn-dark rounded-3 rounded-start-0 opacity-75 fw-bold">
                              <i class="bi bi-arrows-fullscreen text-stroke"></i>
                            </a>
                          </div>
                        </div>
                      <?php endforeach; ?>
                      <button type="button" class="btn position-absolute border-0 top-0 end-0 m-2" data-bs-dismiss="modal">
                        <i class="bi bi-chevron-down text-white shadowed-text text-stroke fs-5"></i>
                      </button>
                      <?php
                        // Get all images for the given user_email
                        $stmt = $db->prepare("SELECT id, filename, tags, title FROM images WHERE email = :email ORDER BY id DESC");
                        $stmt->bindParam(':email', $user_email);
                        $stmt->execute();
                        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
                      ?>

                      <div class="mt-2 mb-2 w-98 scroll-container media-scrollerF snaps-inlineF overflow-auto">
                        <?php $count = 0; ?>
                        <?php foreach ($images as $imageU): ?>
                          <?php
                            $image_idF = $imageU['id'];
                            $image_urlF = $imageU['filename'];
                            $image_titleF = $imageU['title'];
                            $current_image_idF = isset($_GET['artworkid']) ? $_GET['artworkid'] : null;
                            ?>
                            <div class="media-elementF d-inline-flex">
                              <a href="?artworkid=<?php echo $image_idF; ?>">
                                <img class="hori <?php echo ($image_idF == $current_image_idF) ? 'opacity-50' : ''; ?>" src="../thumbnails/<?php echo $image_urlF; ?>" alt="<?php echo $image_titleF; ?>">
                              </a>
                            </div>
                          <?php $count++; ?>
                          <?php if ($count >= 25) break; ?>
                        <?php endforeach; ?>
                        <button id="loadMoreBtnF" class="btn btn-secondary hori opacity-25"><i class="bi bi-plus-circle display-5 text-stroke"></i></button>
                        <script>
                          var currentIndexF = <?php echo $count; ?>;
                          var imagesF = <?php echo json_encode($images); ?>;
                          var containerF = document.querySelector('.media-scrollerF');
                          var loadMoreBtnF = document.getElementById('loadMoreBtnF');

                          function loadMoreImagesF() {
                            for (var i = currentIndexF; i < currentIndexF + 25 && i < imagesF.length; i++) {
                              var imageUF = imagesF[i];
                              var image_idF = imageUF['id'];
                              var image_urlF = imageUF['filename'];
                              var image_titleF = imageUF['title'];
                              var current_image_idF = '<?php echo $current_image_idF; ?>';

                              var mediaElementF = document.createElement('div');
                              mediaElementF.classList.add('media-elementF');
                              mediaElementF.classList.add('d-inline-flex');

                              var linkF = document.createElement('a');
                              linkF.href = '?artworkid=' + image_idF;

                              var imageF = document.createElement('img');
                              imageF.classList.add('hori');
                              if (image_idF == current_image_idF) {
                                imageF.classList.add('opacity-50');
                              }
                              imageF.src = '../thumbnails/' + image_urlF;
                              imageF.alt = image_titleF;

                              linkF.appendChild(imageF);
                              mediaElementF.appendChild(linkF);
                              containerF.insertBefore(mediaElementF, loadMoreBtnF);
                            }

                            currentIndexF += 25;
                            if (currentIndexF >= imagesF.length) {
                              loadMoreBtnF.style.display = 'none';
                            }
                          }

                          loadMoreBtnF.addEventListener('click', loadMoreImagesF);
                        </script>
                      </div>
                      <div class="roow mb-5">
                        <div class="cool-6 d-flex mb-3 justify-content-center">
                          <?php
                            // Get all images for the given user_email
                            $stmt = $db->prepare("SELECT * FROM images WHERE email = :email ORDER BY id DESC");
                            $stmt->bindParam(':email', $user_email);
                            $stmt->execute();
                            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
                          ?>
                          <div id="image-carouselF" class="carousel slide carousel-fade mt-2 w-98">
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
                                  <a href="?artworkid=<?php echo $image_id; ?>">
                                    <img class="lazy-load d-block w-100 rounded object-fit-cover img-UF" style="object-position: top;" data-src="../thumbnails/<?php echo $image_url; ?>" alt="<?php echo $image_title; ?>">
                                    <div class="carousel-caption">
                                      <h5 class="fw-bold shadowed-text"><?php echo $image_title; ?></h5>
                                      <p class="fw-bold shadowed-text"><small>by <?php echo $user['artist']; ?></small></p>
                                    </div>
                                  </a>
                                </div>
                              <?php endforeach; ?>
                            </div>
                            <div class="btn-group w-100 mt-3">
                              <button class="btn btn-outline-light" type="button" data-bs-target="#image-carouselF" data-bs-slide="prev">
                                <i class="bi bi-arrow-left-circle-fill"></i>
                                <span class="visually-hidden">Previous</span>
                              </button>
                              <button class="btn btn-outline-light" type="button" data-bs-target="#image-carouselF" data-bs-slide="next">
                                <i class="bi bi-arrow-right-circle-fill"></i>
                                <span class="visually-hidden">Next</span>
                              </button>
                            </div>
                          </div>
                        </div>
                        <div class="cool-6 mt-2">
                          <div class="d-flex justify-content-center">
                            <div class="w-98 fw-bold">
                              <div class="btn-group mb-3 w-100">
                                <?php
                                  $image_id = $image['id'];
                                  $stmt = $db->query("SELECT COUNT(*) FROM favorites WHERE image_id = $image_id");
                                  $fav_count = $stmt->fetchColumn();
                                  if ($fav_count >= 1000000000) {
                                    $fav_count = round($fav_count / 1000000000, 1) . 'b';
                                  } elseif ($fav_count >= 1000000) {
                                    $fav_count = round($fav_count / 1000000, 1) . 'm';
                                  } elseif ($fav_count >= 1000) {
                                    $fav_count = round($fav_count / 1000, 1) . 'k';
                                  }
                                  $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE email = :email AND image_id = :image_id");
                                  $stmt->bindParam(':email', $email);
                                  $stmt->bindParam(':image_id', $image_id);
                                  $stmt->execute();
                                  $is_favorited = $stmt->fetchColumn();
                                  if ($is_favorited) {
                                ?>
                                  <form action="?artworkid=<?php echo $image['id']; ?>" method="POST">
                                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-light rounded-2 rounded-end-0 fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i> <small>unfavorite</small></button>
                                  </form>
                                <?php } else { ?>
                                  <form action="?artworkid=<?php echo $image['id']; ?>" method="POST">
                                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-light rounded-2 rounded-end-0 fw-bold" name="favorite"><i class="bi bi-heart"></i> <small>favorite</small></button>
                                  </form>
                                <?php } ?>
                                <button class="btn btn-sm btn-outline-light rounded-0 rounded-start-0 fw-bold" onclick="sharePage()">
                                  <i class="bi bi-share-fill"></i> <small>share</small>
                                </button>
                                <a class="btn btn-sm btn-outline-light rounded-0 rounded-end-0 fw-bold" href="../images/<?php echo $image['filename']; ?>">
                                  <i class="bi bi-eye-fill"></i> <small>full res</small>
                                </a>
                                <a class="btn btn-sm btn-outline-light fw-bold rounded-2 rounded-start-0" href="../download_images.php?artworkid=<?= $image_id; ?>">
                                  <i class="bi bi-cloud-arrow-down-fill"></i> <small>download all</small>
                                </a>
                              </div>
                              <div class="container-fluid mb-4 text-white text-center align-items-center d-flex justify-content-center">
                                <button class="btn border-0 disabled fw-semibold">
                                  <small>
                                    <?php echo date('Y/m/d', strtotime($image['date'])); ?>
                                  </small
                                </button>
                                <button class="btn border-0 disabled fw-semibold"><i class="bi bi-heart-fill text-sm"></i> <small><?php echo $fav_count; ?> </small></button>
                                <button class="btn border-0 disabled fw-semibold"><i class="bi bi-eye-fill"></i> <small><?php echo $viewCount; ?> </small></button>
                              </div>
                              <h5 class="text-white text-center shadowed-text fw-bold"><?php echo $image['title']; ?></h5>
                              <div style="word-break: break-word;">
                                <p class="text-white shadowed-text" style="word-break: break-word;">
                                  <small>
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
                                  </small>
                                </p>
                              </div>
                              <div class="mb-3">
                                <?php
                                  if (!empty($image['tags'])) {
                                    $tags = explode(',', $image['tags']);
                                    foreach ($tags as $tag) {
                                      $tag = trim($tag);
                                        if (!empty($tag)) {
                                      ?>
                                        <a href="tagged_images.php?tag=<?php echo urlencode($tag); ?>"
                                          class="btn btn-sm btn-outline-light mb-1 rounded-3 fw-bold">
                                          <i class="bi bi-tags-fill"></i> <?php echo $tag; ?>
                                        </a>
                                      <?php
                                      }
                                    }
                                  } else {
                                    echo "No tags available.";
                                  }
                                ?>
                                <a class="btn btn-sm btn-outline-light mb-1 rounded-3 fw-bold" href="tags.php">
                                  <i class="bi bi-tags-fill"></i> all tags
                                </a>
                              </div>
                              <div class="mb-5">
                                <button class="btn btn-outline-light fw-bold w-100" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDataImage" aria-expanded="false" id="toggleButton2" aria-controls="collapseExample">
                                  <i class="bi bi-caret-down-fill"></i> <small>more</small>
                                </button> 
                                <div class="collapse" id="collapseDataImage">
                                  <?php
                                    // Function to calculate the size of an image in MB
                                    function getImageSizeInMB($filename) {
                                      return round(filesize('../images/' . $filename) / (1024 * 1024), 2);
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
                                  
                                    // Function to format the date
                                    function formatDate($date) {
                                      return date('Y/F/l jS') ;
                                    }
                                  ?>
                                  <?php foreach ($images as $index => $image) { ?>
                                    <div class="text-white mt-3 mb-3 rounded border border-1 border-light">
                                      <ul class="list-unstyled m-1">
                                        <li class="mb-2"><i class="bi bi-file-earmark"></i> Filename: <?php echo $image['filename']; ?></li>
                                        <li class="mb-2"><i class="bi bi-file-earmark-bar-graph"></i> Image data size: <?php echo getImageSizeInMB($image['filename']); ?> MB</li>
                                        <li class="mb-2"><i class="bi bi-arrows-angle-expand text-stroke"></i> Image dimensions: <?php list($width, $height) = getimagesize('../images/' . $image['filename']); echo $width . 'x' . $height; ?></li>
                                        <li class="mb-2"><i class="bi bi-file-earmark-text"></i> MIME type: <?php echo mime_content_type('../images/' . $image['filename']); ?></li>
                                        <li class="mb-2"><i class="bi bi-calendar"></i> Image date: <?php echo date('Y/m/d', strtotime($image['date'])); ?></li>
                                        <li class="mb-2">
                                          <a class="text-decoration-none text-primary" href="../images/<?php echo $image['filename']; ?>">
                                            <i class="bi bi-arrows-fullscreen text-stroke"></i> View original image
                                          </a>
                                        </li>
                                        <li>
                                          <a class="text-decoration-none text-primary" href="../images/<?php echo $image['filename']; ?>" download>
                                            <i class="bi bi-cloud-arrow-down-fill"></i> Download original image
                                          </a>
                                        </li>
                                      </ul>
                                    </div>
                                  <?php } ?>
                                  <?php foreach ($image_childs as $index => $image_child) { ?>
                                    <div class="text-white mt-3 mb-3 rounded border border-1 border-light">
                                      <ul class="list-unstyled m-1">
                                        <li class="mb-2"><i class="bi bi-file-earmark"></i> Filename: <?php echo $image_child['filename']; ?></li>
                                        <li class="mb-2"><i class="bi bi-file-earmark-bar-graph"></i> Image data size: <?php echo getImageSizeInMB($image_child['filename']); ?> MB</li>
                                        <li class="mb-2"><i class="bi bi-arrows-angle-expand text-stroke"></i> Image dimensions: <?php list($width, $height) = getimagesize('../images/' . $image_child['filename']); echo $width . 'x' . $height; ?></li>
                                        <li class="mb-2"><i class="bi bi-file-earmark-text"></i> MIME type: <?php echo mime_content_type('../images/' . $image_child['filename']); ?></li>
                                        <li class="mb-2"><i class="bi bi-calendar"></i> Image date: <?php echo date('Y/m/d', strtotime($image['date'])); ?></li>
                                        <li class="mb-2">
                                          <a class="text-decoration-none text-primary" href="../images/<?php echo $image_child['filename']; ?>">
                                            <i class="bi bi-arrows-fullscreen text-stroke"></i> View original image
                                          </a>
                                        </li>
                                        <li>
                                          <a class="text-decoration-none text-primary" href="../images/<?php echo $image_child['filename']; ?>" download>
                                            <i class="bi bi-cloud-arrow-down-fill"></i> Download original image
                                          </a>
                                        </li>
                                      </ul>
                                    </div>
                                  <?php } ?>
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
                                  <div class="text-white mt-3 mb-3">
                                    <ul class="list-unstyled m-0">
                                      <li class="mb-2"><i class="bi bi-file-earmark-plus"></i> Total size of all images: <?php echo $total_size; ?> MB</li>
                                    </ul>
                                  </div>
                                  <a class="btn btn-outline-light fw-bold w-100 mb-2" href="#downloadOption" data-bs-toggle="modal">
                                    <i class="bi bi-cloud-arrow-down-fill"></i> download all
                                  </a>
                                  <button type="button" class="btn btn-outline-light w-100 fw-bold" data-bs-dismiss="modal">
                                    close
                                  </button>
                                </div>
                              </div>
                            </div> 
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="position-absolute top-0 end-0 me-2 mt-2">
              <div class="btn-group">
                <?php if ($user_email === $email): ?>
                  <!-- Display the edit button only if the current user is the owner of the image -->
                  <a class="btn btn-sm btn-dark fw-bold opacity-75 rounded-3 rounded-end-0" href="../edit/?id=<?php echo $image['id']; ?>">
                    <i class="bi bi-pencil-fill"></i> Edit Image
                  </a>
                <?php endif; ?>
                <div class="dropdown">
                  <button class="btn btn-sm btn-dark fw-bold opacity-75 <?php echo ($user_email === $email) ? 'rounded-start-0 rounded-3' : 'rounded-3'; ?> text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-images"></i> <?php echo $total_all_images; ?>
                  </button>
                  <ul class="dropdown-menu">
                    <li><small><a class="dropdown-item fw-bold" href="#">
                      <?php 
                        if ($total_all_images == 1) {
                          echo "Total Image: 1 image";
                        } else {
                          echo "Total Images: " . $total_all_images . " images";
                        }
                      ?>
                    </a></small></li>
                    <li><small><a class="dropdown-item fw-bold" href="#">Total Size: <?php echo $total_size; ?> MB</a></small></li>
                    <li><small><a class="dropdown-item fw-bold" href="#"><?php echo $viewCount; ?> views</a></small></li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="position-absolute bottom-0 end-0 me-2 mb-2">
              <div class="btn-group">
                <div class="dropdown">
                  <button class="btn btn-sm btn-dark opacity-75 rounded-3 rounded-end-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-eye-fill"></i>
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item fw-bold" href="#" data-bs-toggle="modal" data-bs-target="#originalImageModal">
                        <i class="bi bi-images"></i> full modal view
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item fw-bold" href="../view/gallery/?artworkid=<?php echo $image['id']; ?>">
                        <i class="bi bi-distribute-vertical"></i> full gallery view
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item fw-bold" href="../view/carousel/?artworkid=<?php echo $image['id']; ?>">
                        <i class="bi bi-distribute-horizontal"></i> full carousel view
                      </a>
                    </li>
                  </ul>
                </div>
                <button class="btn btn-sm btn-dark fw-bold opacity-75 rounded-0 text-white" id="loadOriginalBtn">Load Original Image</button>
                <a class="btn btn-sm btn-dark fw-bold opacity-75 rounded-3 rounded-start-0 text-white" data-bs-toggle="modal" data-bs-target="#downloadOption">
                  <i class="bi bi-cloud-arrow-down-fill"></i>
                </a>
              </div>
              <!-- Download Option Modal -->
              <div class="modal fade" id="downloadOption" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h1 class="modal-title fw-bold fs-5" id="exampleModalToggleLabel">Download Option</h1>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body scrollable-div">
                      <a class="btn btn-outline-dark fw-bold w-100 mb-2 text-center rounded-3" href="../images/<?php echo $image['filename']; ?>" download>
                        <i class="bi bi-cloud-arrow-down-fill"></i> Download first image (<?php echo getImageSizeInMB($image['filename']); ?> MB)
                      </a>
                      <?php if ($total_size > 10): ?>
                        <a class="btn btn-outline-dark fw-bold w-100 mb-2 text-center rounded-3" href="#" data-bs-target="#rusModal" data-bs-toggle="modal">
                          <p><i class="bi bi-file-earmark-zip-fill"></i> Download all images (<?php echo $total_size; ?> MB)</p>
                          <p><small>This file is too big. The total size is <?php echo $total_size; ?> MB.</small></p>
                        </a>
                      <?php else: ?>
                        <a class="btn btn-outline-dark fw-bold w-100 mb-2 text-center rounded-3" href="#" id="downloadAllImages">
                          <i class="bi bi-file-earmark-zip-fill"></i> Download all images (<?php echo $total_size; ?> MB)
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
                        <small>2. If you found download error or failed, <a class="text-decoration-none" href="../download_images.php?artworkid=<?= $image_id; ?>">click this link</a> for third option if download all images error or failed.</small>
                      </p>
                      <p class="fw-bold text-center container">
                        <small>3. If you found problem where the zip contain empty file or 0b, download the images manually.</small>
                      </p>
                      <p class="fw-bold text-center container">
                        <small>4. Server sometimes have problem with file and folder path, download manually is the best option if this happening.</small>
                      </p>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-dark fw-bold w-100 text-center rounded-3" data-bs-dismiss="modal">cancel</button>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal fade" id="rusModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h1 class="modal-title fw-bold fs-5" id="exampleModalToggleLabel2">Are You Sure?</h1>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body scrollable-div">
                      <a class="btn btn-outline-dark fw-bold w-100 mb-2 text-center rounded-3" href="#" id="downloadAllImages">
                        <i class="bi bi-file-earmark-zip-fill"></i> Download all images (<?php echo $total_size; ?> MB)
                      </a>
                      <button type="button" class="btn btn-outline-dark mb-2 fw-bold w-100 text-center rounded-3" data-bs-target="#downloadOption" data-bs-toggle="modal"><i class="bi bi-arrow-left-circle-fill"></i> back to previous</button>
                      <div class="progress fw-bold" style="height: 30px; display: none;">
                        <div class="progress-bar progress-bar progress-bar-animated fw-bold" style="width: 0; height: 30px;" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="progress-bar2">0%</div>
                      </div>
                      <h5 class="fw-bold text-center mt-2">Please Note!</h5>
                      <p class="fw-bold text-center container">
                        <small>1. Download can take a really long time, wait until progress bar reach 100% or appear download pop up in the notification.</small>
                      </p>
                      <p class="fw-bold text-center container">
                        <small>2. If you found download error or failed, <a class="text-decoration-none" href="../download_images.php?artworkid=<?= $image_id; ?>">click this link</a> for third option if download all images error or failed.</small>
                      </p>
                      <p class="fw-bold text-center container">
                        <small>3. If you found problem where the zip contain empty file or 0b, download the images manually.</small>
                      </p>
                      <p class="fw-bold text-center container">
                        <small>4. Server sometimes have problem with file and folder path, download manually is the best option if this happening.</small>
                      </p>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-dark fw-bold w-100 text-center rounded-3" data-bs-dismiss="modal">cancel</button>
                    </div>
                  </div>
                </div>
              </div>
              <script>
                document.addEventListener('DOMContentLoaded', function() {
                  var progressBar1 = document.getElementById('progress-bar1');
                  var progressBarContainer1 = progressBar1.parentElement;

                  var progressBar2 = document.getElementById('progress-bar2');
                  var progressBarContainer2 = progressBar2.parentElement;

                  var downloadAllImagesButton = document.getElementById('downloadAllImages');
                  var downloadInProgress = false; // Variable to track download status

                  downloadAllImagesButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    // If download is already in progress, do nothing
                    if (downloadInProgress) {
                      return;
                    }

                    // Disable the download button to prevent double-clicking
                    downloadAllImagesButton.disabled = true;
                    downloadInProgress = true;

                    // Show both progress bars when the download starts
                    progressBarContainer1.style.display = 'block';
                    progressBarContainer2.style.display = 'block';

                    var artworkId = <?= $image_id; ?>; // Get the artwork ID from PHP variable

                    function downloadImages(imageId, progressBar, progressBarContainer) {
                      var xhr = new XMLHttpRequest();
                      xhr.open('GET', '../download_images.php?artworkid=' + imageId);
                      xhr.responseType = 'arraybuffer'; // Use arraybuffer responseType instead of blob

                      xhr.addEventListener('loadstart', function() {
                        progressBar.style.width = '0%';
                        progressBar.textContent = '0%';
                      });

                      xhr.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                          var percent = Math.round((e.loaded / e.total) * 100);
                          progressBar.style.width = percent + '%';
                          progressBar.textContent = percent + '%';

                          // Show "success" alert and replace progress bar when progress bar reaches 100%
                          if (percent === 100) {
                            showSuccessAlert(progressBarContainer);
                          }
                        }
                      });

                      xhr.onreadystatechange = function() {
                        if (xhr.readyState === XMLHttpRequest.DONE) {
                          progressBarContainer.style.display = 'none';

                          if (xhr.status === 200) {
                            // Handle successful download
                            var filename = getFilenameFromResponse(xhr); // Get filename from the response
                            var url = URL.createObjectURL(new Blob([xhr.response], { type: xhr.getResponseHeader('Content-Type') }));

                            // Create a temporary anchor element to trigger the download
                            var a = document.createElement('a');
                            a.style.display = 'none';
                            a.href = url;
                            a.download = filename;
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            window.URL.revokeObjectURL(url);
                          } else {
                            // Handle download error
                            alert('Download failed. Please try again.');
                          }

                          // Enable the download button again after the download is finished
                          downloadAllImagesButton.disabled = false;
                          downloadInProgress = false;
                        }
                      };

                      xhr.send();
                    }

                    // Assuming you have an array of image IDs from the server
                    var imageIds = [artworkId];
                    downloadImages(artworkId, progressBar1, progressBarContainer1);
                    downloadImages(artworkId, progressBar2, progressBarContainer2);
                  });

                  // Clear progress bars when the modal is closed
                  var downloadOptionModal = document.getElementById('downloadOption');
                  downloadOptionModal.addEventListener('hidden.bs.modal', function() {
                    progressBar1.style.width = '0%';
                    progressBar1.textContent = '0%';
                    progressBarContainer1.style.display = 'none';

                    progressBar2.style.width = '0%';
                    progressBar2.textContent = '0%';
                    progressBarContainer2.style.display = 'none';

                    // Enable the download button again when the modal is closed
                    downloadAllImagesButton.disabled = false;
                    downloadInProgress = false;
                  });

                  // Function to show the "success" alert and replace progress bar
                  function showSuccessAlert(progressBarContainer) {
                    var successAlert = document.createElement('div');
                    successAlert.classList.add('alert', 'alert-success', 'mt-3');
                    successAlert.textContent = 'Download complete!';

                    // Replace progress bar with success alert
                    progressBarContainer.style.display = 'none';
                    progressBarContainer.insertAdjacentElement('afterend', successAlert);
                  }

                  // Function to extract filename from the response headers
                  function getFilenameFromResponse(xhr) {
                    var contentDisposition = xhr.getResponseHeader('Content-Disposition');
                    var filename = '';

                    if (contentDisposition && contentDisposition.indexOf('filename=') !== -1) {
                      var match = contentDisposition.match(/filename=([^;]+)/);
                      filename = match ? match[1] : '';
                    }

                    // Convert filename to UTF-8 encoding
                    filename = decodeURIComponent(escape(filename));
                    return filename;
                  }
                });
              </script>
            </div>
            <?php if ($next_image): ?>
              <button class="btn btn-sm opacity-75 rounded fw-bold position-absolute start-0 top-50 translate-middle-y rounded-start-0"  onclick="location.href='?artworkid=<?= $next_image['id'] ?>'">
                <i class="bi bi-chevron-left display-f" style="-webkit-text-stroke: 4px;"></i>
              </button>
            <?php endif; ?> 
            <?php if ($prev_image): ?>
              <button class="btn btn-sm opacity-75 rounded fw-bold position-absolute end-0 top-50 translate-middle-y rounded-end-0"  onclick="location.href='?artworkid=<?= $prev_image['id'] ?>'">
                <i class="bi bi-chevron-right display-f" style="-webkit-text-stroke: 4px;"></i>
              </button>
            <?php endif; ?> 
            <button id="showProgressBtn" class="fw-bold btn btn-sm btn-dark position-absolute top-50 start-50 translate-middle text-nowrap rounded-pill opacity-75" style="display: none;">
              progress
            </button>
            <div class="position-absolute bottom-0 start-0 ms-2 mb-2">
              <div class="btn-group">
                <?php
                  $image_id = $image['id'];
                  $stmt = $db->query("SELECT COUNT(*) FROM favorites WHERE image_id = $image_id");
                  $fav_count = $stmt->fetchColumn();
                  if ($fav_count >= 1000000000) {
                    $fav_count = round($fav_count / 1000000000, 1) . 'b';
                  } elseif ($fav_count >= 1000000) {
                    $fav_count = round($fav_count / 1000000, 1) . 'm';
                  } elseif ($fav_count >= 1000) {
                    $fav_count = round($fav_count / 1000, 1) . 'k';
                  }
                  $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE email = :email AND image_id = :image_id");
                  $stmt->bindParam(':email', $email);
                  $stmt->bindParam(':image_id', $image_id);
                  $stmt->execute();
                  $is_favorited = $stmt->fetchColumn();
                  if ($is_favorited) {
                ?>
                  <form action="?artworkid=<?php echo $image['id']; ?>" method="POST">
                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-dark opacity-75 rounded-3 rounded-end-0" name="unfavorite"><i class="bi bi-heart-fill"></i></button>
                  </form>
                <?php } else { ?>
                  <form action="?artworkid=<?php echo $image['id']; ?>" method="POST">
                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-dark opacity-75 rounded-3 rounded-end-0" name="favorite"><i class="bi bi-heart"></i></button>
                  </form>
                <?php } ?>
                <button class="btn btn-sm btn-dark opacity-75 rounded-0" data-bs-toggle="modal" data-bs-target="#shareLink">
                  <i class="bi bi-share-fill"></i>
                </button>
                <button class="btn btn-sm btn-dark opacity-75 rounded-3 rounded-start-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCompression1" aria-expanded="false" aria-controls="collapseExample1" id="toggleButton1">
                  <i class="bi bi-caret-down-fill"></i>
                </button>
              </div>
            </div>
          </div>
          <div class="d-none d-md-block d-lg-block">
            <div class="collapse" id="collapseCompression1">
              <div class="alert alert-warning fw-bold rounded-4">
                <small><p>first original image have been compressed to <?php echo round($reduction_percentage, 2); ?>%</p> (<a class="text-decoration-none" href="../images/<?php echo $image['filename']; ?>">click to view original image</a>)</small>
              </div>
            </div>
          </div>
        </div>