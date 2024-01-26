<?php
// Set the limit of pagination
$recordsPerPage = 20;
$offset = ($page - 1) * $recordsPerPage;

// Fetch favorites records
$query = "SELECT favorites_music.id, favorites_music.music_id, favorites_music.email, music.*, users.id AS userid, users.artist
          FROM favorites_music
          JOIN music ON favorites_music.music_id = music.id
          JOIN users ON music.email = users.email
          WHERE favorites_music.email = :email
          ORDER BY favorites_music.id DESC
          LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$stmt->bindValue(':limit', $recordsPerPage, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();

// Fetch all rows as an associative array
$rows = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  $rows[] = $row;
}

// Calculate total pages for the logged-in user
$total = $db->querySingle("SELECT COUNT(*) FROM favorites_music WHERE email = '$email'");
$totalPages = ceil($total / $recordsPerPage);
$prevPage = $page - 1;
$nextPage = $page + 1;
?>

      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 row-cols-xl-8 g-1">
        <?php foreach ($rows as $row): ?>
          <?php include('music_info.php'); ?>
        <?php endforeach; ?>
      </div>