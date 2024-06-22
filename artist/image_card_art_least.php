    <div class="w-100 px-1 my-2">
      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-5 g-1">
        <?php foreach ($images as $imageL): ?>
          <div class="col">
            <div class="position-relative">
              <a class="rounded ratio ratio-1x1" href="../image.php?artworkid=<?php echo $imageL['id']; ?>">
                <img class="rounded shadow object-fit-cover lazy-load <?php echo ($imageL['type'] === 'nsfw') ? 'nsfw' : ''; ?>" data-src="../thumbnails/<?php echo $imageL['filename']; ?>" alt="<?php echo $imageL['title']; ?>">
              </a> 
              <div class="position-absolute top-0 start-0">
                <div class="dropdown">
                  <button class="btn btn-sm btn-dark ms-1 mt-1 rounded-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                  </button>
                  <ul class="dropdown-menu">
                    <?php
                      $is_favorited = $db->query("SELECT COUNT(*) FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = {$imageL['id']}")->fetchColumn();
                      if ($is_favorited) {
                    ?>
                      <form method="POST">
                        <input type="hidden" name="image_id" value="<?php echo $imageL['id']; ?>">
                        <li><button type="submit" class="dropdown-item fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i> <small>unfavorite</small></button></li>
                      </form>
                    <?php } else { ?>
                      <form method="POST">
                        <input type="hidden" name="image_id" value="<?php echo $imageL['id']; ?>">
                        <li><button type="submit" class="dropdown-item fw-bold" name="favorite"><i class="bi bi-heart"></i> <small>favorite</small></button></li>
                      </form>
                    <?php } ?>
                    <li><button class="dropdown-item fw-bold" onclick="shareImage(<?php echo $imageL['id']; ?>)"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
                    <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $imageL['id']; ?>"><i class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
                  </ul>

                  <?php include($_SERVER['DOCUMENT_ROOT'] . '/artist/components/card_image_least.php'); ?>

                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>