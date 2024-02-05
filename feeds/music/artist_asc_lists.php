<?php
// Set the limit of pagination
$recordsPerPage = 250;
$offset = ($page - 1) * $recordsPerPage;

// Fetch music records with user details using JOIN
$queryMusic = "SELECT music.*, users.id AS userid, users.artist FROM music JOIN users ON music.email = users.email WHERE users.id = :userID ORDER BY music.id ASC LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($queryMusic);
$stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
$stmt->bindParam(':limit', $recordsPerPage, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

// Fetch all rows as an associative array
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total pages for the logged-in user
$queryTotal = "SELECT COUNT(*) FROM music WHERE email IN (SELECT email FROM users WHERE id = :userID)";
$stmtTotal = $db->prepare($queryTotal);
$stmtTotal->bindParam(':userID', $userID, PDO::PARAM_INT);
$stmtTotal->execute();
$total = $stmtTotal->fetchColumn();
$totalPages = ceil($total / $recordsPerPage);
$prevPage = $page - 1;
$nextPage = $page + 1;
?>

    <div class="container-fluid">
      <?php foreach ($rows as $row): ?>
        <?php include('music_info_lists.php'); ?>
      <?php endforeach; ?>
    </div>
    <style>
      .text-shadow {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);
      }

      .button-group {
        display: flex;
        flex-wrap: wrap;
      }

      @media only screen and (min-width: 767px) {
        .rounded-min-5 {
          border-radius: 1.6rem;
        }
      }
        
      .button-group button {
        white-space: nowrap; /* Prevent wrapping of button text */
      }
    </style>
    
    <!-- Pagination -->
    <div class="container mt-3">
      <div class="pagination d-flex gap-1 justify-content-center mt-3">
        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=oldest_lists&id=<?php echo $userID; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
          <a class="btn btn-sm btn-primary fw-bold" href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=oldest_lists&id=<?php echo $userID; ?>&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?mode=' . (isset($_GET['mode']) ? $_GET['mode'] : 'grid') . '&by=oldest_lists&id=' . $userID . '&page=' . $i . '">' . $i . '</a>';
          }
        }
        ?>

        <?php if ($page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=oldest_lists&id=<?php echo $userID; ?>&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
          <a class="btn btn-sm btn-primary fw-bold" href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=oldest_lists&id=<?php echo $userID; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
        <?php endif; ?>
      </div>
    </div>
    <div class="mt-5"></div>