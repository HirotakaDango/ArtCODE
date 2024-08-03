<?php
// Set the limit of images per page
$limit = 12;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Get the total number of images
$total = $db->querySingle("SELECT COUNT(*) FROM images");

// Get the images for the current page
$stmt = $db->prepare("SELECT * FROM images ORDER BY id DESC LIMIT ?, ?");
$stmt->bindValue(1, $offset, SQLITE3_INTEGER);
$stmt->bindValue(2, $limit, SQLITE3_INTEGER);
$result = $stmt->execute();
?>

    <?php include('image_card_home.php')?>