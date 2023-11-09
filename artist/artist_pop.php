<?php include('header_artist_pop.php'); ?>
<?php
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindParam(':email', $email, PDO::PARAM_STR);
$queryNum->execute();
$user = $queryNum->fetch(PDO::FETCH_ASSOC);

$numpage = $user['numpage'];

// Set the limit of images per page
$limit = empty($numpage) ? 50 : $numpage;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Execute the initial query to get images with favorite counts
$query = $db->prepare('SELECT images.id, images.tags, images.filename, images.title, images.imgdesc, images.type, images.view_count, COUNT(favorites.id) AS favorite_count FROM images JOIN users ON images.email = users.email LEFT JOIN favorites ON images.id = favorites.image_id WHERE users.id = :id GROUP BY images.id ORDER BY favorite_count DESC');
$query->bindParam(':id', $id);
$query->execute();
$imagesWithFavorites = $query->fetchAll(PDO::FETCH_ASSOC);

// Get the total number of images for the selected user from the images with favorites result
$total = count($imagesWithFavorites);

// Get a subset of images based on the offset and limit
$images = array_slice($imagesWithFavorites, $offset, $limit);
?>

    <div class="images">
      <?php foreach ($images as $imageP): ?>
        <div class="image-container">
          <div class="position-relative">
            <a class="shadow rounded imagesA" href="../image.php?artworkid=<?php echo $imageP['id']; ?>">
              <img class="lazy-load imagesImg <?php echo ($imageP['type'] === 'nsfw') ? 'nsfw' : ''; ?>" data-src="../thumbnails/<?php echo $imageP['filename']; ?>" alt="<?php echo $imageP['title']; ?>">
            </a> 
            <div class="position-absolute top-0 start-0">
              <div class="dropdown">
                <button class="btn btn-sm btn-dark ms-1 mt-1 rounded-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <?php
                    $is_favorited = $db->query("SELECT COUNT(*) FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = {$imageP['id']}")->fetchColumn();
                    if ($is_favorited) {
                  ?>
                    <form method="POST">
                      <input type="hidden" name="image_id" value="<?php echo $imageP['id']; ?>">
                      <li><button type="submit" class="dropdown-item fw-bold" name="unfavorite"><i class="bi bi-heart-fill"></i> <small>unfavorite</small></button></li>
                    </form>
                  <?php } else { ?>
                    <form method="POST">
                      <input type="hidden" name="image_id" value="<?php echo $imageP['id']; ?>">
                      <li><button type="submit" class="dropdown-item fw-bold" name="favorite"><i class="bi bi-heart"></i> <small>favorite</small></button></li>
                    </form>
                  <?php } ?>
                  <li><button class="dropdown-item fw-bold" onclick="shareImage(<?php echo $imageP['id']; ?>)"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
                  <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $imageP['id']; ?>"><i class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
                </ul>
                
                <?php include($_SERVER['DOCUMENT_ROOT'] . '/artist/components/card_image_pop.php'); ?>

              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?> 
    </div>
    <?php
      $totalPages = ceil($total / $limit);
      $prevPage = $page - 1;
      $nextPage = $page + 1;
    ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?id=<?php echo $id; ?>&by=popular&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
        <a class="btn btn-sm btn-primary fw-bold" href="?id=<?php echo $id; ?>&by=popular&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
      <?php endif; ?>

      <?php
        // Calculate the range of page numbers to display
        $startPage = max($page - 2, 1);
        $endPage = min($page + 2, $totalPages);

        // Display page numbers within the range
        for ($i = $startPage; $i <= $endPage; $i++) {
          if ($i === $page) {
            echo '<span class="btn btn-sm btn-primary active fw-bold">' . $i . '</span>';
          } else {
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?id=' . $id . '&by=popular&page=' . $i . '">' . $i . '</a>';
          }
        }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?id=<?php echo $id; ?>&by=popular&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
        <a class="btn btn-sm btn-primary fw-bold" href="?id=<?php echo $id; ?>&by=popular&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>