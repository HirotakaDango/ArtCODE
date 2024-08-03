<?php
if (!empty($imageUrl)) {
  $imageBase64 = urlToBase64($_SERVER['DOCUMENT_ROOT'] . parse_url($imageUrl, PHP_URL_PATH));

  if ($imageBase64 === false) {
    $displayMessage = 'Sorry, image not found. Make sure to use the first image parent!';
  } else {
    // Retrieve all images ordered by id DESC
    $query = "SELECT * FROM images ORDER BY view_count DESC";
    $statement = $db->prepare($query);

    try {
      $statement->execute();
      $allImages = $statement->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo 'Query failed: ' . $e->getMessage();
      exit;
    }

    // Compare and filter images while maintaining order
    foreach ($allImages as $image) {
      $storedImageUrl = $_SERVER['DOCUMENT_ROOT'] . '/images/' . $image['filename'];
      $storedImageBase64 = urlToBase64($storedImageUrl);

      if ($storedImageBase64 !== false) { 
        $similarity = calculateSimilarity($storedImageBase64, $imageBase64);
        
        if ($similarity >= 0.9) { 
          $image['similarity'] = $similarity;
          $resultArray[] = $image;
        }
      }
    }

    // Pagination
    $limit = 12;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Slice the array to get the items for the current page
    $pagedResults = array_slice($resultArray, $offset, $limit);

    $totalPages = ceil(count($resultArray) / $limit);
    $totalImagesFound = count($resultArray);
  }
} else {
  $displayMessage = 'You must enter the URL of the image!';
}
?>
    <?php if (!empty($displayMessage)): ?>
      <div class="container d-flex justify-content-center align-items-center" style="height: 85vh;">
        <form method="get" class="card bg-body-tertiary shadow border-0 rounded-4 p-4 w-100">
          <div class="input-group">
            <input type="text" class="form-control border-0 focus-ring focus-ring-light" name="image" value="<?php echo htmlspecialchars($imageUrl); ?>" placeholder="<?php echo $displayMessage; ?>" aria-label="Image URL">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search" style="-webkit-text-stroke: 1px;"></i></button>
          </div>
        </form>
      </div>
    <?php else: ?>
      <h6 class="badge bg-primary ms-2"><?php echo $totalImagesFound; ?> images found</h6>
      <?php include('image_card_similar.php'); ?>
      <div class="pagination d-flex gap-1 justify-content-center mt-3">
        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'newest'; ?>&image=<?php echo urlencode($imageUrl); ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
        <?php endif; ?>

        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'newest'; ?>&image=<?php echo urlencode($imageUrl); ?>&page=<?php echo $page - 1; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
        <?php endif; ?>

        <?php
        $startPage = max($page - 2, 1);
        $endPage = min($page + 2, $totalPages);
        
        for ($i = $startPage; $i <= $endPage; $i++) {
          if ($i === $page) {
            echo '<span class="btn btn-sm btn-primary active fw-bold">' . $i . '</span>';
          } else {
            $by = isset($_GET['by']) ? urlencode($_GET['by']) : 'newest';
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?by=' . $by . '&image=' . urlencode($imageUrl) . '&page=' . $i . '">' . $i . '</a>';
          }
        }
        ?>

        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'newest'; ?>&image=<?php echo urlencode($imageUrl); ?>&page=<?php echo $page + 1; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'newest'; ?>&image=<?php echo urlencode($imageUrl); ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>