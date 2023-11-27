    <div class="d-desktop test">
      <div id="carouselExampleFade" class="carousel slide carousel-fade mb-2" data-bs-ride="carousel">
        <div class="carousel-inner">
          <?php
            $dbPath1 = $_SERVER['DOCUMENT_ROOT'] . '/database.sqlite';
            $dbP = new SQLite3($dbPath1);
            $stmtP = $dbP->prepare("SELECT images.id, images.filename, images.tags, images.title, images.type, COUNT(favorites.id) AS favorite_count FROM images LEFT JOIN favorites ON images.id = favorites.image_id GROUP BY images.id ORDER BY favorite_count DESC LIMIT 42");
            $resultP = $stmtP->execute();
            $count = 0;
            while ($imageP = $resultP->fetchArray()): 
              $filename = $imageP['filename'];
              $title = $imageP['title'];
              $image_id = $imageP['id'];
              $image_type = $imageP['type'];
              if ($count % 7 === 0) {
                echo '<div class="carousel-item ' . ($count === 0 ? 'active' : '') . '">';
                echo '<div class="grid-container">';
              }
          ?>
            <a href="../image.php?artworkid=<?php echo $image_id; ?>" class="grid-item item"> <!-- Add the "item" class here -->
              <div class="position-relative overflow-hidden w-100 h-custom rounded">
                <img class="d-block rounded w-100 h-custom <?php echo ($image_type === 'nsfw') ? 'blurred' : ''; ?>" src="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/thumbnails/<?php echo $filename; ?>" alt="<?php echo $title; ?>">
              </div> 
              <div class="carousel-caption">
                <h5 class="fw-bold"><?php echo $title; ?></h5>
              </div>
            </a>
          <?php
              $count++;
              if ($count % 7 === 0) {
                echo '</div>';
                echo '</div>';
              }
            endwhile;
            if ($count % 7 !== 0) {
              echo '</div>';
              echo '</div>';
            }
          ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleFade" data-bs-slide="prev">
          <i class="bi bi-chevron-left display-5 text-dark" style="-webkit-text-stroke: 4px;"></i>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleFade" data-bs-slide="next">
          <i class="bi bi-chevron-right display-5 text-dark" style="-webkit-text-stroke: 4px;"></i>
        </button>
      </div>
    </div>
    <div class="d-none d-md-block d-lg-block">
      <div class="d-flex mb-2 justify-content-center">
        <div class="media-scroller scrollable-div w-99 snaps-inline overflow-auto">
          <?php
            $resultP->reset();
            while ($imageP = $resultP->fetchArray()): 
            $image_url = $imageP['filename'];
            $image_id = $imageP['id'];
            $image_type = $imageP['type'];
          ?>
            <div class="media-element d-inline-flex">
              <a href="../image.php?artworkid=<?php echo $image_id; ?>">
                <div class="position-relative overflow-hidden d-inline-block rounded">
                  <img class="hori <?php echo ($image_type === 'nsfw') ? 'blurred' : ''; ?>" src="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/thumbnails/<?php echo $image_url; ?>" alt="<?php echo $image_title; ?>">
                </div>
              </a>
            </div>
          <?php endwhile; ?>
        </div>
      </div>
    </div>