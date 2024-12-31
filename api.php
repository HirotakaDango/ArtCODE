<?php
// Replace this with the actual path to your database.sqlite file
$dbPath = 'database.sqlite';

// Connect to the database
$db = new SQLite3($dbPath);

// Check if the connection was successful
if (!$db) {
  die("Connection failed: " . $db->lastErrorMsg());
}

// Get query parameters
$artworkId = isset($_GET['artworkid']) ? intval($_GET['artworkid']) : 0;
$display = isset($_GET['display']) ? $_GET['display'] : '';
$option = isset($_GET['option']) ? $_GET['option'] : '';
$artworkType = isset($_GET['artwork_type']) ? $_GET['artwork_type'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$uid = isset($_GET['uid']) ? intval($_GET['uid']) : 0;
$sortBy = isset($_GET['sortby']) ? $_GET['sortby'] : 'newest'; // Default sorting
$rankings = isset($_GET['rankings']) ? $_GET['rankings'] : ''; // Rankings parameter
$character = isset($_GET['character']) ? $_GET['character'] : '';
$parody = isset($_GET['parody']) ? $_GET['parody'] : '';
$tag = isset($_GET['tag']) ? $_GET['tag'] : '';
$group = isset($_GET['group']) ? $_GET['group'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : ''; // New search parameter

// Define the sorting order
$sortOptions = [
  'newest' => 'images.id DESC',
  'oldest' => 'images.id ASC',
  'popular' => 'favorites_count DESC',
  'view' => 'images.view_count DESC',
  'least' => 'images.view_count ASC'
];

// Default sort order if not specified
$sortOrder = isset($sortOptions[$sortBy]) ? $sortOptions[$sortBy] : $sortOptions['newest'];

if ($display === 'all_images') {
  // Base query
  $queryAllImagesSql = "
    SELECT images.id, images.filename, images.tags, images.title, images.imgdesc, images.link, images.date, images.view_count, images.type, images.episode_name, images.artwork_type, images.`group`, images.categories, images.language, images.parodies, images.characters, images.original_filename,
           COALESCE(favorites_count, 0) AS favorites_count
    FROM images
    INNER JOIN users ON users.email = images.email
    LEFT JOIN (
      SELECT image_id, COUNT(*) AS favorites_count
      FROM favorites
      GROUP BY image_id
    ) AS favorites ON images.id = favorites.image_id
  ";

  // Add conditions to filter by user ID, artwork_type, type, characters, parodies, tags, group, title, and search term
  $conditions = [];
  if ($uid) {
    $conditions[] = "users.id = :uid";
  }
  if ($artworkType) {
    $conditions[] = "images.artwork_type = :artworkType";
  }
  if ($type) {
    $conditions[] = "images.type = :type";
  }
  if ($character) {
    $conditions[] = "images.characters LIKE :character";
  }
  if ($parody) {
    $conditions[] = "images.parodies LIKE :parody";
  }
  if ($tag) {
    $conditions[] = "images.tags LIKE :tag";
  }
  if ($group) {
    $conditions[] = "images.`group` = :group";
  }
  if ($search) {
    $conditions[] = "(images.title LIKE :search OR images.tags LIKE :search OR images.characters LIKE :search OR images.parodies LIKE :search)";
  }
  if ($conditions) {
    $queryAllImagesSql .= " WHERE " . implode(" AND ", $conditions);
  }

  // Add sorting
  $queryAllImagesSql .= " ORDER BY " . $sortOrder;

  if ($rankings) {
    $startDate = '';
    $endDate = '';
    $dateFormat = 'Y-m-d'; // Adjust according to your SQLite date format

    switch ($rankings) {
      case 'daily':
        $startDate = date($dateFormat);
        $endDate = $startDate;
        break;
      case 'weekly':
        $startDate = date('Y-m-d', strtotime('monday this week'));
        $endDate = date($dateFormat);
        break;
      case 'monthly':
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        break;
      case 'yearly':
        $startDate = date('Y-01-01');
        $endDate = date('Y-12-31');
        break;
      default:
        die("Invalid ranking period");
    }

    $queryAllImagesSql = "
      SELECT images.id, images.filename, images.tags, images.title, images.imgdesc, images.link, images.date, images.view_count, images.type, images.episode_name, images.artwork_type, images.`group`, images.categories, images.language, images.parodies, images.characters, images.original_filename,
             COALESCE(SUM(daily.views), 0) AS views
      FROM images
      INNER JOIN users ON users.email = images.email
      LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startDate AND :endDate
      LEFT JOIN (
        SELECT image_id, COUNT(*) AS favorites_count
        FROM favorites
        GROUP BY image_id
      ) AS favorites ON images.id = favorites.image_id
      GROUP BY images.id
      ORDER BY views DESC, images.id DESC
    ";
  }

  // Prepare and bind parameters
  $queryAllImages = $db->prepare($queryAllImagesSql);

  if ($uid) {
    $queryAllImages->bindValue(':uid', $uid, SQLITE3_INTEGER);
  }
  if ($artworkType) {
    $queryAllImages->bindValue(':artworkType', $artworkType, SQLITE3_TEXT);
  }
  if ($type) {
    $queryAllImages->bindValue(':type', $type, SQLITE3_TEXT);
  }
  if ($character) {
    $queryAllImages->bindValue(':character', "%$character%", SQLITE3_TEXT);
  }
  if ($parody) {
    $queryAllImages->bindValue(':parody', "%$parody%", SQLITE3_TEXT);
  }
  if ($tag) {
    $queryAllImages->bindValue(':tag', "%$tag%", SQLITE3_TEXT);
  }
  if ($group) {
    $queryAllImages->bindValue(':group', $group, SQLITE3_TEXT);
  }
  if ($search) {
    $queryAllImages->bindValue(':search', "%$search%", SQLITE3_TEXT);
  }
  if ($rankings) {
    $queryAllImages->bindValue(':startDate', $startDate, SQLITE3_TEXT);
    $queryAllImages->bindValue(':endDate', $endDate, SQLITE3_TEXT);
  }

  $resultAllImages = $queryAllImages->execute();

  $allImagesData = [];
  while ($row = $resultAllImages->fetchArray(SQLITE3_ASSOC)) {
    // Only include images of the requested type if specified
    if ($artworkType && $row['artwork_type'] !== $artworkType) {
      continue;
    }
    if ($search && !(
      strpos($row['title'], $search) !== false ||
      strpos($row['tags'], $search) !== false ||
      strpos($row['characters'], $search) !== false ||
      strpos($row['parodies'], $search) !== false
    )) {
      continue;
    }
    if ($type === 'nsfw' && $row['type'] !== 'nsfw') {
      continue;
    }

    // Calculate image size in MB
    $imagePath = 'images/' . $row['filename'];
    if (file_exists($imagePath)) {
      $imageSize = filesize($imagePath) / (1024 * 1024); // Size in MB
    } else {
      $imageSize = 0;
    }
    $row['size_mb'] = number_format($imageSize, 2); // Format size to 2 decimal places

    $imageId = $row['id'];

    if ($option === 'image_child') {
      // Query to retrieve related image_child records with user join for each image
      $queryImageChild = $db->prepare("
        SELECT image_child.id, image_child.filename, image_child.image_id, image_child.original_filename
        FROM image_child
        INNER JOIN users ON users.email = image_child.email
        WHERE image_child.image_id = :imageId
      ");
      $queryImageChild->bindValue(':imageId', $imageId, SQLITE3_INTEGER);
      $resultImageChild = $queryImageChild->execute();

      $imageChildData = [];
      $totalSize = $imageSize;
      $totalCount = 1; // Count the main image

      while ($childRow = $resultImageChild->fetchArray(SQLITE3_ASSOC)) {
        $childImagePath = 'images/' . $childRow['filename'];
        if (file_exists($childImagePath)) {
          $childImageSize = filesize($childImagePath) / (1024 * 1024); // Size in MB
        } else {
          $childImageSize = 0;
        }
        $childRow['size_mb'] = number_format($childImageSize, 2); // Format size to 2 decimal places
        $totalSize += $childImageSize;
        $totalCount++;

        $imageChildData[] = $childRow;
      }

      // Append image_child data to the current image record
      $row['image_child'] = $imageChildData;
      $row['total_size_mb'] = number_format($totalSize, 2); // Format total size to 2 decimal places
      $row['total_count'] = $totalCount;
    }

    $allImagesData[] = $row;
  }

  if ($uid && empty($allImagesData)) {
    // No images found for the specified user ID
    header('Content-Type: application/json');
    echo json_encode(['images' => []], JSON_PRETTY_PRINT);
  } else if (empty($allImagesData)) {
    // No images found matching the criteria
    header('Content-Type: application/json');
    echo json_encode(['message' => 'No images found matching the criteria.'], JSON_PRETTY_PRINT);
  } else {
    // Output all images with optional image_child as JSON
    header('Content-Type: application/json');
    echo json_encode(['images' => $allImagesData], JSON_PRETTY_PRINT);
  }

} else {
  if ($artworkId <= 0) {
    die("Invalid artwork ID");
  }

  // Query to retrieve image details from the 'images' table with user join based on artworkId
  $queryImage = $db->prepare("
    SELECT images.id, images.filename, images.tags, images.title, images.imgdesc, images.link, images.date, images.view_count, images.type, images.episode_name, images.artwork_type, images.`group`, images.categories, images.language, images.parodies, images.characters, images.original_filename, users.id AS uid, users.artist AS artist_name
    FROM images
    INNER JOIN users ON users.email = images.email
    WHERE images.id = :artworkid
  ");
  $queryImage->bindValue(':artworkid', $artworkId, SQLITE3_INTEGER);
  $resultImage = $queryImage->execute();

  $imageData = $resultImage->fetchArray(SQLITE3_ASSOC);

  if (!$imageData) {
    die("Image not found");
  }

// Calculate image size in MB and resolution
  $imagePath = 'images/' . $imageData['filename'];
  if (file_exists($imagePath)) {
    $imageSize = filesize($imagePath) / (1024 * 1024); // Size in MB
    $imageInfo = getimagesize($imagePath); // Get resolution
    $imageResolution = $imageInfo ? "{$imageInfo[0]}x{$imageInfo[1]}" : 'Unknown';
  } else {
    $imageSize = 0;
    $imageResolution = 'Unknown';
  }
  $imageData['size_mb'] = number_format($imageSize, 2); // Format size to 2 decimal places
  $imageData['resolution'] = $imageResolution; // Add resolution
  
  // Check if the type matches and if it is NSFW
  if ($type === 'nsfw' && $imageData['type'] !== 'nsfw') {
    die("NSFW content not allowed");
  }
  
  // Query to retrieve related image_child records
  $queryImageChild = $db->prepare("
    SELECT image_child.id, image_child.filename, image_child.image_id, image_child.original_filename
    FROM image_child
    INNER JOIN users ON users.email = image_child.email
    WHERE image_child.image_id = :artworkid
  ");
  $queryImageChild->bindValue(':artworkid', $artworkId, SQLITE3_INTEGER);
  $resultImageChild = $queryImageChild->execute();
  
  $imageChildData = [];
  $totalSize = $imageSize;
  $totalCount = 1; // Count the main image
  
  while ($row = $resultImageChild->fetchArray(SQLITE3_ASSOC)) {
    $childImagePath = 'images/' . $row['filename'];
    if (file_exists($childImagePath)) {
      $childImageSize = filesize($childImagePath) / (1024 * 1024); // Size in MB
      $childImageInfo = getimagesize($childImagePath); // Get resolution
      $childImageResolution = $childImageInfo ? "{$childImageInfo[0]}x{$childImageInfo[1]}" : 'Unknown';
    } else {
      $childImageSize = 0;
      $childImageResolution = 'Unknown';
    }
    $row['size_mb'] = number_format($childImageSize, 2); // Format size to 2 decimal places
    $row['resolution'] = $childImageResolution; // Add resolution
    $totalSize += $childImageSize;
    $totalCount++;
  
    $imageChildData[] = $row;
  }
  
  // Retrieve favorites count for the image
  $queryFavoritesCount = $db->prepare("
    SELECT COUNT(*) AS count
    FROM favorites
    WHERE image_id = :artworkid
  ");
  $queryFavoritesCount->bindValue(':artworkid', $artworkId, SQLITE3_INTEGER);
  $resultFavoritesCount = $queryFavoritesCount->execute();
  $favoritesCountRow = $resultFavoritesCount->fetchArray(SQLITE3_ASSOC);
  $favoritesCount = $favoritesCountRow['count'];
  
  // Check the display parameter and prepare the appropriate response
  if ($display === 'info') {
    // Detailed information response
    $response = [
      'images' => array_merge([$imageData], $imageChildData),
      'favorites_count' => $favoritesCount, // Add favorites count here
      'total_size_mb' => number_format($totalSize, 2), // Format total size to 2 decimal places
      'total_count' => $totalCount
    ];
  } else {
    // Basic information response
    $response = [
      'image' => [
        'url' => '/images/' . $imageData['filename'],
        'size_mb' => $imageData['size_mb'],
        'resolution' => $imageData['resolution']
      ],
      'image_child' => array_map(function($img) {
        return [
          'url' => '/images/' . $img['filename'],
          'size_mb' => $img['size_mb'],
          'resolution' => $img['resolution']
        ];
      }, $imageChildData),
      'total_size_mb' => number_format($totalSize, 2), // Format total size to 2 decimal places
      'total_count' => $totalCount
    ];
  }

  // Output the response as JSON
  header('Content-Type: application/json');
  echo json_encode($response, JSON_PRETTY_PRINT);
}
?>