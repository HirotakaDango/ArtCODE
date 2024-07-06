    <div class="w-100 px-1">
      <div class="<?php include('../rows_columns/row-cols.php'); echo $rows_columns; ?>">
        <?php foreach ($images as $image): ?>
          <div class="col">
            <div class="position-relative">
              <a class="rounded rounded-bottom-0 ratio ratio-1x1" href="../image.php?artworkid=<?php echo htmlspecialchars($image['id']); ?>">
                <img class="rounded rounded-bottom-0 object-fit-cover lazy-load" data-src="../thumbnails/<?php echo htmlspecialchars($image['filename']); ?>" alt="<?php echo $image['title']; ?>">
              </a>
              <?php
                $current_image_id = $image['id'];
                
                // Query to count main image from the images table
                $stmt = $db->prepare("SELECT COUNT(*) as image_count FROM images WHERE id = :id");
                $stmt->bindValue(':id', $current_image_id, SQLITE3_INTEGER);
                $imageCountQuery = $stmt->execute();
                if ($imageCountQuery) {
                  $imageCountRow = $imageCountQuery->fetchArray(SQLITE3_ASSOC);
                  $imageCount = $imageCountRow ? $imageCountRow['image_count'] : 0;
                } else {
                  $imageCount = 0;
                }
            
                // Query to count associated images from the image_child table
                $stmt = $db->prepare("SELECT COUNT(*) as child_image_count FROM image_child WHERE image_id = :image_id");
                $stmt->bindValue(':image_id', $current_image_id, SQLITE3_INTEGER);
                $childImageCountQuery = $stmt->execute();
                if ($childImageCountQuery) {
                  $childImageCountRow = $childImageCountQuery->fetchArray(SQLITE3_ASSOC);
                  $childImageCount = $childImageCountRow ? $childImageCountRow['child_image_count'] : 0;
                } else {
                  $childImageCount = 0;
                }
            
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
                      $is_favorited = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = {$image['id']}");
                      if ($is_favorited) {
                    ?>
                      <form method="POST">
                        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                        <li><button type="submit" class="dropdown-item fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i> <small>unfavorite</small></button></li>
                      </form>
                    <?php } else { ?>
                      <form method="POST">
                        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                        <li><button type="submit" class="dropdown-item fw-bold" name="favorite"><i class="bi bi-heart"></i> <small>favorite</small></button></li>
                      </form>
                    <?php } ?>
                    <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#shareImage<?php echo $image['id']; ?>"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
                    <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $image['id']; ?>"><i class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this image from the album?');">
                      <input type="hidden" name="image_album_id" value="<?php echo htmlspecialchars($image['image_album_id']); ?>">
                      <li><button type="submit" name="delete_image" class="dropdown-item fw-bold"><i class="bi bi-trash-fill"></i> delete</button></li>
                    </form> 
                  </ul>
                  <?php include('share_album.php'); ?>
                  
                  <?php include('../card_image.php'); ?>
                  
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>