<?php
// Replace this with the actual path to your database.sqlite file
$dbPath = 'database.sqlite';

// Connect to the database
$db = new SQLite3($dbPath);

// Check if the connection was successful
if (!$db) {
  header('Content-Type: application/json');
  echo json_encode(['error' => "Connection failed: " . $db->lastErrorMsg()]);
  exit;
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
$search = isset($_GET['search']) ? trim($_GET['search']) : ''; // New search parameter

$param_bindings = [];

// Define the sorting order
$sortOptions = [
  'newest' => 'images.id DESC',
  'oldest' => 'images.id ASC',
  'popular' => 'favorites_count DESC',
  'view' => 'images.view_count DESC',
  'least' => 'images.view_count ASC'
];

if ($display === 'all_images') {
  $baseQuerySql = "
    SELECT images.id, images.filename, images.tags, images.title, images.imgdesc, images.link, images.date, images.view_count, images.type, images.episode_name, images.artwork_type, images.\"group\", images.categories, images.language, images.parodies, images.characters, images.original_filename, users.artist,
           COALESCE(favorites.favorites_count, 0) AS favorites_count
    FROM images
    INNER JOIN users ON users.email = images.email
    LEFT JOIN (
      SELECT image_id, COUNT(*) AS favorites_count
      FROM favorites
      GROUP BY image_id
    ) AS favorites ON images.id = favorites.image_id
  ";

  $conditions = [];

  if ($uid) {
    $conditions[] = "users.id = :uid";
    $param_bindings[':uid'] = ['value' => $uid, 'type' => SQLITE3_INTEGER];
  }
  if ($artworkType) {
    $conditions[] = "images.artwork_type = :artworkType";
    $param_bindings[':artworkType'] = ['value' => $artworkType, 'type' => SQLITE3_TEXT];
  }
  if ($type) {
    $conditions[] = "images.type = :type";
    $param_bindings[':type'] = ['value' => $type, 'type' => SQLITE3_TEXT];
  }
  if ($character) {
    $conditions[] = "images.characters LIKE :character";
    $param_bindings[':character'] = ['value' => "%$character%", 'type' => SQLITE3_TEXT];
  }
  if ($parody) {
    $conditions[] = "images.parodies LIKE :parody";
    $param_bindings[':parody'] = ['value' => "%$parody%", 'type' => SQLITE3_TEXT];
  }
  if ($tag) {
    $conditions[] = "images.tags LIKE :tag";
    $param_bindings[':tag'] = ['value' => "%$tag%", 'type' => SQLITE3_TEXT];
  }
  if ($group) {
    $conditions[] = "images.\"group\" = :group";
    $param_bindings[':group'] = ['value' => $group, 'type' => SQLITE3_TEXT];
  }

  if ($search) {
    $processed_search = str_replace(',', ' ', $search);
    $search_input_terms = array_filter(array_map('trim', explode(' ', $processed_search)));

    if (!empty($search_input_terms)) {
      $search_overall_and_clauses = [];
      $search_param_fields = [
        "images.title", "images.imgdesc", "images.tags", "images.episode_name",
        "images.\"group\"", "images.characters", "images.categories",
        "images.language", "images.parodies", "users.artist"
      ];

      $s_term_idx = 0;
      foreach ($search_input_terms as $s_term_value) {
        $s_term_placeholder = ':search_p_term_' . $s_term_idx;
        $param_bindings[$s_term_placeholder] = ['value' => '%' . $s_term_value . '%', 'type' => SQLITE3_TEXT];

        $s_current_term_or_clauses = [];
        foreach ($search_param_fields as $s_field) {
          $s_current_term_or_clauses[] = $s_field . " LIKE " . $s_term_placeholder;
        }
        if (!empty($s_current_term_or_clauses)) {
          $search_overall_and_clauses[] = "(" . implode(" OR ", $s_current_term_or_clauses) . ")";
        }
        $s_term_idx++;
      }
      if (!empty($search_overall_and_clauses)) {
        $conditions[] = "(" . implode(" AND ", $search_overall_and_clauses) . ")";
      }
    }
  }

  $queryAllImagesSql = $baseQuerySql;

  if ($rankings) {
    $param_bindings = []; // Reset bindings for ranking query

    $startDate = '';
    $endDate = '';
    $dateFormat = 'Y-m-d';

    switch ($rankings) {
      case 'daily': $startDate = date($dateFormat); $endDate = $startDate; break;
      case 'weekly': $startDate = date('Y-m-d', strtotime('monday this week')); $endDate = date($dateFormat); break;
      case 'monthly': $startDate = date('Y-m-01'); $endDate = date('Y-m-t'); break;
      case 'yearly': $startDate = date('Y-01-01'); $endDate = date('Y-12-31'); break;
      default:
        header('Content-Type: application/json');
        echo json_encode(['error' => "Invalid ranking period"]);
        exit;
    }

    $queryAllImagesSql = "
      SELECT images.id, images.filename, images.tags, images.title, images.imgdesc, images.link, images.date, images.view_count, images.type, images.episode_name, images.artwork_type, images.\"group\", images.categories, images.language, images.parodies, images.characters, images.original_filename, users.artist,
             COALESCE(SUM(daily.views), 0) AS views,
             COALESCE(fav_count.favorites_count, 0) AS favorites_count
      FROM images
      INNER JOIN users ON users.email = images.email
      LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startDate AND :endDate
      LEFT JOIN (
        SELECT image_id, COUNT(*) AS favorites_count
        FROM favorites
        GROUP BY image_id
      ) AS fav_count ON images.id = fav_count.image_id
      GROUP BY images.id, users.artist
      ORDER BY views DESC, images.id DESC
    ";
    $param_bindings[':startDate'] = ['value' => $startDate, 'type' => SQLITE3_TEXT];
    $param_bindings[':endDate'] = ['value' => $endDate, 'type' => SQLITE3_TEXT];
  } else {
    if ($conditions) {
      $queryAllImagesSql .= " WHERE " . implode(" AND ", $conditions);
    }
    $sortOrder = isset($sortOptions[$sortBy]) ? $sortOptions[$sortBy] : $sortOptions['newest'];
    $queryAllImagesSql .= " ORDER BY " . $sortOrder;
  }

  $queryAllImages = $db->prepare($queryAllImagesSql);
  if (!$queryAllImages) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Failed to prepare SQL statement', 'sql_error' => $db->lastErrorMsg(), 'query_debug' => $queryAllImagesSql]);
    exit;
  }

  foreach ($param_bindings as $placeholder => $details) {
    $bindResult = $queryAllImages->bindValue($placeholder, $details['value'], $details['type']);
    if (!$bindResult) {
      header('Content-Type: application/json');
      echo json_encode(['error' => 'Failed to bind parameter', 'placeholder' => $placeholder, 'value' => $details['value'], 'sql_error' => $db->lastErrorMsg()]);
      exit;
    }
  }

  $resultAllImages = $queryAllImages->execute();
  if (!$resultAllImages) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Failed to execute query', 'sql_error' => $db->lastErrorMsg()]);
    exit;
  }

  $allImagesData = [];
  while ($row = $resultAllImages->fetchArray(SQLITE3_ASSOC)) {
    $imagePath = 'images/' . $row['filename'];
    if (file_exists($imagePath)) {
      $imageSize = filesize($imagePath) / (1024 * 1024);
    } else {
      $imageSize = 0;
    }
    $row['size_mb'] = number_format($imageSize, 2);

    $imageId = $row['id'];

    if ($option === 'image_child') {
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
      $totalCount = 1;

      while ($childRow = $resultImageChild->fetchArray(SQLITE3_ASSOC)) {
        $childImagePath = 'images/' . $childRow['filename'];
        if (file_exists($childImagePath)) {
          $childImageSize = filesize($childImagePath) / (1024 * 1024);
        } else {
          $childImageSize = 0;
        }
        $childRow['size_mb'] = number_format($childImageSize, 2);
        $totalSize += $childImageSize;
        $totalCount++;
        $imageChildData[] = $childRow;
      }
      $row['image_child'] = $imageChildData;
      $row['total_size_mb'] = number_format($totalSize, 2);
      $row['total_count'] = $totalCount;
    }
    $allImagesData[] = $row;
  }

  if ($uid && empty($allImagesData)) {
    header('Content-Type: application/json');
    echo json_encode(['images' => []], JSON_PRETTY_PRINT);
  } else if (empty($allImagesData)) {
    header('Content-Type: application/json');
    echo json_encode(['message' => 'No images found matching the criteria.', 'images' => []], JSON_PRETTY_PRINT);
  } else {
    header('Content-Type: application/json');
    echo json_encode(['images' => $allImagesData], JSON_PRETTY_PRINT);
  }

} else {
  if ($artworkId <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => "Invalid artwork ID"]);
    exit;
  }

  $queryImage = $db->prepare("
    SELECT images.id, images.filename, images.tags, images.title, images.imgdesc, images.link, images.date, images.view_count, images.type, images.episode_name, images.artwork_type, images.\"group\", images.categories, images.language, images.parodies, images.characters, images.original_filename, users.id AS uid, users.artist AS artist_name
    FROM images
    INNER JOIN users ON users.email = images.email
    WHERE images.id = :artworkid
  ");
  $queryImage->bindValue(':artworkid', $artworkId, SQLITE3_INTEGER);
  $resultImage = $queryImage->execute();
  $imageData = $resultImage->fetchArray(SQLITE3_ASSOC);

  if (!$imageData) {
    header('Content-Type: application/json');
    echo json_encode(['error' => "Image not found"]);
    exit;
  }

  $imagePath = 'images/' . $imageData['filename'];
  if (file_exists($imagePath)) {
    $imageSize = filesize($imagePath) / (1024 * 1024);
    $imageInfo = getimagesize($imagePath);
    $imageResolution = $imageInfo ? "{$imageInfo[0]}x{$imageInfo[1]}" : 'Unknown';
  } else {
    $imageSize = 0;
    $imageResolution = 'Unknown';
  }
  $imageData['size_mb'] = number_format($imageSize, 2);
  $imageData['resolution'] = $imageResolution;
  
  if ($type === 'nsfw' && $imageData['type'] !== 'nsfw') {
    header('Content-Type: application/json');
    echo json_encode(['error' => "NSFW content not allowed for this request type"]);
    exit;
  }
  
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
  $totalCount = 1;
  
  while ($row = $resultImageChild->fetchArray(SQLITE3_ASSOC)) {
    $childImagePath = 'images/' . $row['filename'];
    if (file_exists($childImagePath)) {
      $childImageSize = filesize($childImagePath) / (1024 * 1024);
      $childImageInfo = getimagesize($childImagePath);
      $childImageResolution = $childImageInfo ? "{$childImageInfo[0]}x{$childImageInfo[1]}" : 'Unknown';
    } else {
      $childImageSize = 0;
      $childImageResolution = 'Unknown';
    }
    $row['size_mb'] = number_format($childImageSize, 2);
    $row['resolution'] = $childImageResolution;
    $totalSize += $childImageSize;
    $totalCount++;
    $imageChildData[] = $row;
  }
  
  $queryFavoritesCount = $db->prepare("
    SELECT COUNT(*) AS count
    FROM favorites
    WHERE image_id = :artworkid
  ");
  $queryFavoritesCount->bindValue(':artworkid', $artworkId, SQLITE3_INTEGER);
  $resultFavoritesCount = $queryFavoritesCount->execute();
  $favoritesCountRow = $resultFavoritesCount->fetchArray(SQLITE3_ASSOC);
  $favoritesCount = $favoritesCountRow['count'];
  
  if ($display === 'info') {
    $response = [
      'images' => array_merge([$imageData], $imageChildData),
      'favorites_count' => $favoritesCount,
      'total_size_mb' => number_format($totalSize, 2),
      'total_count' => $totalCount
    ];
  } else {
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
      'total_size_mb' => number_format($totalSize, 2),
      'total_count' => $totalCount
    ];
  }

  header('Content-Type: application/json');
  echo json_encode($response, JSON_PRETTY_PRINT);
}
?>
