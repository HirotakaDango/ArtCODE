    <div class="w-100 px-1 my-2">
      <div class="<?php include('../rows_columns/row-cols.php'); echo $rows_columns; ?>">
        <?php foreach ($images as $imageM): ?>
          <div class="col">
            <div class="position-relative">
              <a class="rounded ratio ratio-1x1" href="../image.php?artworkid=<?php echo $imageM['id']; ?>">
                <img class="rounded shadow object-fit-cover lazy-load <?php echo ($imageM['type'] === 'nsfw') ? 'nsfw' : ''; ?>" data-src="../thumbnails/<?php echo $imageM['filename']; ?>" alt="<?php echo $imageM['title']; ?>">
              </a> 
              <?php
                $current_image_id = $imageM['id'];
                
                // Query to count main image from the images table
                $stmt = $db->prepare("SELECT COUNT(*) as image_count FROM images WHERE id = :id");
                $stmt->bindValue(':id', $current_image_id, PDO::PARAM_INT);
                $stmt->execute();
                $imageCountRow = $stmt->fetch(PDO::FETCH_ASSOC);
                $imageCount = $imageCountRow ? $imageCountRow['image_count'] : 0;
            
                // Query to count associated images from the image_child table
                $stmt = $db->prepare("SELECT COUNT(*) as child_image_count FROM image_child WHERE image_id = :image_id");
                $stmt->bindValue(':image_id', $current_image_id, PDO::PARAM_INT);
                $stmt->execute();
                $childImageCountRow = $stmt->fetch(PDO::FETCH_ASSOC);
                $childImageCount = $childImageCountRow ? $childImageCountRow['child_image_count'] : 0;
            
                // Total count of main images and associated images
                $totalImagesCount = $imageCount + $childImageCount;
              ?>
              <?php include('../rows_columns/image_counts.php'); ?>
              <div class="position-absolute top-0 start-0">
                <div class="dropdown">
                  <button class="btn border-0 p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical text-white link-body-emphasis fs-5" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); text-stroke: 2;"></i>
                  </button>
                  <ul class="dropdown-menu">
                    <?php
                      $is_favorited = $db->query("SELECT COUNT(*) FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = {$imageM['id']}")->fetchColumn();
                      if ($is_favorited) {
                    ?>
                      <form method="POST">
                        <input type="hidden" name="image_id" value="<?php echo $imageM['id']; ?>">
                        <li><button type="submit" class="dropdown-item fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i> <small>unfavorite</small></button></li>
                      </form>
                    <?php } else { ?>
                      <form method="POST">
                        <input type="hidden" name="image_id" value="<?php echo $imageM['id']; ?>">
                        <li><button type="submit" class="dropdown-item fw-bold" name="favorite"><i class="bi bi-heart"></i> <small>favorite</small></button></li>
                      </form>
                    <?php } ?>
                    <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#shareImage<?php echo $imageM['id']; ?>"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
                    <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $imageM['id']; ?>"><i class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
                  </ul>
                  <?php include('share_artist_month.php'); ?>

                  <?php include($_SERVER['DOCUMENT_ROOT'] . '/artist/components/card_image_month.php'); ?>

                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>