        <a class="text-decoration-none link-body-emphasis" href="../image.php?artworkid=<?php echo $image['id']; ?>">
          <div class="card rounded-4 border-0 bg-body-tertiary shadow h-100 mb-1">
            <div class="row">
              <div class="col-4 col-md-2">
                <div class="position-relative">
                  <div class="shadow ratio ratio-1x1 rounded-start-4">
                    <img class="object-fit-cover img-size lazy-load <?php echo ($image['type'] === 'nsfw') ? 'nsfw' : ''; ?>" data-src="../thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>">
                  </div> 
                </div>
              </div>
              <div class="col-8 col-md-10">
                <div class="d-flex align-items-center justify-content-start h-100">
                  <h6 class="fw-bold d-none d-md-block d-lg-block"><?php echo (!is_null($image['title']) && mb_strlen($image['title'], 'UTF-8') > 100) ? mb_substr($image['title'], 0, 100, 'UTF-8') . '...' : $image['title']; ?></h6>
                  <h6 class="fw-bold d-md-none d-lg-none"><?php echo (!is_null($image['title']) && mb_strlen($image['title'], 'UTF-8') > 30) ? mb_substr($image['title'], 0, 30, 'UTF-8') . '...' : $image['title']; ?></h6>
                </div>
              </div>
            </div>
          </div>
        </a>