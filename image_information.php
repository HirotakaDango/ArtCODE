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
                        total <?php echo $fav_count; ?> favorites
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
                <a class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-4 fw-bold w-100" href="/similar_image_search/?image=/images/<?php echo urlencode($image['filename']); ?>">
                  <small>find similar image</small>
                </a>
              </div>
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
              <button type="button" class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold rounded-4 w-100 mb-2" data-bs-toggle="modal" data-bs-target="#previewMangaModal">
                <small>previews of current artwork</small>
              </button>
              <!-- Preview Modal -->
              <div class="modal fade" id="previewMangaModal" tabindex="-1" aria-labelledby="previewMangaModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen m-0 p-0">
                  <div class="modal-content m-0 p-0 border-0 rounded-0">
                    <iframe class="w-100 h-100 border-0" src="<?php echo 'preview.php?artworkid=' . $_GET['artworkid']; ?>"></iframe>
                    <button type="button" class="btn btn-small btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium position-fixed bottom-0 end-0 m-2 rounded-pill" data-bs-dismiss="modal">close</button>
                  </div>
                </div>
              </div>