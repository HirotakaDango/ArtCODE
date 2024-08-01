    <div class="btn-group w-100 gap-2 my-3 container d-flex justify-content-center">
      <a class="btn bg-body-tertiary p-4 fw-bold w-50 rounded-4 shadow <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'upload/') !== false) echo 'opacity-75'; ?>" href="/upload/">Upload</a>
      <a class="btn bg-body-tertiary p-4 fw-bold w-50 rounded-4 shadow <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'import/') !== false) echo 'opacity-75'; ?>" href="/import/">Import</a>
      <a class="btn bg-body-tertiary p-4 fw-bold w-50 rounded-4 shadow <?php if(basename($_SERVER['PHP_SELF']) == 'episode.php') echo 'opacity-75' ?>" href="/upload/episode.php">Episode</a>
    </div> 