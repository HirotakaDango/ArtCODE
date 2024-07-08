<?php include('header_profile_pop.php'); ?>
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

// Check for the 'tag' parameter in the URL
if (isset($_GET['tag'])) {
  $tag = $_GET['tag'];

  // Modify your SQL queries to retrieve images with tags that contain the specified tag
  $query = $db->prepare("SELECT COUNT(*) FROM images WHERE email = :email AND tags LIKE :tagPattern");
  $query->bindValue(':email', $email);
  $query->bindValue(':tagPattern', "%$tag%", PDO::PARAM_STR);
  if ($query->execute()) {
    $total = $query->fetchColumn();
  } else {
    // Handle the query execution error
    echo "Error executing the query.";
  }

  $stmt = $db->prepare("SELECT images.*, COUNT(favorites.id) AS favorite_count FROM images LEFT JOIN favorites ON images.id = favorites.image_id WHERE images.email = :email AND images.tags LIKE :tagPattern GROUP BY images.id ORDER BY favorite_count DESC LIMIT :limit OFFSET :offset");
  $stmt->bindValue(':email', $email);
  $stmt->bindValue(':tagPattern', "%$tag%", PDO::PARAM_STR);
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

} else {
  // If the 'tag' parameter is not present, retrieve all images
  $query = $db->prepare("SELECT COUNT(*) FROM images WHERE email = :email");
  $query->bindValue(':email', $email);
  if ($query->execute()) {
    $total = $query->fetchColumn();
  } else {
    // Handle the query execution error
    echo "Error executing the query.";
  }

  $stmt = $db->prepare("SELECT images.*, COUNT(favorites.id) AS favorite_count FROM images LEFT JOIN favorites ON images.id = favorites.image_id WHERE images.email = :email GROUP BY images.id ORDER BY favorite_count DESC LIMIT :limit OFFSET :offset");
  $stmt->bindValue(':email', $email);
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
}

if ($stmt->execute()) {
  // Fetch the results as an associative array
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  // Handle the query execution error
  echo "Error executing the query.";
}
?>

    <?php include('image_card_pro_tagged_pop.php'); ?>
    <?php
      $totalPages = ceil($total / $limit);
      $prevPage = $page - 1;
      $nextPage = $page + 1;
    ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=tagged_popular&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
      <?php endif; ?>

      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=tagged_popular&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?by=tagged_popular&' . $tag . 'page=' . $i . '">' . $i . '</a>';
          }
        }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=tagged_popular&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=tagged_popular&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>