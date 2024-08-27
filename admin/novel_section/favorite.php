<?php
// admin/novel_section/favorite.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();

// Retrieve the email from the session
$email = $_SESSION['admin']['email'];

// Connect to the SQLite database
$db = new PDO('sqlite:' . $_SERVER['DOCUMENT_ROOT'] . '/database.sqlite');

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
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <title>Admin Novel Management</title>
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>
  <body>
    <div class="container-fluid px-0">
      <div class="row g-0">
        <div class="col-auto">
          <?php include('../admin_header.php'); ?>
        </div>
        <div class="col overflow-auto vh-100">
          <?php include('../navbar.php'); ?>
          <div>
            <div class="container-fluid mt-3">
              <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 row-cols-xl-8 g-1">
                <?php while ($image = $result->fetch(PDO::FETCH_ASSOC)): ?>
                  <?php include ('novel_info.php'); ?>
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
            <div class="mt-5"></div>
          </div>
        </div>
      </div>
    </div>
    <?php include ('../../bootstrapjs.php'); ?>
  </body>
</html>
