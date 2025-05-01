<?php
require_once('../../auth.php');

try {
  // Connect to SQLite
  $db = new PDO('sqlite:../../database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Fetch latest-per-episode artist counts
  $sql = "
    SELECT
      users.id     AS userid,
      users.artist AS artist,
      COUNT(*)      AS count
    FROM (
      SELECT
        email,
        episode_name,
        MAX(id) AS latest_image_id
      FROM images
      WHERE artwork_type = 'manga'
      GROUP BY email, episode_name
    ) AS latest_images
    JOIN users
      ON latest_images.email = users.email
    GROUP BY users.artist, users.id
  ";
  $stmt = $db->query($sql);
  $raw  = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Build flat counts
  $counts = [];
  foreach ($raw as $row) {
    $artist = trim($row['artist']);
    if ($artist === '') continue;
    $counts[$artist] = [
      'count'  => (int)$row['count'],
      'userid' => $row['userid']
    ];
  }

  // Sort and group by first letter
  ksort($counts, SORT_NATURAL | SORT_FLAG_CASE);
  $groupedArtists = [];
  foreach ($counts as $artist => $data) {
    $firstChar = mb_strtoupper(mb_substr($artist, 0, 1, 'UTF-8'), 'UTF-8');
    if (!isset($groupedArtists[$firstChar])) {
      $groupedArtists[$firstChar] = [];
    }
    $groupedArtists[$firstChar][$artist] = $data;
  }
  ksort($groupedArtists, SORT_STRING);

} catch (Exception $e) {
  die("Error: " . htmlspecialchars($e->getMessage()));
}

// Determine selected category (default to first)
$allGroups = array_keys($groupedArtists);
$selected   = isset($_GET['category']) && in_array($_GET['category'], $allGroups, true)
              ? $_GET['category']
              : $allGroups[0];

// Determine page number
$page       = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0
              ? (int)$_GET['page']
              : 1;
$perPage    = 50;
$artistsInCat  = $groupedArtists[$selected] ?? [];
$totalArtists  = count($artistsInCat);
$totalPages    = (int)ceil($totalArtists / $perPage);

// Slice out only the 50 artists for this page
$offset      = ($page - 1) * $perPage;
$artistsPage = array_slice($artistsInCat, $offset, $perPage, true);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artists &mdash; Category <?php echo $selected; ?></title>
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
  </head>
  <body>
    <?php include('../../header.php'); ?>
    <div class="container my-3">
      <h1 class="mb-4 fw-bold">
        Artists <small class="text-muted">&mdash; Category <?php echo $selected; ?></small>
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

      <!-- Artist list for current page -->
      <?php if ($totalArtists > 0): ?>
        <?php foreach ($artistsPage as $artist => $data): ?>
          <div class="my-1 w-100">
            <a href="./?uid=<?php echo urlencode($data['userid']); ?>" class="btn bg-secondary-subtle fw-bold d-flex justify-content-between align-items-center w-100 p-2">
              <span class="text-start">
                <i class="bi bi-person-fill"></i> <?php echo $artist; ?>
              </span>
              <span class="text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>">
                <?php echo $data['count']; ?>
              </span>
            </a>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No artists found in this category.</p>
      <?php endif; ?>
    </div>

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
        ?>
          <?php if ($i === $page): ?>
            <span class="btn btn-sm btn-primary active fw-bold"><?php echo $i; ?></span>
          <?php else: ?>
            <a class="btn btn-sm btn-primary fw-bold" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
              <?php echo $i; ?>
            </a>
          <?php endif; ?>
        <?php endfor; ?>

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

    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>