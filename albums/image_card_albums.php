    <div class="w-100 px-1">
      <div class="<?php include('../rows_columns/row-cols.php'); echo $rows_columns; ?>">
        <?php
          while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $album_name = $row['album_name'];
            $album_id = $row['id'];

            // Fetch the latest image from the image_album table for the current album
            $query = "SELECT ia.image_id, i.filename FROM image_album AS ia INNER JOIN images AS i ON ia.image_id = i.id WHERE ia.album_id = '$album_id' ORDER BY ia.id DESC LIMIT 1";
            $latest_image = $db->querySingle($query, true);

            // Count images in the current album
            $imageCountStmt = $db->prepare('SELECT COUNT(*) as total_images FROM image_album WHERE album_id = :album_id');
            $imageCountStmt->bindValue(':album_id', $album_id, SQLITE3_INTEGER);
            $imageCountResult = $imageCountStmt->execute()->fetchArray(SQLITE3_ASSOC);
            $totalImagesCount = $imageCountResult['total_images'] ? $imageCountResult['total_images'] : 0;
    
            // Check if the album name is not null before calling urlencode()
            if ($album_name) {
          ?>
            <div class="col">
              <div class="position-relative">
                <?php
                  if ($latest_image) {
                    $thumbnail_path = "../thumbnails/" . $latest_image['filename'];
                ?>
                  <a class="rounded rounded-bottom-0 ratio ratio-1x1" href="../album_images.php?album=<?= urlencode($album_id) ?>"><img data-src="<?= $thumbnail_path ?>" class="rounded rounded-bottom-0 object-fit-cover lazy-load" alt="Album Image"></a>
                <?php
                  } else {
                ?>
                  <a class="rounded rounded-bottom-0 ratio ratio-1x1" href="../album_images.php?album=<?= urlencode($album_id) ?>"><img data-src="../icon/bg.png" class="rounded rounded-bottom-0 object-fit-cover lazy-load"></a>
                <?php
                } ?>
                <?php include('rows_columns/image_counts_prev.php'); ?>
                <div class="position-absolute top-0 start-0">
                  <div class="dropdown">
                    <button class="btn border-0 p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="bi bi-three-dots-vertical text-white link-body-emphasis fs-5" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); text-stroke: 2;"></i>
                    </button>
                    <ul class="dropdown-menu">
                      <form method="POST" onsubmit="return confirm('Are you sure you want to delete this album?');">
                        <input type="hidden" name="delete_album" value="<?= $album_name ?>">
                        <li><button class="dropdown-item fw-bold"><i class="bi bi-trash-fill"></i> Delete</button></li>
                      </form>
                      <li><a class="dropdown-item fw-bold" href="../album_images.php?album=<?= urlencode($album_id) ?>"><i class="bi bi-eye-fill"></i> View</a></li>
                      <li><button type="button" class="dropdown-item fw-bold" onclick="editAlbum('<?= $album_id ?>', '<?= addslashes($album_name) ?>')"><i class="bi bi-pencil-fill"></i> Edit</button></li>
                    </ul>
                  </div>
                </div>
              </div>
              <h6 class="text-center fw-bold bg-body-tertiary shadow p-2 rounded rounded-top-0 m-0"><?= substr($album_name, 0, 13) ?></h6>
            </div>
          <?php }
        } ?>
      </div>
    </div>
    <div class="mt-5"></div>