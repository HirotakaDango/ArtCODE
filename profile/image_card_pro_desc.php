    <div class="w-100 px-1">
      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-5 g-1">
        <?php foreach ($results as $imageD): ?>
          <div class="col">
            <div class="position-relative">
              <a class="rounded ratio ratio-1x1" href="../image.php?artworkid=<?php echo $imageD['id']; ?>">
                <img class="rounded shadow object-fit-cover lazy-load <?php echo ($imageD['type'] === 'nsfw') ? 'nsfw' : ''; ?>" data-src="../thumbnails/<?php echo $imageD['filename']; ?>" alt="<?php echo $imageD['title']; ?>">
              </a> 
              <div class="position-absolute top-0 start-0">
                <div class="dropdown">
                  <button class="btn btn-sm btn-dark ms-1 mt-1 rounded-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                  </button>
                  <ul class="dropdown-menu">
                    <li><button class="dropdown-item fw-bold" onclick="location.href='../edit_image.php?id=<?php echo $imageD['id']; ?>'" ><i class="bi bi-pencil-fill"></i> edit image</button></li>
                    <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#deleteImage_<?php echo $imageD['id']; ?>"><i class="bi bi-trash-fill"></i> delete</button></li>
                    <?php
                    $is_favorited = false; // Initialize to false

                    // Check if the image is favorited
                    $stmt = $db->prepare("SELECT COUNT(*) AS num_favorites FROM favorites WHERE email = :email AND image_id = :image_id");
                    $stmt->bindValue(':email', $email);
                    $stmt->bindValue(':image_id', $imageD['id']);
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
                      <input type="hidden" name="image_id" value="<?php echo $imageD['id']; ?>">
                      <li>
                        <button type="submit" class="dropdown-item fw-bold" name="<?php echo $form_action ?>">
                          <i class="bi <?php echo $is_favorited ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                          <small><?php echo $button_label ?></small>
                        </button>
                      </li>
                    </form>
                    <li><button class="dropdown-item fw-bold" onclick="shareImage(<?php echo $imageD['id']; ?>)"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
                    <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $imageD['id']; ?>"><i class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
                  </ul>
                </div>
              </div>
            </div>

            <?php include($_SERVER['DOCUMENT_ROOT'] . '/profile/components/delete_tagged_desc.php'); ?>
            <?php include($_SERVER['DOCUMENT_ROOT'] . '/profile/components/card_image_desc.php'); ?>

          </div>
        <?php endforeach; ?>
      </div>
    </div>