<?php include('header_artist_pop.php'); ?>
<?php
// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Check for the 'tag' parameter in the URL
if (isset($_GET['tag'])) {
  $tag = $_GET['tag'];

  // Modify your SQL queries to retrieve images with tags that contain the specified tag
  $query = $db->prepare("SELECT COUNT(*) FROM images JOIN users ON images.email = users.email WHERE users.id = :id AND images.tags LIKE :tagPattern");
  $query->bindParam(':id', $id);
  $query->bindValue(':tagPattern', "%$tag%", PDO::PARAM_STR);
} else {
  // If the 'tag' parameter is not present, retrieve all images
  $query = $db->prepare("SELECT COUNT(*) FROM images JOIN users ON images.email = users.email WHERE users.id = :id");
  $query->bindParam(':id', $id);
}

if ($query->execute()) {
  $total = $query->fetchColumn();
} else {
  // Handle the query execution error
  echo "Error executing the query.";
}

// Set the limit of images per page
$limit = 12;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

if (isset($_GET['tag'])) {
  $stmt = $db->prepare("SELECT images.*, COUNT(favorites.id) AS favorite_count FROM images JOIN users ON images.email = users.email LEFT JOIN favorites ON images.id = favorites.image_id WHERE users.id = :id AND images.tags LIKE :tagPattern GROUP BY images.id ORDER BY favorite_count DESC LIMIT :limit OFFSET :offset");
  $stmt->bindParam(':id', $id);
  $stmt->bindValue(':tagPattern', "%$tag%", PDO::PARAM_STR);
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
} else {
  $stmt = $db->prepare("SELECT images.*, COUNT(favorites.id) AS favorite_count FROM images JOIN users ON images.email = users.email LEFT JOIN favorites ON images.id = favorites.image_id WHERE users.id = :id GROUP BY images.id ORDER BY favorite_count DESC LIMIT :limit OFFSET :offset");
  $stmt->bindParam(':id', $id);
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
}

if ($stmt->execute()) {
  // Fetch the results as an associative array
  $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  // Handle the query execution error
  echo "Error executing the query.";
}
?>

    <?php include('image_card_art_pop.php'); ?>
    <?php
      $totalPages = ceil($total / $limit);
      $prevPage = $page - 1;
      $nextPage = $page + 1;
    ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?id=<?php echo $id; ?>&by=tagged_popular&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
      <?php endif; ?>

      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?id=<?php echo $id; ?>&by=tagged_popular&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
      <?php endif; ?>

      <?php
        $tag = isset($_GET['tag']) ? 'tag=' . $_GET['tag'] . '&' : '';

        // Calculate the range of page numbers to display
        $startPage = max($page - 2, 1);
        $endPage = min($page + 2, $totalPages);

        // Display page numbers within the range
        for ($i = $startPage; $i <= $endPage; $i++) {
          if ($i === $page) {
            echo '<span class="btn btn-sm btn-primary active fw-bold">' . $i . '</span>';
          } else {
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?id=' . $id . 'by=tagged_popular&' . $tag . 'page=' . $i . '">' . $i . '</a>';
          }
        }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?id=<?php echo $id; ?>&by=tagged_popular&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?id=<?php echo $id; ?>&by=tagged_popular&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>