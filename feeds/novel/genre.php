<?php
require_once('../../auth.php');

// Database connection using PDO
try {
  $db = new PDO('sqlite:../../database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}

$email = $_SESSION['email'];

// Pagination variables
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get total number of records
try {
  $totalStmt = $db->prepare("SELECT COUNT(*) FROM novel WHERE email = :email");
  $totalStmt->bindValue(':email', $email);
  $totalStmt->execute();
  $total = $totalStmt->fetchColumn();
} catch (PDOException $e) {
  die("Error getting total records: " . $e->getMessage());
}

// Specify the tag you want to filter by
$tag = isset($_GET['tag']) ? $_GET['tag'] : '';

// Get the total number of records with the given tag and current email
try {
  $tagStmt = $db->prepare('SELECT COUNT(*) FROM novel WHERE tags LIKE :tag AND email = :email');
  $tagStmt->bindValue(':tag', '%' . $tag . '%');
  $tagStmt->bindValue(':email', $email);
  $tagStmt->execute();
  $totalWithTag = $tagStmt->fetchColumn();
} catch (PDOException $e) {
  die("Error getting total records with tag: " . $e->getMessage());
}

// Get all of the images from the database, joined with users table, filtered by tag
try {
  $result = $db->prepare("SELECT novel.*, users.id AS user_id, users.email, users.artist FROM novel JOIN users ON novel.email = users.email WHERE novel.email = :email AND novel.tags LIKE :tag ORDER BY novel.id DESC LIMIT :limit OFFSET :offset");
  $result->bindValue(':email', $email);
  $result->bindValue(':tag', '%' . $tag . '%');
  $result->bindValue(':limit', $limit, PDO::PARAM_INT);
  $result->bindValue(':offset', $offset, PDO::PARAM_INT);
  $result->execute();
} catch (PDOException $e) {
  die("Error retrieving data from novel table: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html data-bs-theme="dark">
  <head>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Genre: <?php echo urlencode($tag); ?></title>
    <?php include ('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include ('header.php'); ?>
    <div class="container-fluid">
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 row-cols-xl-8 g-2">
        <?php while ($image = $result->fetch(PDO::FETCH_ASSOC)): ?>
          <div class="col">
            <div class="card shadow-sm h-100">
              <a class="shadow rounded" href="view.php?id=<?php echo $image['id']; ?>">
                <img class="w-100 object-fit-cover" style="border-radius: 2.9px 2.9px 0 0;" height="300" src="thumbnails/<?php echo $image['filename']; ?>">
              </a>
              <div class="card-body">
                <h5 class="card-text text-center fw-bold"><?php echo $image['title']; ?></h5>
                <p class="card-text text-center small fw-bold"><small>by <?php echo $image['artist']; ?></small></p>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
    <?php
      $totalPages = ceil($totalWithTag / $limit);
      $prevPage = $page - 1;
      $nextPage = $page + 1;
    ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?page=1&tag=<?php echo urlencode($tag); ?>"><i class="bi text-stroke bi-chevron-double-left"></i></a>
        <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $page - 1; ?>&tag=<?php echo urlencode($tag); ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?page=' . $i . '&tag=' . urlencode($tag) . '">' . $i . '</a>';
          }
        }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $page + 1; ?>&tag=<?php echo urlencode($tag); ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
        <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $totalPages; ?>&tag=<?php echo urlencode($tag); ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>
    <?php include ('../../bootstrapjs.php'); ?>
  </body>
</html>
