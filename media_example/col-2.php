        <div class="cool-6">
          <div class="container d-md-none d-lg-none">
            <div class="collapse" id="collapseCompression1">
              <div class="alert alert-warning fw-bold rounded-3">
                <small>first original image have been compressed to <?php echo round($reduction_percentage, 2); ?>% (<a class="text-decoration-none" href="../images/<?php echo $image['filename']; ?>">click to view original image</a>)</small>
              </div>
            </div>
          </div>
          <div class="caard border-md-lg">
            <div class="container-fluid mb-4 d-none d-md-flex d-lg-flex">
              <?php
                $stmt = $db->prepare("SELECT u.id, u.email, u.password, u.region, u.artist, u.pic, u.desc, u.bgpic, i.id AS image_id, i.filename, i.tags FROM users u INNER JOIN images i ON u.id = i.id WHERE u.id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
              ?>
              <div class="d-flex">
                <a class="text-decoration-none text-dark fw-bold rounded-pill" href="#" data-bs-toggle="modal" data-bs-target="#userModal">
                 <?php if (!empty($user['pic'])): ?>
                   <img class="object-fit-cover border border-1 rounded-circle" src="../<?php echo $user['pic']; ?>" style="width: 32px; height: 32px;">
                  <?php else: ?>
                    <img class="object-fit-cover border border-1 rounded-circle" src="../icon/profile.svg" style="width: 32px; height: 32px;">
                  <?php endif; ?>
                  <?php echo (mb_strlen($user['artist']) > 20) ? mb_substr($user['artist'], 0, 20) . '...' : $user['artist']; ?> <small class="badge rounded-pill bg-primary"><i class="bi bi-globe-asia-australia"></i> <?php echo $user['region']; ?></small>
                </a>
              </div>
              <div class="ms-auto">
                <form method="post">
                  <?php if ($is_following): ?>
                    <button class="btn btn-sm btn-outline-dark rounded-pill fw-bold opacity-75" type="submit" name="unfollow"><i class="bi bi-person-dash-fill"></i> unfollow</button>
                  <?php else: ?>
                    <button class="btn btn-sm btn-outline-dark rounded-pill fw-bold opacity-75" type="submit" name="follow"><i class="bi bi-person-fill-add"></i> follow</button>
                  <?php endif; ?>
                </form>
              </div>
            </div>
            <div class="me-2 ms-2 rounded fw-bold">
              <div class="d-flex d-md-none d-lg-none gap-2">
                <?php if ($next_image): ?>
                  <a class="image-containerA shadow rounded" href="?artworkid=<?= $next_image['id'] ?>">
                    <div class="position-relative">
                      <img class="img-blur object-fit-cover rounded opacity-75" style="width: 100%; height: 120px;" src="../thumbnails/<?php echo $next_image['filename']; ?>" alt="<?php echo $next_image['title']; ?>">
                      <h6 class="fw-bold shadowed-text text-white position-absolute top-50 start-50 translate-middle">
                        <i class="bi bi-arrow-left-circle text-stroke"></i> Next
                      </h6>
                    </div>
                  </a>
                <?php else: ?>
                  <a class="image-containerA shadow rounded" href="../artist.php?id=<?php echo $user['id']; ?>">
                    <div class="position-relative">
                      <?php if (!empty($user['pic'])): ?>
                        <img class="img-blur object-fit-cover rounded opacity-75" style="width: 100%; height: 120px;" alt="<?php echo $user['artist']; ?>" src="../<?php echo $user['pic']; ?>">
                      <?php else: ?>
                        <img class="img-blur object-fit-cover rounded opacity-75" style="width: 100%; height: 120px;" alt="<?php echo $user['artist']; ?>" src="../icon/profile.svg">
                      <?php endif; ?>
                      <h6 class="fw-bold shadowed-text text-white position-absolute top-50 start-50 translate-middle">
                        <i class="bi bi-box-arrow-in-up-left text-stroke"></i> All
                      </h6>
                    </div> 
                  </a>
                <?php endif; ?>
                <a class="image-containerA shadow rounded" href="?artworkid=<?= $image['id'] ?>">
                  <img class="object-fit-cover opacity-50 rounded" style="width: 100%; height: 120px;" src="../thumbnails/<?= $image['filename'] ?>" alt="<?php echo $image['title']; ?>">
                </a>
                <?php if ($prev_image): ?>
                  <a class="image-containerA shadow rounded" href="?artworkid=<?= $prev_image['id'] ?>">
                    <div class="position-relative">
                      <img class="img-blur object-fit-cover rounded opacity-75" style="width: 100%; height: 120px;" src="../thumbnails/<?php echo $prev_image['filename']; ?>" alt="<?php echo $prev_image['title']; ?>">
                      <h6 class="fw-bold shadowed-text text-white position-absolute top-50 start-50 translate-middle">
                        Prev <i class="bi bi-arrow-right-circle text-stroke"></i>
                      </h6>
                    </div>
                  </a>
                <?php else: ?>
                  <a class="image-containerA shadow rounded" href="../artist.php?id=<?php echo $user['id']; ?>">
                    <div class="position-relative">
                      <?php if (!empty($user['pic'])): ?>
                        <img class="img-blur object-fit-cover rounded opacity-75" style="width: 100%; height: 120px;" alt="<?php echo $user['artist']; ?>" src="../<?php echo $user['pic']; ?>">
                      <?php else: ?>
                        <img class="img-blur object-fit-cover rounded opacity-75" style="width: 100%; height: 120px;" alt="<?php echo $user['artist']; ?>" src="../icon/profile.svg">
                      <?php endif; ?>
                      <h6 class="fw-bold shadowed-text text-white position-absolute top-50 start-50 translate-middle">
                        <i class="bi bi-box-arrow-in-up-right text-stroke"></i> All
                      </h6>
                    </div> 
                  </a>
                <?php endif; ?>
              </div>
              <h5 class="text-dark fw-bold text-center mt-3"><?php echo $image['title']; ?></h5>
              <div style="word-break: break-word;" data-lazyload>
                <p class="text-secondary" style="word-break: break-word;">
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
              <p class="text-secondary" style="word-wrap: break-word;">
                <a class="text-primary" href="<?php echo $image['link']; ?>">
                  <small>
                    <?php echo (strlen($image['link']) > 40) ? substr($image['link'], 0, 40) . '...' : $image['link']; ?>
                  </small>
                </a>
              </p>
              <div class="container-fluid bg-body-secondary p-2 mt-2 mb-2 rounded-4 text-center align-items-center d-flex justify-content-center">
                <div class="dropdown-center">
                  <button class="btn text-secondary border-0 fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <small>
                      <?php echo date('Y/m/d', strtotime($image['date'])); ?>
                    </small
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item fw-semibold text-center" href="#">
                        uploaded at <?php echo date('F j, Y', strtotime($image['date'])); ?>
                      </a>
                    </li>
                  </ul>
                </div>
                <div class="dropdown-center">
                  <button class="btn text-secondary border-0 fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-heart-fill text-sm"></i> <small><?php echo $fav_count; ?></small>
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item fw-semibold text-center" href="#">
                        total <?php echo $fav_count; ?> favorites
                      </a>
                    </li>
                  </ul>
                </div>
                <div class="dropdown-center">
                  <button class="btn text-secondary border-0 fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-eye-fill"></i> <small><?php echo $viewCount; ?> </small>
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item fw-semibold text-center" href="#">
                        total <?php echo $viewCount; ?> views
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="btn-group w-100" role="group" aria-label="Basic example">
                <button class="btn btn-primary fw-bold rounded-start-4" data-bs-toggle="modal" data-bs-target="#shareLink">
                  <i class="bi bi-share-fill"></i> <small>share</small>
                </button>
                <a class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#downloadOption">
                  <i class="bi bi-cloud-arrow-down-fill"></i> <small>download</small>
                </a>
                <button class="btn btn-primary dropdown-toggle fw-bold rounded-end-4" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#dataModal">
                  <i class="bi bi-info-circle-fill"></i> <small>info</small>
                </button>
                <!-- Data Modal -->
                <div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">All Data from <?php echo $image['title']; ?></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body scrollable-div">
                        <div>
                          <div class="text-dark text-center mt-2 mb-4">
                            <h6 class="fw-bold"><i class="bi bi-file-earmark-plus"></i> Total size of all images: <?php echo $total_size; ?> MB</h6>
                          </div>
                          <button class="btn btn-outline-dark fw-bold w-100 mb-2" id="toggleButton3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDataImage1" aria-expanded="false" aria-controls="collapseExample">
                            <i class="bi bi-caret-down-fill"></i> <small>show more</small>
                          </button>
                          <div class="collapse mt-2" id="collapseDataImage1">
                            <?php foreach ($images as $index => $image) { ?>
                              <div class="mb-3 img-thumbnail border-dark">
                                <ul class="list-unstyled m-0">
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
                              <div class="mt-3 mb-3 img-thumbnail border-dark">
                                <ul class="list-unstyled m-0">
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
                            <a class="btn btn-outline-dark fw-bold w-100" href="#downloadOption" data-bs-toggle="modal">
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
                          <div class="mt-2"i
                            <?php
                              // Retrieve tags for the current image
                              $currentImageTags = explode(',', $image['tags']);
                              $currentImageTags = array_map('trim', $currentImageTags);

                              // Check if there are no tags available
                              if (empty($currentImageTags)) {
                                echo "No tags available";
                              } else {
                                foreach ($currentImageTags as $tag) : 
                              ?>
                              <?php
                                // Initialize tag count
                                $tagCount = 0;

                                // Retrieve all images that contain this tag
                                $query = "SELECT * FROM images WHERE tags LIKE :tag";
                                $tagParam = '%' . $tag . '%';
                                $stmt = $db->prepare($query);
                                $stmt->bindParam(':tag', $tagParam);
                                $stmt->execute();

                                // Count the number of images with this tag
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                  $tagCount++;
                                }
                              ?>
                                <small>
                                  <a href='tagged_images.php?tag=<?php echo urlencode($tag); ?>'
                                  class="btn btn-sm btn-dark mb-1 rounded-3 fw-bold">
                                    <i class="bi bi-tags-fill"></i> <?php echo $tag; ?> (<?php echo $tagCount; ?>)
                                  </a>
                                </small>
                              <?php endforeach; 
                            } ?>
                            <a href="tags.php" class="btn btn-sm btn-dark mb-1 rounded-3 fw-bold">
                              <i class="bi bi-tags-fill"></i> all tags
                            </a>
                          </div>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-dark w-100 fw-bold" data-bs-dismiss="modal">close</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="d-none d-md-flex d-lg-flex mt-2 gap-2">
                <?php if ($next_image): ?>
                  <a class="image-containerA shadow rounded" href="?artworkid=<?= $next_image['id'] ?>">
                    <div class="position-relative">
                      <img class="img-blur object-fit-cover rounded opacity-75" style="width: 100%; height: 160px;" src="../thumbnails/<?php echo $next_image['filename']; ?>" alt="<?php echo $next_image['title']; ?>">
                      <h6 class="fw-bold shadowed-text text-white position-absolute top-50 start-50 translate-middle">
                        <i class="bi bi-arrow-left-circle text-stroke"></i> Next
                      </h6>
                    </div>
                  </a>
                <?php else: ?>
                  <a class="image-containerA shadow rounded" href="../artist.php?id=<?php echo $user['id']; ?>">
                    <div class="position-relative">
                      <?php if (!empty($user['pic'])): ?>
                        <img class="img-blur object-fit-cover rounded opacity-75" style="width: 100%; height: 160px;" alt="<?php echo $user['artist']; ?>" src="../<?php echo $user['pic']; ?>">
                      <?php else: ?>
                        <img class="img-blur object-fit-cover rounded opacity-75" style="width: 100%; height: 160px;" alt="<?php echo $user['artist']; ?>" src="../icon/profile.svg">
                      <?php endif; ?>
                      <h6 class="fw-bold shadowed-text text-white position-absolute top-50 start-50 translate-middle">
                        <i class="bi bi-box-arrow-in-up-left text-stroke"></i> All
                      </h6>
                    </div> 
                  </a>
                <?php endif; ?>
                <a class="image-containerA shadow rounded" href="?artworkid=<?= $image['id'] ?>">
                  <img class="object-fit-cover opacity-50 rounded" style="width: 100%; height: 160px;" src="../thumbnails/<?= $image['filename'] ?>" alt="<?php echo $image['title']; ?>">
                </a>
                <?php if ($prev_image): ?>
                  <a class="image-containerA shadow rounded" href="?artworkid=<?= $prev_image['id'] ?>">
                    <div class="position-relative">
                      <img class="img-blur object-fit-cover rounded opacity-75" style="width: 100%; height: 160px;" src="../thumbnails/<?php echo $prev_image['filename']; ?>" alt="<?php echo $prev_image['title']; ?>">
                      <h6 class="fw-bold shadowed-text text-white position-absolute top-50 start-50 translate-middle">
                        Prev <i class="bi bi-arrow-right-circle text-stroke"></i>
                      </h6>
                    </div>
                  </a>
                <?php else: ?>
                  <a class="image-containerA shadow rounded" href="../artist.php?id=<?php echo $user['id']; ?>">
                    <div class="position-relative">
                      <?php if (!empty($user['pic'])): ?>
                        <img class="img-blur object-fit-cover rounded opacity-75" style="width: 100%; height: 160px;" alt="<?php echo $user['artist']; ?>" src="../<?php echo $user['pic']; ?>">
                      <?php else: ?>
                        <img class="img-blur object-fit-cover rounded opacity-75" style="width: 100%; height: 160px;" alt="<?php echo $user['artist']; ?>" src="../icon/profile.svg">
                      <?php endif; ?>
                      <h6 class="fw-bold shadowed-text text-white position-absolute top-50 start-50 translate-middle">
                        <i class="bi bi-box-arrow-in-up-right text-stroke"></i> All
                      </h6>
                    </div> 
                  </a> 
                <?php endif; ?>
              </div>
              <div class="btn-group w-100">
                <a class="btn btn-primary rounded-4 mt-2 fw-bold rounded-end-0" style="word-wrap: break-word;" data-bs-toggle="modal" href="#imgcarousel" role="button" aria-expanded="false" aria-controls="collapseExample">
                  <small>
                    <i class="bi bi-images"></i> view all <?php echo $user['artist']; ?>'s images
                  </small>
                </a>
                <a class="btn btn-primary rounded-4 mt-2 fw-bold rounded-start-0" style="word-wrap: break-word;" href="../artist.php?id=<?= $user['id'] ?>">
                  <small>
                    <i class="bi bi-box-arrow-up-right text-stroke"></i>
                  </small>
                </a>
              </div>
              <?php include 'imguser.php'; ?>
              <div class="collapse" id="collapseExample">
                <form class="mt-2" action="../add_to_album.php" method="post">
                  <input class="form-control" type="hidden" name="image_id" value="<?= $image['id']; ?>">
                  <select class="form-select fw-bold text-secondary rounded-4 mb-2" name="album_id">
                    <option class="form-control" value=""><small>add to album:</small></option>
                    <?php
                      // Connect to the SQLite database
                      $db = new SQLite3('../database.sqlite');

                      // Get the email of the current user
                      $email = $_SESSION['email'];

                      // Retrieve the list of albums created by the current user
                      $stmt = $db->prepare('SELECT album_name, id FROM album WHERE email = :email');
                      $stmt->bindValue(':email', $email, SQLITE3_TEXT);
                      $results = $stmt->execute();

                      // Loop through each album and create an option in the dropdown list
                      while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                        $album_name = $row['album_name'];
                        $id = $row['id'];
                        echo '<option value="' . $id. '">' . htmlspecialchars($album_name). '</option>';
                      }

                      $db->close();
                    ?>
                  </select>
                  <button class="form-control bg-primary text-white fw-bold rounded-4" type="submit"><small>add to album</small></button>
                </form>
                <iframe class="mt-2 rounded" style="width: 100%; height: 300px;" src="<?php echo $url_comment; ?>"></iframe>
                <a class="btn btn-primary w-100 rounded-4 fw-bold mt-2 mb-2" href="../comment.php?imageid=<?php echo $image['id']; ?>"><i class="bi bi-chat-left-text-fill"></i> <small>view all comments</small></a>
              </div>
              <a class="btn btn-primary rounded-4 w-100 fw-bold text-center" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample" id="toggleButton">
                <i class="bi bi-caret-down-fill"></i> <small>more</small>
              </a>
              <p class="text-secondary mt-3"><i class="bi bi-tags-fill"></i> tags</p>
              <div class="tag-buttons">
                <?php
                  if (!empty($image['tags'])) {
                    $tags = explode(',', $image['tags']);
                    foreach ($tags as $tag) {
                      $tag = trim($tag);
                      if (!empty($tag)) {
                    ?>
                      <a href="../tagged_images.php?tag=<?php echo urlencode($tag); ?>"
                        class="btn btn-sm btn-secondary mb-1 rounded-3 fw-bold opacity-50">
                        <i class="bi bi-tags-fill"></i> <?php echo $tag; ?>
                      </a>
                    <?php
                      }
                    }
                  } else {
                    echo "No tags available.";
                  }
                ?>
                <a href="../tags.php" class="btn btn-sm btn-secondary mb-1 rounded-3 fw-bold opacity-50">
                  <i class="bi bi-tags-fill"></i> all tags
                </a>
              </div>
            </div>
          </div> 
        </div>