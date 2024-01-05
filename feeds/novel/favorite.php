<?php
require_once('auth.php');

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
$totalStmt = $db->prepare("SELECT COUNT(*) FROM favorites_novel WHERE email = :email");
$totalStmt->bindValue(':email', $email);
$totalStmt->execute();
$total = $totalStmt->fetchColumn();

// Get all favorite novels from the database, joined with the novel and users tables with pagination
try {
  $result = $db->prepare("SELECT novel.*, users.id AS user_id, users.email, users.artist FROM novel JOIN users ON novel.email = users.email JOIN favorites_novel ON novel.id = favorites_novel.novel_id WHERE favorites_novel.email = :email ORDER BY novel.id DESC LIMIT :limit OFFSET :offset");
  $result->bindValue(':email', $email);
  $result->bindValue(':limit', $limit, PDO::PARAM_INT);
  $result->bindValue(':offset', $offset, PDO::PARAM_INT);
  $result->execute();
} catch (PDOException $e) {
  die("Error retrieving data from favorite novels: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html data-bs-theme="dark">
  <head>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Favorites</title>
    <?php include ('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include ('header.php'); ?>
    <div class="container-fluid">
      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 row-cols-xl-8 g-1">
        <?php while ($favoriteNovel = $result->fetch(PDO::FETCH_ASSOC)): ?>
          <div class="col">
            <div class="card shadow-sm h-100">
              <a class="shadow rounded" href="view.php?id=<?php echo $favoriteNovel['id']; ?>">
                <img class="w-100 object-fit-cover" style="border-radius: 2.9px 2.9px 0 0;" height="200" src="thumbnails/<?php echo $favoriteNovel['filename']; ?>">
              </a>
              <div class="card-body">
                <h5 class="card-text text-center fw-bold"><?php echo $favoriteNovel['title']; ?></h5>
                <p class="card-text text-center small fw-bold"><small>by <?php echo $favoriteNovel['artist']; ?></small></p>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
    <?php
      $totalPages = ceil($total / $limit);
      $prevPage = $page - 1;
      $nextPage = $page + 1;
    ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
        <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $page - 1; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?page=' . $i . '">' . $i . '</a>';
          }
        }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $page + 1; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
        <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>
    <?php include ('../../bootstrapjs.php'); ?>
  </body>
</html>