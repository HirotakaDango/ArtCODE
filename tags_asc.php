<?php
// Construct the search condition with at least 80% match
$searchCondition = '';
if (!empty($searchQuery)) {
  $searchQuery = trim($searchQuery);
  $searchWords = explode(' ', $searchQuery);
  $searchConditions = [];
  foreach ($searchWords as $word) {
    $searchConditions[] = "tags LIKE '%" . $db->escapeString($word) . "%'";
  }
  $searchCondition = '(' . implode(' AND ', $searchConditions) . ')';
}

// Retrieve the count of images for each tag matching the search condition
$query = "SELECT tags, COUNT(*) as count FROM images";
if (!empty($searchCondition)) {
  $query .= " WHERE $searchCondition";
}
$query .= " GROUP BY tags ORDER BY tags ASC";

$result = $db->query($query);

// Store the tag counts as an associative array
$tagCounts = [];
while ($row = $result->fetchArray()) {
  $tagList = explode(',', $row['tags']);
  foreach ($tagList as $tag) {
    $trimmedTag = trim($tag);
    if (!isset($tagCounts[$trimmedTag])) {
      $tagCounts[$trimmedTag] = 0;
    }
    $tagCounts[$trimmedTag] += $row['count'];
  }
}

// Group the tags by the first character
$groupedTags = [];
foreach ($tagCounts as $tag => $count) {
  $firstChar = strtoupper(mb_substr($tag, 0, 1));
  $groupedTags[$firstChar][$tag] = $count;
}
?>

    <div class="container-fluid mt-2">
      <div class="container-fluid">
        <div class="row justify-content-center">
          <?php foreach ($groupedTags as $group => $tags): ?>
            <div class="col-4 col-md-2 col-sm-5 px-0">
              <a class="btn btn-outline-dark border-0 fw-medium d-flex flex-column align-items-center" href="#category-<?php echo $group; ?>"><h6 class="fw-medium">Category</h6> <h6 class="fw-bold"><?php echo $group; ?></h6></a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php foreach ($groupedTags as $group => $tags): ?>
        <div id="category-<?php echo $group; ?>" class="category-section pt-5">
          <h5 class="fw-bold text-start">Category <?php echo $group; ?></h5>
          <div class="row">
            <?php foreach ($tags as $tag => $count): ?>
              <?php
                // Check if the tag has any associated images
                $stmt = $db->prepare("SELECT * FROM images WHERE tags LIKE ? ORDER BY id DESC LIMIT 1");
                $stmt->bindValue(1, '%' . $tag . '%');
                $imageResult = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
                if ($imageResult):
              ?>
              <div class="col-md-2 col-sm-5 px-0">
                <a href="tagged_images.php?tag=<?php echo str_replace('%27', "'", urlencode($tag)); ?>" class="m-1 d-block text-decoration-none">
                  <div class="card rounded-4 border-0 shadow text-bg-dark ratio ratio-1x1">
                    <img data-src="thumbnails/<?php echo $imageResult['filename']; ?>" alt="<?php echo $imageResult['title']; ?>" class="lazy-load card-img object-fit-cover rounded-4 w-100 h-100">
                    <div class="card-img-overlay d-flex align-items-center justify-content-center">
                      <span class="fw-bold text-center" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);">
                        <?php echo $tag . ' (' . $count . ')'; ?>
                      </span>
                    </div>
                  </div>
                </a>
              </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="mt-5"></div>