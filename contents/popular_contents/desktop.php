    <div class="d-desktop test">
      <div id="carouselExampleFade" class="carousel slide carousel-fade mb-2" data-bs-ride="carousel">
        <div class="carousel-inner">
          <?php
            $dbP = new SQLite3('database.sqlite');
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
            <a href="image.php?artworkid=<?php echo $image_id; ?>" class="grid-item item"> <!-- Add the "item" class here -->
              <div class="position-relative overflow-hidden w-100 h-custom rounded">
                <img class="d-block rounded w-100 h-custom <?php echo ($image_type === 'nsfw') ? 'blurred' : ''; ?>" src="thumbnails/<?php echo $filename; ?>" alt="<?php echo $title; ?>">
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