              <div id="music-player" class="w-100">
                <div class="d-flex fw-medium text-white">
                  <span class="me-auto small" id="duration"></span>
                  <span class="ms-auto small" id="duration-left"></span>
                </div>
                <audio id="player" class="d-none" controls>
                  <source src="<?php echo $musicFile; ?>" type="audio/mpeg">
                  Your browser does not support the audio element.
                </audio>
                <input type="range" class="w-100 form-range" id="duration-slider" value="0">
                <div class="modal fade" id="optionModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4 border-0">
                      <div class="modal-body fw-medium">
                        <div class="mb-4">
                          <label class="form-label mb-3" for="speed">Speed</label>
                          <select class="form-select fw-medium" id="speed">
                            <option value="0.00">0.00x</option>
                            <option value="0.25">0.25x</option>
                            <option value="0.50">0.50x</option>
                            <option value="0.75">0.75x</option>
                            <option value="1.00" selected>1.00x</option>
                            <option value="1.25">1.25x</option>
                            <option value="1.50">1.50x</option>
                            <option value="1.75">1.75x</option>
                            <option value="2.00">2.00x</option>
                            <option value="2.25">2.25x</option>
                            <option value="2.50">2.50x</option>
                            <option value="2.75">2.75x</option>
                            <option value="3.00">3.00x</option>
                            <option value="3.25">3.25x</option>
                            <option value="3.50">3.50x</option>
                            <option value="3.75">3.75x</option>
                            <option value="4.00">4.00x</option>
                            <option value="4.25">4.25x</option>
                            <option value="4.50">4.50x</option>
                            <option value="4.75">4.75x</option>
                            <option value="5.00">5.00x</option>
                          </select>
                        </div>
                        <div class="mb-4">
                          <label class="form-label" for="volume">Volume</label>
                          <div class="d-flex justify-content-start align-items-center gap-2">
                            <i class="bi bi-volume-mute-fill me-auto fs-3"></i>
                            <input class="form-range mx-auto" type="range" id="volume" min="0" max="1" step="0.01" value="1">
                            <i class="bi bi-volume-up-fill ms-auto fs-3"></i>
                          </div>
                        </div>
                        <?php if ($user_email === $email): ?>
                          <a class="btn border-0 bg-body-tertiary link-body-emphasis fw-bold w-100 mb-2" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/music/edit.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&id=<?php echo $row['id']; ?>">
                            Edit Song
                          </a>
                        <?php endif; ?>
                        <a class="btn border-0 bg-body-tertiary link-body-emphasis fw-bold w-100 mb-2" href="play_simple.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&album=<?php echo urlencode($row['album']); ?>&id=<?php echo $row['id']; ?>">Play Simple Mode</a>
                        <a class="btn border-0 bg-body-tertiary link-body-emphasis fw-bold w-100" href="<?php echo $row['file']; ?>" download>Download Song</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>