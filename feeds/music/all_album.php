<?php
require_once('../../auth.php');
$db = new SQLite3('../../database.sqlite');
$email = $_SESSION['email'];

// Pagination
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$recordsPerPage = 20;
$offset = ($page - 1) * $recordsPerPage;

// Fetch distinct albums and the cover of the first song for each album
$query = "SELECT MIN(music.id) AS id, music.file, music.email, music.cover, music.album, music.title, users.id AS userid, users.artist 
          FROM music 
          LEFT JOIN users ON music.email = users.email 
          GROUP BY music.album
          ORDER BY id DESC 
          LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);
$stmt->bindValue(':limit', $recordsPerPage, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();

// Calculate total pages
$total = $db->querySingle("SELECT COUNT(DISTINCT album) FROM music");
$totalPages = ceil($total / $recordsPerPage);
$prevPage = $page - 1;
$nextPage = $page + 1;
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Albums</title>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container-fluid mt-3">
      <?php include('header.php'); ?>
      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 row-cols-xl-8 g-1">
        <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)) : ?>
          <div class="col">
            <div class="card shadow-sm h-100 position-relative rounded-3">
              <a class="shadow position-relative btn p-0" href="album.php?album=<?php echo $row['album']; ?>">
                <img class="w-100 object-fit-cover rounded" height="200" src="covers/<?php echo $row['cover']; ?>">
                <i class="bi bi-play-fill position-absolute start-50 top-50 display-1 translate-middle"></i>
              </a>
              <div class="p-2 position-absolute bottom-0 start-0">
                <h5 class="card-text text-center fw-bold text-shadow"><a class="text-decoration-none text-white" href="album.php?album=<?php echo $row['album']; ?>"><?php echo (!is_null($row['title']) && strlen($row['title']) > 15) ? substr($row['title'], 0, 15) . '...' : $row['title']; ?></a></h5>
                <p class="card-text small fw-bold text-shadow text-shadow"><small>by <a class="text-decoration-none text-white" href="artist.php?id=<?php echo $row['userid']; ?>"><?php echo (!is_null($row['artist']) && strlen($row['artist']) > 15) ? substr($row['artist'], 0, 15) . '...' : $row['artist']; ?></a></small></p>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>

    <!-- Pagination -->
    <div class="container mt-3">
      <div class="pagination d-flex gap-1 justify-content-center mt-3">
        <?php if ($page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
          <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
          <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
          <a class="btn btn-sm btn-primary fw-bold" href="?page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
        <?php endif; ?>
      </div>
    </div>
    <style>
      .text-shadow {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);
      }
    </style>
    <div class="mt-5"></div>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
