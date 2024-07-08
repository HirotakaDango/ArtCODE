    <?php
      // Get all of the images uploaded by the current user
      $stmt = $db->prepare("SELECT images.*, COUNT(favorites.id) AS favorite_count FROM images LEFT JOIN favorites ON images.id = favorites.image_id WHERE images.email = :email GROUP BY images.id ORDER BY favorite_count DESC LIMIT 6");
      $stmt->bindValue(':email', $email);
      if ($stmt->execute()) {
        // Fetch the results as an associative array
        $resultsPopular = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } else {
        // Handle the query execution error
        echo "Error executing the query.";
      }
    ?>
    
    <style>.text-shadow { text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); } </style>
    <h6 class="container-fluid fw-bold mb-2">Popular images from <?php echo $artist; ?></h6>
    
    <!-- Mobile -->
    <div id="carouselPopularMobile" class="carousel slide d-md-none d-lg-none mb-3">
      <div class="carousel-indicators">
        <?php foreach ($resultsPopular as $key => $imagePopular): ?>
          <button type="button" data-bs-target="#carouselPopularMobile" data-bs-slide-to="<?php echo $key; ?>"<?php if ($key === 0) echo ' class="active"'; ?> aria-label="Slide <?php echo $key + 1; ?>"></button>
        <?php endforeach; ?>
      </div>
      <div class="carousel-inner">
        <?php foreach ($resultsPopular as $key => $imagePopular): ?>
          <div class="carousel-item<?php if ($key === 0) echo ' active'; ?>">
            <a class="d-block" href="../image.php?artworkid=<?php echo $imagePopular['id']; ?>">
              <img src="../thumbnails/<?php echo $imagePopular['filename']; ?>" class="d-block w-100 object-fit-cover" height="280" alt="<?php echo $imagePopular['title']; ?>">
              <div class="carousel-caption d-md-block">
                <h5 class="fw-bold text-white text-shadow"><?php echo $imagePopular['title']; ?></h5>
                <p class="fw-medium text-white text-shadow"><?php echo $imagePopular['imgdesc']; ?></p>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselPopularMobile" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselPopularMobile" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
    <!-- End of Mobile -->
    
    <!-- Desktop -->
    <div class="d-none d-md-block d-lg-block mb-3 ">
      <div class="d-flex justify-content-center align-item-center">
        <div id="carouselPopularDesktop" class="carousel slide" style="width: 99.5%;">
          <div class="carousel-inner rounded">
            <?php $itemCount = count($resultsPopular); ?>
            <?php for ($i = 0; $i < $itemCount; $i += 1): ?>
              <div class=" carousel-item<?php if ($i === 0) echo ' active'; ?>">
                <div class="row">
                  <div class="col-md-6 m-0 p-0 pe-1">
                    <div class="position-relative">
                      <a class="d-block" href="../image.php?artworkid=<?php echo $resultsPopular[$i]['id']; ?>">
                        <img src="../thumbnails/<?php echo $resultsPopular[$i]['filename']; ?>" class="d-block w-100 object-fit-cover rounded" height="300" alt="<?php echo $resultsPopular[$i]['title']; ?>">
                        <div class="carousel-caption d-md-block position-absolute">
                          <h5 class="fw-bold text-white text-shadow"><?php echo $resultsPopular[$i]['title']; ?></h5>
                          <p class="fw-medium text-white text-shadow"><?php echo $resultsPopular[$i]['imgdesc']; ?></p>
                        </div>
                      </a>
                    </div>
                  </div>
                  <?php if ($i + 1 < $itemCount): ?>
                    <div class="col-md-6 m-0 p-0 ps-1">
                      <div class="position-relative">
                        <a class="d-block" href="../image.php?artworkid=<?php echo $resultsPopular[$i + 1]['id']; ?>">
                          <img src="../thumbnails/<?php echo $resultsPopular[$i + 1]['filename']; ?>" class="d-block w-100 object-fit-cover rounded" height="300" alt="<?php echo $resultsPopular[$i + 1]['title']; ?>">
                          <div class="carousel-caption d-md-block position-absolute">
                            <h5 class="fw-bold text-white text-shadow"><?php echo $resultsPopular[$i + 1]['title']; ?></h5>
                            <p class="fw-medium text-white text-shadow"><?php echo $resultsPopular[$i + 1]['imgdesc']; ?></p>
                          </div>
                        </a>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endfor; ?>
          </div>
          <div class="carousel-indicators">
            <?php for ($i = 0; $i < $itemCount; $i += 2): ?>
              <button type="button" data-bs-target="#carouselPopularDesktop" data-bs-slide-to="<?php echo $i; ?>"<?php if ($i === 0) echo ' class="active"'; ?> aria-label="Slide <?php echo $i / 2 + 1; ?>"></button>
            <?php endfor; ?>
          </div>
        </div>
      </div>
    </div>
    <!-- End of Desktop -->

