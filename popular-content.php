    <style>
      @media (min-width: 768px) {
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
        grid-template-columns: repeat(4, 1fr);
        grid-gap: 3px;
        justify-items: center;
        align-items: center;
        margin: 0 4px;
      }

      .grid-item {
        position: relative;
        width: 100%;
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
      
      .w-99 {
        width: 98.45%;
      }
    
      .item {
        display: flex;
        grid-row: span 2;
        height: 100%;
      }


      .item:first-child {
        grid-column: span 2;
        grid-row: span 2;
        height: 100%;
      }

      .item img {
        width: 100%;
        height: 300px;
        object-fit: cover;
        object-position: top;
      }

      .carousel-caption h5 {
        font-size: 20px;
        margin: 0;
        padding: 5px;
      }
      
      .rotating-class {
        animation: loader 2s infinite; /* Reducing animation duration for more FPS */
        display: flex;
        justify-content: center;
        align-items: center;
        will-change: transform;
        transform-origin: center;
      }

      @keyframes loader {
        0% {
          transform: rotate(0deg);
        }
        100% {
          border-radius: 50%;
          transform: rotate(360deg); /* Use 360deg for a full rotation */
        }
      }

      .rotating-class {
        animation-timing-function: steps(60); /* You can experiment with the number of steps */
      }

      .hori {
        border-radius: 5px;
        width: 100px;
        height: 120px;
        object-fit: cover;
      }

      .media-scroller {
        display: grid;
        gap: 4px; /* Updated gap value */
        grid-auto-flow: column;
        overflow-x: auto;
        overscroll-behavior-inline: contain;
      }

      .snaps-inline {
        scroll-snap-type: inline mandatory;
        scroll-padding-inline: var(--_spacer, 1rem);
      }
  
      .snaps-inline > * {
        scroll-snap-align: start;
      }

      .scrollable-div {
        overflow: auto;
        scrollbar-width: thin;  /* For Firefox */
        -ms-overflow-style: none;  /* For Internet Explorer and Edge */
        scrollbar-color: transparent transparent;  /* For Chrome, Safari, and Opera */
      }

      .scrollable-div::-webkit-scrollbar {
        width: 0;
        height: 0;
        background-color: transparent;
      }
      
      .scrollable-div::-webkit-scrollbar-thumb {
        background-color: transparent;
      }
      
      .blurred {
        filter: blur(4px);
      }
    </style>
    <div style="position: absolute; top: 76px; z-index: 2;">
      <a class="btn btn-primary d-flex rounded-start-0 rounded-pill fw-bold" href="popular.php">
        <i class="bi bi-star-fill rotating-class me-1"></i> Popular
      </a>
    </div>
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
          <i class="bi bi-arrow-left-circle-fill display-5 text-dark"></i>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleFade" data-bs-slide="next">
          <i class="bi bi-arrow-right-circle-fill display-5 text-dark"></i>
        </button>
      </div>
    </div>
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
            <i class="bi bi-arrow-left-circle-fill display-5 text-dark"></i>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleFadeMobile" data-bs-slide="next">
            <i class="bi bi-arrow-right-circle-fill display-5 text-dark"></i>
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
