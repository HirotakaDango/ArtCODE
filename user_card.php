      <div id="category-<?php echo $group; ?>" class="category-section pt-5">
        <h5 class='fw-bold text-start'>Category <?php echo $category; ?></h5>
        <div class="row">
          <?php foreach ($currentUsers as $user): ?>
            <div class="col-md-2 col-sm-5 px-0">
              <a class="artist m-1 d-block text-decoration-none" href="artist.php?id=<?= $user['id'] ?>">
                <div class="card rounded-4 border-0 shadow text-bg-dark ratio ratio-1x1">
                  <div class="card-img-overlay d-flex align-items-center justify-content-center">
                    <span class="fw-bold text-center" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);">
                      <?= $user['artist'] ?>
                    </span>
                  </div>
                </div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>