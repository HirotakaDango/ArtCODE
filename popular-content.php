    <style>
      @media (min-width: 768px) {
        .h-custom {
          height: 300px;
          object-fit: cover;
          object-position: top;
        }

        .d-desktop {
          display: block;
        }

        .d-mobile {
          display: none;
        }
        
        .carousel-caption {
          border-radius: 0 0 5px 5px;
        }
      }

      @media (max-width: 767px) {
        .h-custom {
          height: 225px;
          object-fit: cover;
          object-position: top;
        }

        .d-desktop {
          display: none;
        }

        .d-mobile {
          display: block;
        }
      }

      .grid-container {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        grid-gap: 3px;
        justify-items: center;
        align-items: center;
        margin: 0 4px;
      }

      .grid-item {
        position: relative;
        width: 100%;
        height: 100%;
        max-height: 550px;
        object-fit: cover;
        object-position: top;
      }

      .carousel-caption {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        padding: 10px;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);
        text-align: center;
      }

      .carousel-caption h5 {
        font-size: 20px;
        margin: 0;
        padding: 5px;
      }
    </style>

    <div style="position: absolute; top: 55px; left: 10px; z-index: 2;">
      <a class="btn btn-primary rounded-pill fw-bold btn-sm btn-md btn-lg" href="popular.php">
        <i class="bi bi-star-fill"></i> Popular
      </a>
    </div>

    <div class="d-desktop">
      <div id="carouselExampleFade" class="carousel slide carousel-fade mb-2" data-bs-ride="carousel">
        <div class="carousel-inner">
          <?php
            $dbP = new SQLite3('database.sqlite');
            $stmtP = $dbP->prepare("SELECT images.id, images.filename, images.tags, images.title, COUNT(favorites.id) AS favorite_count FROM images LEFT JOIN favorites ON images.id = favorites.image_id GROUP BY images.id ORDER BY favorite_count DESC LIMIT 28");
            $resultP = $stmtP->execute();
            $count = 0;
            while ($imageP = $resultP->fetchArray()): 
              $filename = $imageP['filename'];
              $title = $imageP['title'];
              $image_id = $imageP['id'];
              if ($count % 4 === 0) {
                echo '<div class="carousel-item ' . ($count === 0 ? 'active' : '') . '">';
                echo '<div class="grid-container">';
              }
          ?>
            <a href="image.php?artworkid=<?php echo $image_id; ?>" class="grid-item">
              <img class="w-100 h-custom rounded shadow" src="thumbnails/<?php echo $filename; ?>" alt="<?php echo $title; ?>">
              <div class="carousel-caption">
                <h5 class="fw-bold"><?php echo $title; ?></h5>
              </div>
            </a>
          <?php
              $count++;
              if ($count % 4 === 0) {
                echo '</div>';
                echo '</div>';
              }
            endwhile;
            if ($count % 4 !== 0) {
              echo '</div>';
              echo '</div>';
            }
          ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleFade" data-bs-slide="prev">
          <i class="bi bi-arrow-left-circle-fill display-5 text-dark"></i>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleFade" data-bs-slide="next">
          <i class="bi bi-arrow-right-circle-fill display-5 text-dark"></i>
        </button>
      </div>
    </div>

    <div class="d-mobile">
      <div id="carouselExampleFadeMobile" class="carousel slide carousel-fade mb-2" data-bs-ride="carousel">
        <div class="carousel-inner">
          <?php
            $resultP->reset();
            $count = 0;
            while ($imageP = $resultP->fetchArray()): 
              $filename = $imageP['filename'];
              $title = $imageP['title'];
              $image_id = $imageP['id'];
          ?>
            <div class="carousel-item <?php echo $count === 0 ? 'active' : ''; ?>">
              <a href="image.php?artworkid=<?php echo $image_id; ?>">
                <img class="d-block w-100 h-custom" src="thumbnails/<?php echo $filename; ?>" alt="<?php echo $title; ?>">
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
          <i class="bi bi-arrow-left-circle-fill display-5 text-dark"></i>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleFadeMobile" data-bs-slide="next">
          <i class="bi bi-arrow-right-circle-fill display-5 text-dark"></i>
        </button>
      </div>
    </div>
