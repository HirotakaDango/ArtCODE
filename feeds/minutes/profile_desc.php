<?php
// Pagination
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$recordsPerPage = 20;
$offset = ($page - 1) * $recordsPerPage;

// Fetch videos records with user details using JOIN
$query = "SELECT videos.*, users.id AS userid, users.pic, users.artist
          FROM videos
          JOIN users ON videos.email = users.email
          WHERE videos.email = :email
          ORDER BY videos.id DESC
          LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->bindParam(':limit', $recordsPerPage, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total pages for the logged-in user
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM videos WHERE email = :email");
$totalStmt->bindParam(':email', $email, PDO::PARAM_STR);
$totalStmt->execute();
$total = $totalStmt->fetchColumn();
$totalPages = ceil($total / $recordsPerPage);
$prevPage = $page - 1;
$nextPage = $page + 1;
?>

    <div class="container-fluid">
      <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-1">
        <?php foreach ($rows as $row): ?>
          <?php include('video_info.php'); ?>
        <?php endforeach; ?>
      </div>
    </div>
    <style>
      .text-shadow {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);
      }
    </style>

    <!-- Pagination -->
    <div class="container mt-3">
      <div class="pagination d-flex gap-1 justify-content-center mt-3">
        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?by=newest&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
          <a class="btn btn-sm btn-primary fw-bold" href="?by=newest&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?by=newest&page=' . $i . '">' . $i . '</a>';
          }
        }
        ?>

        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?by=newest&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
          <a class="btn btn-sm btn-primary fw-bold" href="?by=newest&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
        <?php endif; ?>
      </div>
    </div>
    <div class="mt-5"></div>