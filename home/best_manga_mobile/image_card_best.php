    <div class="w-100 px-1 mt-2 mb-5">
      <h5 class="fw-bold px-1">Popular Manga</h5>
      <h6 class="fw-bold small px-1 mb-3">
        These manga are displayed based on their view counts from <?php $timeManga = isset($_GET['time']) ? $_GET['time'] : 'day'; echo $timeManga === 'alltime' ? 'all time' : "this $timeManga"; ?>. The more views a manga has, the higher its ranking in this list.
      </h6>
      <div class="<?php include('../rows_columns/row-cols.php'); echo $rows_columns; ?>">
        <?php 
        $jManga = 0; // Initialize the counter variable
        while ($imageUManga = $resultManga->fetchArray(SQLITE3_ASSOC)): 
          // Determine badge color
          $badgeClassManga = '';
          switch ($jManga) {
            case 0:
              $badgeClassManga = 'gold text-white shadow'; // Gold
              break;
            case 1:
              $badgeClassManga = 'silver text-white shadow'; // Silver
              break;
            case 2:
              $badgeClassManga = 'bronze text-white shadow'; // Bronze
              break;
            default:
              $badgeClassManga = 'nothing text-white shadow'; // Default color for others
          }
        ?>
          <div class="col position-relative">
            <div class="position-relative ratio-cover">
              <a class="rounded" href="/manga/title.php?title=<?= urlencode($imageUManga['episode_name']); ?>&uid=<?= $imageUManga['user_id']; ?>">
                <img class="rounded shadow object-fit-cover lazy-load rounded-bottom-0 <?php echo ($imageUManga['type'] === 'nsfw') ? 'nsfw' : ''; ?>" data-src="/thumbnails/<?php echo $imageUManga['filename']; ?>" alt="<?php echo $imageUManga['episode_name']; ?>">
              </a>
              <span class="position-absolute top-0 start-0 m-2 badge <?php echo $badgeClassManga; ?>" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4);">No. <?php echo $jManga + 1; ?></span>
            </div>
            <div class="d-flex align-items-center p-2 bg-light rounded-bottom mx-0 text-dark">
              <img class="rounded-circle object-fit-cover border border-1" width="40" height="40" src="/<?php echo !empty($imageUManga['pic']) ? $imageUManga['pic'] : 'icon/profile.svg'; ?>" alt="Profile Picture" style="margin-top: -2px;">
              <div class="ms-2">
                <div class="fw-bold text-truncate" style="max-width: 100px;"><?php echo $imageUManga['episode_name']; ?></div>
                <a class="fw-medium text-decoration-none text-dark link-body-emphasis small text-truncate" style="max-width: 100px;" href="#" type="button" data-bs-toggle="modal" data-bs-target="#userModalBest-<?php echo $imageUManga['user_id']; ?>"><?php echo $imageUManga['artist']; ?></a>
              </div>
            </div>
            <div class="modal fade" id="userModalBest-<?php echo $imageUManga['user_id']; ?>" tabindex="-1" aria-labelledby="userModalLabelBest" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-transparent border-0">
                  <div class="modal-body position-relative">
                    <a class="position-absolute top-0 end-0 m-4" href="/artist.php?id=<?php echo urlencode($imageUManga['user_id']); ?>" target="_blank">
                      <i class="bi bi-box-arrow-up-right link-body-emphasis text-white" style="-webkit-text-stroke: 1px; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); text-stroke: 2;"></i>
                    </a>
                    <iframe src="/rows_columns/user_preview.php?id=<?php echo urlencode($imageUManga['user_id']); ?>" class="rounded-4 p-0 shadow" width="100%" height="300" style="border: none;"></iframe>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php 
            $jManga++; // Increment the counter after each image
          endwhile; 
          ?>
      </div>
    </div>