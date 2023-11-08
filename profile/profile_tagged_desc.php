<?php include('header_profile_desc.php'); ?>
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

  $stmt = $db->prepare("SELECT * FROM images WHERE email = :email AND tags LIKE :tagPattern ORDER BY id DESC LIMIT :limit OFFSET :offset");
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

  $stmt = $db->prepare("SELECT * FROM images WHERE email = :email ORDER BY id DESC LIMIT :limit OFFSET :offset");
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

    <div class="images">
      <?php foreach ($results as $imageD): ?>
        <div class="image-container">
          <div class="position-relative">
            <a class="shadow rounded imagesA" href="../image.php?artworkid=<?php echo $imageD['id']; ?>">
              <img class="lazy-load imagesImg <?php echo ($imageD['type'] === 'nsfw') ? 'nsfw' : ''; ?>" data-src="../thumbnails/<?php echo $imageD['filename']; ?>" alt="<?php echo $imageD['title']; ?>">
            </a> 
            <div class="position-absolute top-0 start-0">
              <div class="dropdown">
                <button class="btn btn-sm btn-dark ms-1 mt-1 rounded-1 opacity-50" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <li><button class="dropdown-item fw-bold" onclick="location.href='../edit_image.php?id=<?php echo $imageD['id']; ?>'" ><i class="bi bi-pencil-fill"></i> edit image</button></li>
                  <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#deleteImage_<?php echo $imageD['id']; ?>"><i class="bi bi-trash-fill"></i> delete</button></li>
                  <?php
                  $is_favorited = false; // Initialize to false

                  // Check if the image is favorited
                  $stmt = $db->prepare("SELECT COUNT(*) AS num_favorites FROM favorites WHERE email = :email AND image_id = :image_id");
                  $stmt->bindValue(':email', $email);
                  $stmt->bindValue(':image_id', $imageD['id']);
                  $stmt->execute();
                  $row = $stmt->fetch(PDO::FETCH_ASSOC);

                  if ($row['num_favorites'] > 0) {
                    $is_favorited = true;
                  }

                  // Define the form action
                  $form_action = $is_favorited ? 'unfavorite' : 'favorite';

                  // Button label
                  $button_label = $is_favorited ? 'unfavorite' : 'favorite';
                  ?>
                  <form method="POST">
                    <input type="hidden" name="image_id" value="<?php echo $imageD['id']; ?>">
                    <li>
                      <button type="submit" class="dropdown-item fw-bold" name="<?php echo $form_action ?>">
                        <i class="bi <?php echo $is_favorited ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                        <small><?php echo $button_label ?></small>
                      </button>
                    </li>
                  </form>
                  <li><button class="dropdown-item fw-bold" onclick="shareImage(<?php echo $imageD['id']; ?>)"><i class="bi bi-share-fill"></i> <small>share</small></button></li>
                  <li><button class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#infoImage_<?php echo $imageD['id']; ?>"><i class="bi bi-info-circle-fill"></i> <small>info</small></button></li>
                </ul>
              </div>
            </div>
          </div>

          <?php include($_SERVER['DOCUMENT_ROOT'] . '/profile/components/delete_tagged_desc.php'); ?>
          <?php include($_SERVER['DOCUMENT_ROOT'] . '/profile/components/card_image_desc.php'); ?>

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
        <a class="btn btn-sm btn-primary fw-bold" href="?by=tagged_newest&tag=<?php echo $tag; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
      <?php endif; ?>

      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=tagged_newest&tag=<?php echo $tag; ?>&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="by=tagged_desc&'. $tag .'&page=' . $i . '">' . $i . '</a>';
          }
        }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=tagged_newest&tag=<?php echo $tag; ?>&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=tagged_newest&tag=<?php echo $tag; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>