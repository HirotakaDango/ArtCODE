    <div class="col">
      <div class="card shadow-sm h-100 position-relative rounded-3">
        <a class="shadow position-relative btn p-0" href="/feeds/music/play_favorite.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=newest&album=<?php echo urlencode($row['album']); ?>&id=<?php echo $row['id']; ?>">
          <img class="w-100 object-fit-cover rounded" height="200" src="/feeds/music/covers/<?php echo $row['cover']; ?>">
          <i class="bi bi-play-fill position-absolute start-50 top-50 display-1 translate-middle"></i>
        </a>
        <div class="p-2 position-absolute bottom-0 start-0">
          <h5 class="card-text fw-bold text-shadow">
            <?php echo (!is_null($row['title']) && strlen($row['title']) > 15) ? substr($row['title'], 0, 15) . '...' : $row['title']; ?>
          </h5>
          <p class="card-text small fw-bold text-shadow">
            <small>by <a class="text-decoration-none text-white" href="artist.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&id=<?php echo $row['userid']; ?>">
              <?php echo (!is_null($row['artist']) && strlen($row['artist']) > 15) ? substr($row['artist'], 0, 15) . '...' : $row['artist']; ?>
            </a></small>
          </p>
        </div>
      </div>
    </div>
