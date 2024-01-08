          <div class="col">
            <div class="card border-0 h-100 shadow-sm rounded-1 position-relative rounded-4 shadow">
              <a class="d-block" href="#" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $row['id']; ?>">
                <img class="lazy-load object-fit-cover rounded-4 <?php echo ($row['type'] === 'nsfw') ? 'nsfw' : ''; ?>" style="width: 100%; height: 300px;" data-src="../thumbnails/<?php echo $row['filename']; ?>" alt="<?php echo $title; ?>">
              </a>
              <div class="position-absolute bottom-0 start-0 m-2">
                <a class="link-light text-decoration-none text-shadow fw-bold" href="../../image.php?artworkid=<?php echo $row['id']; ?>">
                  <?php echo (!is_null($title) && mb_strlen($title, 'UTF-8') > 15) ? mb_substr($title, 0, 15, 'UTF-8') . '...' : $title; ?>
                </a>
                <div class="d-flex justify-content-between align-items-center">
                  <a class="link-light text-decoration-none text-shadow fw-bold small" href="../../artist.php?id=<?= $id ?>"><?php echo (!is_null($artist) && strlen($artist) > 15) ? substr($artist, 0, 15) . '...' : $artist; ?></a>
                </div>
              </div>
            </div>

            <?php include('../contents/search/card_image_search.php'); ?>

          </div>