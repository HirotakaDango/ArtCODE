<?php
// Get the current date in YYYY-MM-DD format
$currentDate = date('Y-m-d');

// Prepare and execute the query to count the total number of images with the specified character
$stmt = $db->prepare("SELECT COUNT(*) FROM images WHERE REPLACE(characters, ' ', '') LIKE :characterWithoutSpaces ESCAPE '\\' OR REPLACE(characters, ' ', '') LIKE :character_start ESCAPE '\\' OR REPLACE(characters, ' ', '') LIKE :character_end ESCAPE '\\' OR characters = :character_exact");
$stmt->bindValue(':characterWithoutSpaces', "{$characterWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':character_start', "%,{$characterWithoutSpaces}", SQLITE3_TEXT);
$stmt->bindValue(':character_end', "%,{$characterWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':character_exact', $character, SQLITE3_TEXT);
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

// Retrieve the total number of images with the specified character
$stmt = $db->prepare("SELECT COUNT(*) FROM images WHERE REPLACE(characters, ' ', '') LIKE :characterWithoutSpaces ESCAPE '\\' OR REPLACE(characters, ' ', '') LIKE :character_start ESCAPE '\\' OR REPLACE(characters, ' ', '') LIKE :character_end ESCAPE '\\' OR characters = :character_exact");
$stmt->bindValue(':characterWithoutSpaces', "{$characterWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':character_start', "%,{$characterWithoutSpaces}", SQLITE3_TEXT);
$stmt->bindValue(':character_end', "%,{$characterWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':character_exact', $character, SQLITE3_TEXT);
$total = $stmt->execute()->fetchArray()[0];

// Retrieve the images for the current page, sorted by daily views and then by image ID
$stmt = $db->prepare("SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(daily.views, 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id AND daily.date = :currentDate
  WHERE REPLACE(characters, ' ', '') LIKE :characterWithoutSpaces ESCAPE '\\' OR REPLACE(characters, ' ', '') LIKE :character_start ESCAPE '\\' OR REPLACE(characters, ' ', '') LIKE :character_end ESCAPE '\\' OR characters = :character_exact
  GROUP BY images.id
  ORDER BY views DESC, images.id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':characterWithoutSpaces', "{$characterWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':character_start', "%,{$characterWithoutSpaces}", SQLITE3_TEXT);
$stmt->bindValue(':character_end', "%,{$characterWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':character_exact', $character, SQLITE3_TEXT);
$stmt->bindValue(':currentDate', $currentDate, SQLITE3_TEXT);
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();
?>

    <?php include('image_card_character.php') ?>