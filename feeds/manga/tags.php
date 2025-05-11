<?php
require_once('../../auth.php');

try {
  // Connect to SQLite
  $db = new PDO('sqlite:../../database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Fetch latest-per-episode tag counts
  $sql = "
    SELECT tags, COUNT(*) AS count FROM (
      SELECT tags,
             episode_name,
             MAX(id) AS latest_image_id
      FROM images
      WHERE artwork_type = 'manga'
      GROUP BY tags, episode_name
    )
    GROUP BY tags
  ";
  $stmt = $db->query($sql);
  $raw  = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Build flat counts
  $counts = [];
  foreach ($raw as $row) {
    foreach (explode(',', $row['tags']) as $tag) {
      $t = trim($tag);
      if ($t === '') continue;
      if (!isset($counts[$t])) {
        $counts[$t] = 0;
      }
      $counts[$t] += (int)$row['count'];
    }
  }

  // Sort and group by first letter
  ksort($counts, SORT_NATURAL | SORT_FLAG_CASE);
  $groupedTags = [];
  foreach ($counts as $tag => $cnt) {
    $firstChar = mb_strtoupper(mb_substr($tag, 0, 1, 'UTF-8'), 'UTF-8');
    if (!isset($groupedTags[$firstChar])) {
      $groupedTags[$firstChar] = [];
    }
    $groupedTags[$firstChar][$tag] = $cnt;
  }
  ksort($groupedTags, SORT_STRING);

} catch (Exception $e) {
  die("Error: " . htmlspecialchars($e->getMessage()));
}

// Build a list of group keys
$allGroups  = array_keys($groupedTags);
// Safely grab the “first” group (or false if none)
$firstGroup = reset($allGroups);

// Determine selected category (default to first, or null if none)
$selected = isset($_GET['category']) && in_array($_GET['category'], $allGroups, true)
          ? $_GET['category']
          : ($firstGroup !== false ? $firstGroup : null);

// Determine page number
$page      = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0
          ? (int)$_GET['page']
          : 1;
$perPage   = 50;
$tagsInCat = $selected !== null
           ? $groupedTags[$selected]
           : [];
$totalTags = count($tagsInCat);
$totalPages = $perPage > 0
            ? (int)ceil($totalTags / $perPage)
            : 1;

// Slice out only the 50 tags for this page
$offset   = ($page - 1) * $perPage;
$tagsPage = array_slice($tagsInCat, $offset, $perPage, true);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tags &mdash; Category <?php echo $selected; ?></title>
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
  </head>
  <body>
    <?php include('../../header.php'); ?>
    <?php include('./header_manga.php'); ?>
    <div class="container my-3">
      <h1 class="mb-4 fw-bold">
        Tags <small class="text-muted">&mdash; Category <?php echo $selected; ?></small>
      </h1>

      <!-- Category selector -->
      <div class="row justify-content-center mb-4 container">
        <?php foreach ($allGroups as $group): ?>
          <div class="col-4 col-md-2 col-sm-5 px-0 mb-2">
            <a class="btn btn-<?php echo $group === $selected ? 'primary' : 'outline-'.include($_SERVER['DOCUMENT_ROOT'].'/appearance/opposite.php'); ?> border-0 fw-medium d-flex flex-column align-items-center w-100" href="?category=<?php echo urlencode($group); ?>">
              <h6 class="fw-medium mb-0">Category</h6>
              <h6 class="fw-bold mb-0"><?php echo $group; ?></h6>
            </a>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Tag list for current page -->
      <?php if ($totalTags > 0): ?>
        <?php foreach ($tagsPage as $tag => $count): ?>
          <div class="my-1 w-100">
            <a href="./?tag=<?php echo urlencode($tag); ?>" class="btn bg-secondary-subtle fw-bold d-flex justify-content-between align-items-center w-100 p-2">
              <span class="text-start">
                <i class="bi bi-tag-fill"></i> <?php echo $tag; ?>
              </span>
              <span class="text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>">
                <?php echo $count; ?>
              </span>
            </a>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No tags found in this category.</p>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <div class="container-fluid mb-5 mt-3">
      <div class="pagination d-flex gap-1 justify-content-center mt-3">
        <?php if (isset($page) && isset($totalPages)): ?>
          <a class="btn btn-sm btn-primary fw-bold <?php if($page <= 1) echo 'd-none'; ?>"
             href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">
            <i class="bi text-stroke bi-chevron-double-left"></i>
          </a>
        <?php endif; ?>

        <?php if (isset($page) && $page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold"
             href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
            <i class="bi text-stroke bi-chevron-left"></i>
          </a>
        <?php endif; ?>

        <?php
        if (isset($page) && isset($totalPages)) {
          $startPage = max($page - 2, 1);
          $endPage   = min($page + 2, $totalPages);
          for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i === $page) {
              echo '<span class="btn btn-sm btn-primary active fw-bold">' . $i . '</span>';
            } else {
              echo '<a class="btn btn-sm btn-primary fw-bold" href="?' .
                   http_build_query(array_merge($_GET, ['page' => $i])) .
                   '">' . $i . '</a>';
            }
          }
        }
        ?>

        <?php if (isset($page) && isset($totalPages) && $page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold"
             href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
            <i class="bi text-stroke bi-chevron-right"></i>
          </a>
        <?php endif; ?>

        <?php if (isset($page) && isset($totalPages)): ?>
          <a class="btn btn-sm btn-primary fw-bold <?php if($totalPages <= 1) echo 'd-none'; ?>"
             href="?<?php echo http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>">
            <i class="bi text-stroke bi-chevron-double-right"></i>
          </a>
        <?php endif; ?>
      </div>
    </div>

    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>