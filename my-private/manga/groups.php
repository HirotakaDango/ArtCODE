<?php
require_once('../../auth.php');

try {
  // Connect to SQLite
  $db = new PDO('sqlite:../../database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Fetch latest-per-episode group counts
  $sql = "
    SELECT `group`, COUNT(*) AS count FROM (
      SELECT `group`,
             episode_name,
             MAX(id) AS latest_image_id
      FROM private_images
      WHERE artwork_type = 'manga'
      GROUP BY `group`, episode_name
    )
    GROUP BY `group`
  ";
  $stmt = $db->query($sql);
  $raw  = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Build flat counts
  $counts = [];
  foreach ($raw as $row) {
    foreach (explode(',', $row['group']) as $g) {
      $g = trim($g);
      if ($g === '') continue;
      if (!isset($counts[$g])) {
        $counts[$g] = 0;
      }
      $counts[$g] += (int)$row['count'];
    }
  }

  // Sort and group by first letter
  ksort($counts, SORT_NATURAL | SORT_FLAG_CASE);
  $groupedGroups = [];
  foreach ($counts as $grp => $cnt) {
    $firstChar = mb_strtoupper(mb_substr($grp, 0, 1, 'UTF-8'), 'UTF-8');
    if (!isset($groupedGroups[$firstChar])) {
      $groupedGroups[$firstChar] = [];
    }
    $groupedGroups[$firstChar][$grp] = $cnt;
  }
  ksort($groupedGroups, SORT_STRING);

} catch (Exception $e) {
  die("Error: " . $e->getMessage());
}

// Build a list of group keys
$allGroups   = array_keys($groupedGroups);
// Safely grab the "first" group (or false if none)
$firstGroup  = reset($allGroups);

// Determine selected category (default to first, or null if none)
$selectedGroup = isset($_GET['category']) && in_array($_GET['category'], $allGroups, true)
               ? $_GET['category']
               : ($firstGroup !== false ? $firstGroup : null);

// Determine page number
$page           = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0
                ? (int)$_GET['page']
                : 1;
$perPage        = 50;
$groupsInCat    = $selectedGroup !== null
                ? $groupedGroups[$selectedGroup]
                : [];
$totalGroups    = count($groupsInCat);
$totalPages     = $perPage > 0 ? (int)ceil($totalGroups / $perPage) : 1;

// Slice out only this page’s groups
$offset         = ($page - 1) * $perPage;
$groupsPage     = array_slice($groupsInCat, $offset, $perPage, true);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Groups<?php if ($selectedGroup) echo " — Category " . $selectedGroup; ?></title>
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
  </head>
  <body>
    <?php include('../../header.php'); ?>
    <?php include('./header_manga.php'); ?>
    <div class="container my-3">
      <h1 class="mb-4 fw-bold">
        Groups
        <?php if ($selectedGroup): ?>
          <small class="text-muted">— Category <?php echo $selectedGroup; ?></small>
        <?php endif; ?>
      </h1>

      <?php if (empty($allGroups)): ?>
        <p>No groups available.</p>
      <?php else: ?>
        <!-- Category selector -->
        <div class="row justify-content-center mb-4 container">
          <?php foreach ($allGroups as $group): ?>
            <div class="col-4 col-md-2 col-sm-5 px-0 mb-2">
              <a class="btn btn-<?php echo $group === $selectedGroup ? 'primary' : 'outline-'.include($_SERVER['DOCUMENT_ROOT'].'/appearance/opposite.php'); ?> border-0 fw-medium d-flex flex-column align-items-center w-100" href="?category=<?php echo urlencode($group); ?>">
                <h6 class="fw-medium mb-0">Category</h6>
                <h6 class="fw-bold mb-0"><?php echo $group; ?></h6>
              </a>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Group list for current page -->
        <?php if ($totalGroups > 0): ?>
          <?php foreach ($groupsPage as $grp => $count): ?>
            <div class="my-1 w-100">
              <a href="./?group=<?php echo urlencode($grp); ?>" class="btn bg-secondary-subtle fw-bold d-flex justify-content-between align-items-center w-100 p-2">
                <span class="text-start">
                  <i class="bi bi-tag-fill"></i> <?php echo $grp; ?>
                </span>
                <span class="text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>">
                  <?php echo $count; ?>
                </span>
              </a>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No groups found in this category.</p>
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