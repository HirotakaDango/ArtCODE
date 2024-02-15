      <a class="z-3 text-shadow position-absolute top-0 start-0 ms-3 mt-2 m-md-1 btn border-0 fw-bold text-start link-body-emphasis" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/music/?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>">
        <i class="bi bi-chevron-down fs-4" style="-webkit-text-stroke: 3px;"></i>
      </a>
      <a class="z-3 text-shadow position-absolute top-0 end-0 me-3 mt-2 m-md-1 btn border-0 fw-bold text-start link-body-emphasis d-md-none" href="#" data-bs-toggle="modal" data-bs-target="#shareLink">
        <i class="bi bi-share-fill fs-4" style="-webkit-text-stroke: 0.5px;"></i>
      </a>
      <div class="mb-2 mb-md-0"></div>