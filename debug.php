<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project File List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
  </head>
  <body>
    <?php
    $rootDir = __DIR__;
    $excludeDirs = ['images', 'background_pictures', 'profile_pictures', 'thumbnails'];
    $excludeFiles = ['management.php'];
    function getFilesRecursive($dir, $baseUrl = '') {
      $files = [];
      $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
      );
      foreach ($iterator as $file) {
        if ($file->isFile()) {
          $ext = strtolower($file->getExtension());
          $filename = $file->getFilename();
          if (in_array($filename, $GLOBALS['excludeFiles'])) continue;
          if (in_array($ext, ['php', 'html', 'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico'])) {
            $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen($dir)));
            $relativePath = ltrim($relativePath, '/');
            $isExcluded = false;
            foreach ($GLOBALS['excludeDirs'] as $exDir) {
              if (strpos($relativePath, $exDir . '/') === 0) { $isExcluded = true; break; }
            }
            if (!$isExcluded) $files[] = $baseUrl . '/' . $relativePath;
          }
        }
      }
      return $files;
    }
    $fileList = getFilesRecursive($rootDir, '');
    $fileList = array_values(array_unique(array_filter($fileList, fn($f) => strpos($f, '/') === 0)));
    array_unshift($fileList, '/');
    if (!in_array('/icon/favicon.png', $fileList)) $fileList[] = '/icon/favicon.png';
    $totalFiles = count($fileList);
    ?>
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
          <h1 class="mb-3 fs-3 fw-bold">
            <i class="bi bi-folder2-open"></i> Project Files
          </h1>
          <div class="mb-4 text-secondary fw-semibold">
            <i class="bi bi-collection"></i>
            Total Files: <span class="fw-bold text-info"><?php echo $totalFiles; ?></span>
          </div>
          <ul class="list-group mb-4">
            <?php foreach($fileList as $file): ?>
              <li class="list-group-item bg-transparent border-secondary text-break">
                <i class="bi bi-file-earmark"></i>
                <?php echo htmlspecialchars($file, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="text-center text-secondary small mt-4">
            File list generated on
            <span class="fw-semibold text-info"><?php echo date("Y-m-d H:i"); ?></span>
            &mdash; <i class="bi bi-github"></i> <span class="fw-semibold">HirotakaDango</span>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>