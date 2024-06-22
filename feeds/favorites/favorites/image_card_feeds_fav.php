    <div class="w-100 px-1">
      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-5 g-1">
        <?php while ($image = $result->fetchArray()): ?>
          <div class="col">
            <div class="position-relative">
              <a class="rounded ratio ratio-1x1" href="../../image.php?artworkid=<?php echo $image['id']; ?>">
                <img class="rounded shadow object-fit-cover lazy-load" data-src="../../thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>">
              </a> 
              <div class="position-absolute top-0 start-0">
                <div class="dropdown">
                  <button class="btn btn-sm btn-dark ms-1 mt-1 rounded-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                  </button>
                  <ul class="dropdown-menu">
                    <form method="POST">
                      <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                      <li><button type="submit" class="dropdown-item fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i> <small>unfavorite</small></button></li>
                    </form>
                    <li><button class="dropdown-item fw-bold" onclick="shareImage(<?php echo $image['id']; ?>)"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
                    <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $image['id']; ?>"><i class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
                  </ul>
                  
                  <?php include('../../contents/card_image_4.php'); ?>
                  
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>