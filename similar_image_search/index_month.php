<?php
require_once('../auth.php');

$email = $_SESSION['email'];

// Get the start and end of the current month
$startOfMonth = date('Y-m-01'); // First day of the month
$endOfMonth = date('Y-m-t');   // Last day of the month

if (!empty($imageUrl)) {
  $imageBase64 = urlToBase64($_SERVER['DOCUMENT_ROOT'] . parse_url($imageUrl, PHP_URL_PATH));

  if ($imageBase64 === false) {
    $displayMessage = 'Sorry, image not found. Make sure to use the first image parent!';
  } else {
    // Retrieve images ordered by monthly views
    $stmt = $db->prepare("
      SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(SUM(daily.views), 0) AS views
      FROM images
      JOIN users ON images.email = users.email
      LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfMonth AND :endOfMonth
      GROUP BY images.id, users.artist, users.pic, users.id
      ORDER BY views DESC, images.id DESC
    ");

    // Bind parameters and execute query
    $stmt->bindValue(':startOfMonth', $startOfMonth, PDO::PARAM_STR);
    $stmt->bindValue(':endOfMonth', $endOfMonth, PDO::PARAM_STR);

    try {
      $stmt->execute();
      $allImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo 'Query failed: ' . $e->getMessage();
      exit;
    }

    // Compare and filter images while maintaining order
    $resultArray = [];
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

    // Get user's numpage setting
    $queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
    $queryNum->bindValue(':email', $email, PDO::PARAM_STR);
    if ($queryNum->execute()) {
      $user = $queryNum->fetch(PDO::FETCH_ASSOC);
      $numpage = isset($user['numpage']) ? (int)$user['numpage'] : 50;
    } else {
      $numpage = 50;
    }
    
    // Pagination
    $limit = $numpage;
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
            <input type="text" class="form-control border-0 focus-ring focus-ring-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" name="image" value="<?php echo htmlspecialchars($imageUrl); ?>" placeholder="<?php echo $displayMessage; ?>" aria-label="Image URL">
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