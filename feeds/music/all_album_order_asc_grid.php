<?php
// Set the limit of pagination
$recordsPerPage = 20;
$offset = ($page - 1) * $recordsPerPage;

// Fetch distinct albums and the cover of the first song for each album
$query = "SELECT MIN(music.id) AS id, music.file, music.email, music.cover, music.album, music.title, users.id AS userid, users.artist 
          FROM music 
          LEFT JOIN users ON music.email = users.email 
          GROUP BY music.album
          ORDER BY music.title ASC 
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

      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 row-cols-xl-8 g-1">
        <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)) : ?>
          <div class="col">
            <div class="card shadow-sm h-100 position-relative rounded-3">
              <a class="shadow position-relative btn p-0" href="album.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&album=<?php echo $row['album']; ?>">
                <img class="w-100 object-fit-cover rounded" height="200" src="covers/<?php echo $row['cover']; ?>">
                <i class="bi bi-play-fill position-absolute start-50 top-50 display-1 translate-middle"></i>
              </a>
              <div class="p-2 position-absolute bottom-0 start-0">
                <h5 class="card-text text-center fw-bold text-shadow"><a class="text-decoration-none text-white" href="album.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&album=<?php echo $row['album']; ?>"><?php echo (!is_null($row['album']) && strlen($row['album']) > 15) ? substr($row['album'], 0, 15) . '...' : $row['title']; ?></a></h5>
                <p class="card-text small fw-bold text-shadow text-shadow"><small>by <a class="text-decoration-none text-white" href="mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&id=<?php echo $row['userid']; ?>"><?php echo (!is_null($row['artist']) && strlen($row['artist']) > 15) ? substr($row['artist'], 0, 15) . '...' : $row['artist']; ?></a></small></p>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>