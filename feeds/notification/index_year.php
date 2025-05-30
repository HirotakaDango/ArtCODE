<?php
// Get the current date
$currentDate = date('Y-m-d');

// Get the first and last day of the current Year
$startOfYear = date('Y-01-01');
$endOfYear = date('Y-12-31');

// Prepare the query
$stmt = $db->prepare("
    SELECT 
        images.*, 
        users.artist, 
        users.pic, 
        users.id AS user_id, 
        COALESCE(SUM(daily.views), 0) AS views
    FROM images
    JOIN users ON images.email = users.email
    LEFT JOIN daily ON images.id = daily.image_id 
        AND daily.date BETWEEN :startOfYear AND :endOfYear
    GROUP BY images.id
    ORDER BY views DESC, images.id DESC
    LIMIT :limit OFFSET :offset
");

// Bind parameters
$stmt->bindValue(':startOfYear', $startOfYear, SQLITE3_TEXT);
$stmt->bindValue(':endOfYear', $endOfYear, SQLITE3_TEXT);
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);

// Execute the query
$result = $stmt->execute();
?>

    <div class="container-fluid">
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-1">
        <?php while ($image = $result->fetchArray()): ?>
          <?php
            $title = $image['title'];
            $filename = $image['filename'];
            $email = $image['email'];
            $artist = '';
            $stmt = $db->prepare("SELECT id, artist FROM users WHERE email = ?");
            $stmt->bindValue(1, $email, SQLITE3_TEXT);
            $result2 = $stmt->execute();
            if ($user = $result2->fetchArray()) {
              $artist = $user['artist'];
              $id = $user['id'];
            }
          ?>
          <?php include($_SERVER['DOCUMENT_ROOT'] . '/feeds/explores/card_explores.php'); ?>
        <?php endwhile; ?>
      </div>
    </div>