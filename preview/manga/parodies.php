<?php
try {
  // Connect to SQLite
  $db = new PDO('sqlite:../../database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Fetch latest-per-episode parody counts
  $sql = "
    SELECT parodies, COUNT(*) AS count FROM (
      SELECT parodies,
             episode_name,
             MAX(id) AS latest_image_id
      FROM images
      WHERE artwork_type = 'manga'
      GROUP BY parodies, episode_name
    )
    GROUP BY parodies
  ";
  $stmt = $db->query($sql);
  $raw  = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Build flat counts
  $counts = [];
  foreach ($raw as $row) {
    foreach (explode(',', $row['parodies']) as $parody) {
      $p = trim($parody);
      if ($p === '') continue;
      if (!isset($counts[$p])) {
        $counts[$p] = 0;
      }
      $counts[$p] += (int)$row['count'];
    }
  }

  // Sort and group by first letter
  ksort($counts, SORT_NATURAL | SORT_FLAG_CASE);
  $groupedParodies = [];
  foreach ($counts as $parody => $cnt) {
    $firstChar = mb_strtoupper(mb_substr($parody, 0, 1, 'UTF-8'), 'UTF-8');
    if (!isset($groupedParodies[$firstChar])) {
      $groupedParodies[$firstChar] = [];
    }
    $groupedParodies[$firstChar][$parody] = $cnt;
  }
  ksort($groupedParodies, SORT_STRING);

} catch (Exception $e) {
  die("Error: " . htmlspecialchars($e->getMessage()));
}

// Build a list of group keys
$allGroups  = array_keys($groupedParodies);
// Safely grab the "first" group (or null if none)
$firstGroup = reset($allGroups);

// Determine selected category (default to first, or null if none)
$selectedGroup = isset($_GET['category']) && in_array($_GET['category'], $allGroups, true)
               ? $_GET['category']
               : ($firstGroup !== false ? $firstGroup : null);

// Determine page number
$page          = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0
               ? (int)$_GET['page']
               : 1;
$perPage       = 50;
$parodiesInCat = $selectedGroup !== null
               ? $groupedParodies[$selectedGroup]
               : [];
$totalParodies = count($parodiesInCat);
$totalPages    = $perPage > 0 ? (int)ceil($totalParodies / $perPage) : 1;

// Slice out only this page’s parodies
$offset       = ($page - 1) * $perPage;
$parodiesPage = array_slice($parodiesInCat, $offset, $perPage, true);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parodies<?php if ($selectedGroup) echo " — Category " . $selectedGroup; ?></title>
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
  </head>
  <body>
    <?php include('../header_preview.php'); ?>
    <?php include('./header_manga.php'); ?>
    <div class="container my-3">
      <h1 class="mb-4 fw-bold">
        Parodies
        <?php if ($selectedGroup): ?>
          <small class="text-muted">— Category <?php echo $selectedGroup; ?></small>
        <?php endif; ?>
      </h1>

      <?php if (empty($allGroups)): ?>
        <p>No parodies available.</p>
      <?php else: ?>
        <!-- Category selector -->
        <div class="row justify-content-center mb-4 container">
          <?php foreach ($allGroups as $group): ?>
            <div class="col-4 col-md-2 col-sm-5 px-0 mb-2">
              <a class="btn btn-<?php echo $group === $selected ? 'primary' : ''; ?> border-0 fw-medium d-flex flex-column align-items-center w-100" href="?category=<?php echo urlencode($group); ?>">
                <h6 class="fw-medium mb-0">Category</h6>
                <h6 class="fw-bold mb-0"><?php echo $group; ?></h6>
              </a>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Parody list for current page -->
        <?php if ($totalParodies > 0): ?>
          <?php foreach ($parodiesPage as $parody => $count): ?>
            <div class="my-1 w-100">
              <a href="./?parody=<?php echo urlencode($parody); ?>" class="btn bg-secondary-subtle fw-bold d-flex justify-content-between align-items-center w-100 p-2">
                <span class="text-start">
                  <i class="bi bi-tag-fill"></i> <?php echo $parody; ?>
                </span>
                <span class="text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>">
                  <?php echo $count; ?>
                </span>
              </a>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No parodies found in this category.</p>
        <?php endif; ?>

        <!-- Pagination -->
        <div class="container-fluid mb-5 mt-3">
          <div class="pagination d-flex gap-1 justify-content-center mt-3">
            <?php if ($page > 1): ?>
              <a class="btn btn-sm btn-primary fw-bold" href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">
                <i class="bi text-stroke bi-chevron-double-left"></i>
              </a>
              <a class="btn btn-sm btn-primary fw-bold" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                <i class="bi text-stroke bi-chevron-left"></i>
              </a>
            <?php endif; ?>

            <?php
              $startPage = max($page - 2, 1);
              $endPage   = min($page + 2, $totalPages);
              for ($i = $startPage; $i <= $endPage; $i++):
                if ($i === $page):
            ?>
              <span class="btn btn-sm btn-primary active fw-bold"><?php echo $i; ?></span>
            <?php else: ?>
              <a class="btn btn-sm btn-primary fw-bold"
                 href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                <?php echo $i; ?>
              </a>
            <?php
                endif;
              endfor;
            ?>

            <?php if ($page < $totalPages): ?>
              <a class="btn btn-sm btn-primary fw-bold" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                <i class="bi text-stroke bi-chevron-right"></i>
              </a>
              <a class="btn btn-sm btn-primary fw-bold" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>">
                <i class="bi text-stroke bi-chevron-double-right"></i>
              </a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>