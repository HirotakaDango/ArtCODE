<?php
// Calculate the first and last day of the current year
$startOfYear = date('Y-01-01'); // First day of the year
$endOfYear = date('Y-12-31');   // Last day of the year

// Prepare and execute the query to count the total number of images with the specified parody
$stmt = $db->prepare("SELECT COUNT(*) FROM images WHERE REPLACE(parodies, ' ', '') LIKE :parodyWithoutSpaces ESCAPE '\\' OR REPLACE(parodies, ' ', '') LIKE :parody_start ESCAPE '\\' OR REPLACE(parodies, ' ', '') LIKE :parody_end ESCAPE '\\' OR parodies = :parody_exact");
$stmt->bindValue(':parodyWithoutSpaces', "{$parodyWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':parody_start', "%,{$parodyWithoutSpaces}", SQLITE3_TEXT);
$stmt->bindValue(':parody_end', "%,{$parodyWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':parody_exact', $parody, SQLITE3_TEXT);
$count = $stmt->execute()->fetchArray()[0];

// Define the limit and offset for the query
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT); // Assuming $email is the email you want to search for
$resultNum = $queryNum->execute();
$user = $resultNum->fetchArray(SQLITE3_ASSOC);

$numpage = $user['numpage'];

// Set the limit of images per page
$limit = empty($numpage) ? 50 : $numpage;
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Get the current page number from the URL parameter
$offset = ($page - 1) * $limit; // Calculate the offset based on the page number and limit

// Retrieve the total number of images with the specified parody
$stmt = $db->prepare("SELECT COUNT(*) FROM images WHERE REPLACE(parodies, ' ', '') LIKE :parodyWithoutSpaces ESCAPE '\\' OR REPLACE(parodies, ' ', '') LIKE :parody_start ESCAPE '\\' OR REPLACE(parodies, ' ', '') LIKE :parody_end ESCAPE '\\' OR parodies = :parody_exact");
$stmt->bindValue(':parodyWithoutSpaces', "{$parodyWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':parody_start', "%,{$parodyWithoutSpaces}", SQLITE3_TEXT);
$stmt->bindValue(':parody_end', "%,{$parodyWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':parody_exact', $parody, SQLITE3_TEXT);
$total = $stmt->execute()->fetchArray()[0];

// Retrieve the images for the current page, sorted by yearly views and then by image ID
$stmt = $db->prepare("SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(SUM(daily.views), 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfYear AND :endOfYear
  WHERE REPLACE(parodies, ' ', '') LIKE :parodyWithoutSpaces ESCAPE '\\' OR REPLACE(parodies, ' ', '') LIKE :parody_start ESCAPE '\\' OR REPLACE(parodies, ' ', '') LIKE :parody_end ESCAPE '\\' OR parodies = :parody_exact
  GROUP BY images.id
  ORDER BY views DESC, images.id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':parodyWithoutSpaces', "{$parodyWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':parody_start', "%,{$parodyWithoutSpaces}", SQLITE3_TEXT);
$stmt->bindValue(':parody_end', "%,{$parodyWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':parody_exact', $parody, SQLITE3_TEXT);
$stmt->bindValue(':startOfYear', $startOfYear, SQLITE3_TEXT);
$stmt->bindValue(':endOfYear', $endOfYear, SQLITE3_TEXT);
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();
?>

    <?php include('image_card_parody.php') ?>