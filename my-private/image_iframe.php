        <div class="cool-6">
          <div class="bg-body-tertiary d-flex justify-content-center d-md-none d-lg-none">
            <?php if ($next_image): ?>
              <a class="img-pointer btn me-auto border-0" href="?<?php echo http_build_query(array_merge($_GET, ['artworkid' => $next_image['id']])); ?>">
                <i class="bi bi-chevron-left text-stroke-2"></i>
              </a>
            <?php else: ?>
              <button class="img-pointer btn me-auto border-0" onclick="location.href='/artist.php?<?php echo http_build_query(array_merge($_GET, ['by' => 'newest', 'id' => $user['id']])); ?>'">
                <i class="bi bi-box-arrow-in-up-left text-stroke"></i>
              </button>
            <?php endif; ?>
            <h6 class="mx-auto img-pointer user-select-none text-center fw-bold scrollable-title mt-2" style="overflow-x: auto; white-space: nowrap; margin: 0 auto;">
              <?php echo $image['title']; ?>
            </h6>
            <?php if ($prev_image): ?>
              <a class="img-pointer btn ms-auto border-0" href="?<?php echo http_build_query(array_merge($_GET, ['artworkid' => $prev_image['id']])); ?>">
                <i class="bi bi-chevron-right text-stroke-2"></i>
              </a>
            <?php else: ?>
              <button class="img-pointer btn ms-auto border-0" onclick="location.href='/artist.php?<?php echo http_build_query(array_merge($_GET, ['by' => 'newest', 'id' => $user['id']])); ?>'">
                <i class="bi bi-box-arrow-in-up-right text-stroke"></i>
              </button>
            <?php endif; ?>
          </div>
          <div class="caard position-relative">
            <?php
              $id    = (int)$image['id'];
              $ratio = $width > 0 ? ($height/$width)*100 : 56.25;
            ?>
            <div id="iframeContainer<?= $id ?>" class="ratio" style="--bs-aspect-ratio: <?= $ratio ?>%;">
              <a href="#" id="originalImageLink" data-bs-toggle="modal" data-bs-target="#originalImageModal" data-original-src="/images/<?php echo $image['filename']; ?>">
                <iframe src="iframe_image_view.php?artworkid=<?= $id ?>" class="ratio-item border-0 shadow-lg rounded-r h-100 w-100" allowfullscreen scrolling="no" frameborder="0"></iframe>
              </a>
            </div>
            <?php include('view_option.php'); ?>
            <div class="d-none d-md-block">
              <div class="d-flex justify-content-between align-items-center mt-2">
                <?php
                  // Prepare current query params except artworkid for navigation
                  $baseParams = $_GET;
                  unset($baseParams['artworkid']);
                ?>
            
                <?php if ($next_image): ?>
                  <a
                    id="nextPageLink"
                    class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold rounded img-pointer me-auto border-0 d-flex justify-content-center align-items-center"
                    href="?<?php echo http_build_query(array_merge($baseParams, ['artworkid' => $next_image['id']])); ?>">
                    <i class="bi bi-chevron-left text-stroke-2"></i>
                  </a>
                <?php else: ?>
                  <button
                    id="nextPageLink"
                    class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold rounded img-pointer me-auto border-0 d-flex justify-content-center align-items-center"
                    onclick="window.location.href='/artist.php?by=newest&id=<?php echo $user['id']; ?>'">
                    <i class="bi bi-box-arrow-in-up-left text-stroke"></i>
                  </button>
                <?php endif; ?>
            
                <?php if ($prev_image): ?>
                  <a
                    id="prevPageLink"
                    class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold rounded img-pointer ms-auto border-0 d-flex justify-content-center align-items-center"
                    href="?<?php echo http_build_query(array_merge($baseParams, ['artworkid' => $prev_image['id']])); ?>">
                    <i class="bi bi-chevron-right text-stroke-2"></i>
                  </a>
                <?php else: ?>
                  <button
                    id="prevPageLink"
                    class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold rounded img-pointer ms-auto border-0 d-flex justify-content-center align-items-center"
                    onclick="window.location.href='/artist.php?by=newest&id=<?php echo $user['id']; ?>'">
                    <i class="bi bi-box-arrow-in-up-right text-stroke"></i>
                  </button>
                <?php endif; ?>
              </div>
            </div>
            <script>
              document.addEventListener('keydown', function(e) {
                // Skip if focused on an input or textarea
                if (['input', 'textarea'].includes(e.target.tagName.toLowerCase())) return;
                if (e.key === 'ArrowRight') {
                  const prevLink = document.getElementById('prevPageLink');
                  if (prevLink) {
                    prevLink.click();
                  }
                } else if (e.key === 'ArrowLeft') {
                  const nextLink = document.getElementById('nextPageLink');
                  if (nextLink) {
                    nextLink.click();
                  }
                }
              });
            </script>
            <?php if ($next_image): ?>
              <div class="d-md-none d-lg-none">
                <a class="btn btn-sm opacity-75 rounded fw-bold position-absolute start-0 top-50 translate-middle-y rounded-start-0" href="?<?php echo http_build_query(array_merge($_GET, ['artworkid' => $next_image['id']])); ?>">
                  <i class="bi bi-chevron-left display-f" style="-webkit-text-stroke: 4px;"></i>
                </a>
              </div>
            <?php endif; ?> 
            <?php if ($prev_image): ?>
              <div class="d-md-none d-lg-none">
                <a class="btn btn-sm opacity-75 rounded fw-bold position-absolute end-0 top-50 translate-middle-y rounded-end-0" href="?<?php echo http_build_query(array_merge($_GET, ['artworkid' => $prev_image['id']])); ?>">
                  <i class="bi bi-chevron-right display-f" style="-webkit-text-stroke: 4px;"></i>
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>