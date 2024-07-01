      <div id="category-<?php echo $category; ?>" class="category-section pt-5">
        <h5 class="fw-bold text-start">Category <?php echo $category; ?></h5>
        <div class="row">
          <?php foreach ($currentgroups as $tag => $count): ?>
            <?php
              // Check if the tag has any associated images
              $stmt = $db->prepare("SELECT * FROM images WHERE `group` LIKE ? ORDER BY id DESC LIMIT 1");
              $stmt->bindValue(1, '%' . $tag . '%');
              $imageResult = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
              if ($imageResult):
            ?>
            <div class="col-md-2 col-sm-5 px-0">
              <a href="../tagged_images.php?tag=<?php echo str_replace('%27', "'", urlencode($tag)); ?>" class="m-1 d-block text-decoration-none">
                <div class="card rounded-4 border-0 shadow text-bg-dark ratio ratio-1x1">
                  <img data-src="../thumbnails/<?php echo $imageResult['filename']; ?>" alt="<?php echo $imageResult['title']; ?>" class="lazy-load card-img object-fit-cover rounded-4 w-100 h-100">
                  <div class="card-img-overlay d-flex align-items-center justify-content-center">
                    <span class="fw-bold text-center" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);">
                      <?php echo $tag . ' (' . $count . ')'; ?>
                    </span>
                  </div>
                </div>
              </a>
            </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>