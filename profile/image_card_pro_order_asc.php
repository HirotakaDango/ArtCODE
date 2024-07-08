    <div class="w-100 px-1">
      <div class="<?php include('../rows_columns/row-cols.php'); echo $rows_columns; ?>">
        <?php foreach ($results as $imageA): ?>
          <div class="col">
            <div class="position-relative">
              <a class="rounded ratio ratio-1x1" href="../image.php?artworkid=<?php echo $imageA['id']; ?>">
                <img class="rounded shadow object-fit-cover lazy-load <?php echo ($imageA['type'] === 'nsfw') ? 'nsfw' : ''; ?>" data-src="../thumbnails/<?php echo $imageA['filename']; ?>" alt="<?php echo $imageA['title']; ?>">
              </a> 
              <?php
                $current_image_id = $imageA['id'];
                
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
                    <li><button class="dropdown-item fw-bold" onclick="location.href='../edit_image.php?id=<?php echo $imageA['id']; ?>'" ><i class="bi bi-pencil-fill"></i> edit image</button></li>
                    <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#deleteImage_<?php echo $imageA['id']; ?>"><i class="bi bi-trash-fill"></i> delete</button></li>
                    <?php
                    $is_favorited = false; // Initialize to false

                    // Check if the image is favorited
                    $stmt = $db->prepare("SELECT COUNT(*) AS num_favorites FROM favorites WHERE email = :email AND image_id = :image_id");
                    $stmt->bindValue(':email', $email);
                    $stmt->bindValue(':image_id', $imageA['id']);
                    $stmt->execute();
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($row['num_favorites'] > 0) {
                      $is_favorited = true;
                    }

                    // Define the form action
                    $form_action = $is_favorited ? 'unfavorite' : 'favorite';

                    // Button label
                    $button_label = $is_favorited ? 'unfavorite' : 'favorite';
                    ?>
                    <form method="POST">
                      <input type="hidden" name="image_id" value="<?php echo $imageA['id']; ?>">
                      <li>
                        <button type="submit" class="dropdown-item fw-bold" name="<?php echo $form_action ?>">
                          <i class="bi <?php echo $is_favorited ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                          <small><?php echo $button_label ?></small>
                        </button>
                      </li>
                    </form>
                    <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#shareImage<?php echo $imageA['id']; ?>"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
                    <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $imageA['id']; ?>"><i class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
                  </ul>
                  <?php include('share_profile_order_asc.php'); ?>
                </div>
              </div>
            </div>

            <?php include($_SERVER['DOCUMENT_ROOT'] . '/profile/components/delete_image_order_asc.php'); ?>
            <?php include($_SERVER['DOCUMENT_ROOT'] . '/profile/components/card_image_order_asc.php'); ?>

          </div>
        <?php endforeach; ?>
      </div>
    </div>