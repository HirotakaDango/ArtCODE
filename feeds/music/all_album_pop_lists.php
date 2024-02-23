<?php
// Set the limit of pagination
$recordsPerPage = 250;
$offset = ($page - 1) * $recordsPerPage;

// Fetch distinct albums and the cover of the first song for each album
$query = "SELECT MIN(music.id) AS id, 
                    music.file, 
                    music.email, 
                    music.cover, 
                    music.album, 
                    music.title, 
                    users.id AS userid, 
                    users.artist,
                    COUNT(favorites_music.music_id) AS favorites_count
              FROM music 
              LEFT JOIN users ON music.email = users.email
              LEFT JOIN favorites_music ON music.id = favorites_music.music_id
              GROUP BY music.album
              ORDER BY favorites_count DESC
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

    <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)) : ?>
      <div class="card bg-dark-subtle bg-opacity-10 link-body-emphasis rounded-4 border-0 shadow mt-2">
        <div class="card-body">
            <h6 class="card-text text-start fw-bold text-shadow">
              <a class="text-decoration-none link-light link-body-emphasis" href="album.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&album=<?php echo $row['album']; ?>">
                <?php echo $row['album']; ?>
              </a>
            </h6>
            <p class="card-text small fw-bold text-shadow text-shadow">
              <small>by
                <a class="text-decoration-none link-light link-body-emphasis" href="artist.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&id=<?php echo $row['userid']; ?>">
                  <?php echo (!is_null($row['artist']) && strlen($row['artist']) > 25) ? mb_substr($row['artist'], 0, 25) . '...' : $row['artist']; ?>
                </a>
              </small>
            </p>
        </div>
      </div>
    <?php endwhile; ?>
