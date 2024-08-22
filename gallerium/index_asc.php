<?php
// Get the images for the current page
$stmt = $db->prepare("SELECT * FROM images ORDER BY id ASC LIMIT :offset, :limit");
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$result = $stmt->execute();
?>

<?php include('image_card_gallerium.php'); ?>