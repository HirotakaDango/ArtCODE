    <style>
      @media (min-width: 768px) {
        .h-custom {
          height: 550px;
          object-fit: cover;
          object-position: top;
        }
        
        .t-custom {
          font-size: 42px;
        }
      }
      
      @media (max-width: 767px) {
        .h-custom {
          height: 225px;
          object-fit: cover;
          object-position: top;
        }
      }
    </style>
    <div style="position: absolute; top: 55px; left: 10px; z-index: 2;">
      <a class="btn btn-primary rounded-pill fw-bold btn-sm btn-md btn-lg" href="popular.php">
        <i class="bi bi-star-fill"></i> Popular
      </a>
    </div> 
    <div id="carouselExampleFade" class="carousel slide carousel-fade mb-2" data-bs-ride="carousel">
      <div class="carousel-inner">
        <?php
          $dbP = new SQLite3('database.sqlite');
          $stmtP = $dbP->prepare("SELECT images.id, images.filename, images.tags, images.title, COUNT(favorites.id) AS favorite_count FROM images LEFT JOIN favorites ON images.id = favorites.image_id GROUP BY images.id ORDER BY favorite_count DESC LIMIT 25");
          $resultP = $stmtP->execute();
          $count = 0;
          while ($imageP = $resultP->fetchArray()): 
            $filename = $imageP['filename'];
            $title = $imageP['title'];
            $image_id = $imageP['id'];
          ?>
            <div class="carousel-item <?php echo $count == 0 ? 'active' : ''; ?>">
              <a href="image.php?filename=<?php echo $image_id; ?>">
                <img class="d-block w-100 h-custom" src="thumbnails/<?php echo $filename; ?>" alt="<?php echo $title; ?>">
              </a>
              <div class="container">
                <div class="carousel-caption text-start">
                  <h5 class="text-center t-custom fw-bold" style="text-shadow: 1px 1px 1px #000;"><?php echo $title; ?></h5>
                </div>
              </div>
            </div>
          <?php 
            $count++;
            endwhile;
          ?>
        </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleFade" data-bs-slide="prev">
        <i class="bi bi-arrow-left-circle-fill display-5 text-dark"></i>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleFade" data-bs-slide="next">
        <i class="bi bi-arrow-right-circle-fill display-5 text-dark"></i>
      </button>
    </div>