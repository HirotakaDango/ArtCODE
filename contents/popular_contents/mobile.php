    <div class="d-flex justify-content-center ">
      <div class="d-mobile w-99">
        <div id="carouselExampleFadeMobile" class="carousel slide carousel-fade mb-1" data-bs-ride="carousel">
          <div class="carousel-inner">
            <?php
              $resultP->reset();
              $count = 0;
              while ($imageP = $resultP->fetchArray()): 
                $filename = $imageP['filename'];
                $title = $imageP['title'];
                $image_id = $imageP['id'];
                $image_type = $imageP['type'];
              ?>
                <div class="carousel-item <?php echo $count === 0 ? 'active' : ''; ?>">
                  <a href="image.php?artworkid=<?php echo $image_id; ?>">
                    <div class="position-relative overflow-hidden w-100 h-custom rounded">
                      <img class="d-block rounded w-100 h-custom <?php echo ($image_type === 'nsfw') ? 'blurred' : ''; ?>" src="thumbnails/<?php echo $filename; ?>" alt="<?php echo $title; ?>">
                    </div>
                    <div class="carousel-caption">
                      <h5 class="fw-bold"><?php echo $title; ?></h5>
                    </div>
                  </a>
                </div>
              <?php
                $count++;
              endwhile;
            ?>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleFadeMobile" data-bs-slide="prev">
            <i class="bi bi-chevron-left display-5 text-dark" style="-webkit-text-stroke: 4px;"></i>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleFadeMobile" data-bs-slide="next">
            <i class="bi bi-chevron-right display-5 text-dark" style="-webkit-text-stroke: 4px;"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="d-flex d-md-none mb-2 d-lg-none justify-content-center">
      <div class="media-scroller scrollable-div w-99 snaps-inline overflow-auto">
        <?php
          $resultP->reset();
          while ($imageP = $resultP->fetchArray()): 
          $image_url = $imageP['filename'];
          $image_id = $imageP['id'];
          $image_type = $imageP['type'];
        ?>
          <div class="media-element d-inline-flex">
            <a href="image.php?artworkid=<?php echo $image_id; ?>">
              <div class="position-relative overflow-hidden d-inline-block rounded">
                <img class="hori <?php echo ($image_type === 'nsfw') ? 'blurred' : ''; ?>" src="thumbnails/<?php echo $image_url; ?>" alt="<?php echo $image_title; ?>">
              </div>
            </a>
          </div>
        <?php endwhile; ?>
      </div>
    </div>