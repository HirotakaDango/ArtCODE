    <div class="container-fluid px-1 mt-2">
      <?php if (empty($displayImages)): ?>
        <h5 class="position-absolute top-50 start-50 translate-middle fw-bold">No images found</h5>
      <?php else: ?>
        <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 g-1">
          <?php foreach ($displayImages as $image): ?>
            <div class="col">
              <div class="card border-0 rounded-4">
                <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal<?= $image['id']; ?>">
                  <div class="ratio ratio-1x1">
                    <img class="rounded object-fit-cover lazy-load" data-src="<?= $websiteUrl . '/' . $thumbPath . '/' . $image['filename']; ?>" alt="<?= $image['title']; ?>">
                  </div>
                </a>
              </div>
            </div>
            <div class="modal fade" id="imageModal<?= $image['id']; ?>" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen">
                <div class="modal-content border-0">
                  <div class="modal-body p-0">
                    <button type="button" class="btn border-0 link-body-emphasis z-3 position-fixed top-0 end-0 text-shadow" data-bs-dismiss="modal"><i class="bi bi-chevron-down fs-3" style="-webkit-text-stroke: 3px;"></i></button>
                    <div class="row g-0">
                      <div class="col-md-7 h-100 overflow-auto vh-100">
                        <div>
                          <div class="position-relative">
                            <img class="w-100 h-100 lazy-load" data-src="<?= $websiteUrl . '/' . $folderPath . '/' . $image['filename']; ?>" alt="<?= $image['title']; ?>">
                            <a class="btn border-0 link-body-emphasis position-absolute bottom-0 end-0" href="<?= $websiteUrl . '/' . $folderPath . '/' . $image['filename']; ?>" download="<?= $image['filename']; ?>"><i class="bi bi-download text-white text-shadow fs-4"></i></a>
                          </div>
                          <?php
                            foreach ($imageChildData as $childImage) {
                              if ($childImage['image_id'] === $image['id']) {
                                echo '<div class="position-relative">';
                                echo '<img class="w-100 h-100 lazy-load mt-1" data-src="' . $websiteUrl . '/' . $folderPath . '/' . $childImage['filename'] . '" alt="Child Image">';
                                echo '<a class="btn border-0 link-body-emphasis position-absolute bottom-0 end-0" href="' . $websiteUrl . '/' . $folderPath . '/' . $childImage['filename'] . '" download="' . $childImage['filename'] . '"><i class="bi bi-download text-white text-shadow fs-4"></i></a>';
                                echo '</div>';
                              }
                            }
                          ?>
                        </div>
                      </div>
                      <div class="col-md-5 h-100 overflow-auto vh-100 bg-body-tertiary">
                        <div class="p-3">
                          <p class="text-start fw-medium mt-3"><small><i>images uploaded by <a href="<?php echo $websiteUrl . '/artist.php?id=' . $image['userId']; ?>"><?= $image['artist']; ?></a></i></small></p>
                          <h5 class="text-center fw-bold my-4"><?= $image['title']; ?></h5>
                          <p class="text-start fw-medium" style="word-wrap: break-word;">
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
                          <div class="w-100 mt-4">
                            <?php
                              if (!empty($image['tags'])) {
                                $tags = explode(',', $image['tags']);
                                foreach ($tags as $tag) {
                                  $tag = trim($tag);
                                  if (!empty($tag)) {
                                ?>
                                  <a href="<?= $websiteUrl; ?>/tagged_images.php?tag=<?php echo urlencode($tag); ?>"
                                    class="btn btn-sm border-0 link-body-emphasis fw-bold">
                                    <i class="bi bi-tags-fill"></i> <?php echo $tag; ?>
                                  </a>
                                <?php
                                  }
                                }
                              } else {
                                echo "No tags available.";
                              }
                            ?>
                          </div>
                          <div class="btn-group w-100 gap-1 mt-4">
                            <a class="w-50 btn border-0 link-body-emphasis rounded-3 fw-bold" href="<?php echo $websiteUrl . '/artist.php?id=' . $image['userId']; ?>"><i class="bi bi-person-circle"></i> <?= $image['artist']; ?></a>
                            <a class="w-50 btn border-0 link-body-emphasis rounded-3 fw-bold" href="<?= $websiteUrl; ?>/image.php?artworkid=<?= $image['id']; ?>" target="_blank"><i class="bi bi-box-arrow-up-right"></i> original source</a>
                          </div>
                          <div class="btn-group w-100 gap-1 mt-1 mb-3">
                            <button class="w-50 btn border-0 link-body-emphasis rounded-3 fw-bold"><?= $image['view_count']; ?> views</button>
                            <button class="w-50 btn border-0 link-body-emphasis rounded-3 fw-bold"><?= $image['favorites_count']; ?> favorites</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>