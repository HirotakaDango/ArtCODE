<?php
$page = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]) : 1;
if ($page === false || $page < 1) {
  $page = 1;
}

$offset = ($page - 1) * $limit;

$whereClauses = [];
$bindings = [];

function addMultiLikeOrFilter(&$whereClauses, &$bindings, $getParamKey, $columnName, $paramPrefix) {
  if (!empty($_GET[$getParamKey])) {
    $value = trim($_GET[$getParamKey]);
    $valuesToFilter = [];
    if (strpos($value, ',') !== false) {
      $valuesToFilter = array_map('trim', explode(',', $value));
    } elseif (!empty($value)) {
      $valuesToFilter = [$value];
    }
    $valuesToFilter = array_filter($valuesToFilter);

    if (!empty($valuesToFilter)) {
      $subClauses = [];
      $counter = 0;
      foreach ($valuesToFilter as $singleValue) {
        $placeholder = ':' . $paramPrefix . '_' . $counter;
        $subClauses[] = $columnName . " LIKE " . $placeholder;
        $bindings[$placeholder] = '%' . $singleValue . '%';
        $counter++;
      }
      if (!empty($subClauses)) {
        $whereClauses[] = "(" . implode(" OR ", $subClauses) . ")";
      }
    }
  }
}

if (!empty($_GET['q'])) {
  $q_input_terms = array_filter(array_map('trim', explode(' ', trim($_GET['q']))));
  if (!empty($q_input_terms)) {
    $q_overall_and_clauses = [];
    $q_search_fields = ["private_images.title", "users.artist", "private_images.characters", "private_images.\"group\"", "private_images.categories", "private_images.tags", "private_images.parodies", "private_images.language"];
    $q_term_idx = 0;
    foreach ($q_input_terms as $q_term_value) {
      $q_term_placeholder = ':search_q_term_' . $q_term_idx;
      $bindings[$q_term_placeholder] = '%' . $q_term_value . '%';
      $current_term_or_clauses = [];
      foreach ($q_search_fields as $field) {
        $current_term_or_clauses[] = $field . " LIKE " . $q_term_placeholder;
      }
      if (!empty($current_term_or_clauses)) {
        $q_overall_and_clauses[] = "(" . implode(" OR ", $current_term_or_clauses) . ")";
      }
      $q_term_idx++;
    }
    if (!empty($q_overall_and_clauses)) {
      $whereClauses[] = "(" . implode(" AND ", $q_overall_and_clauses) . ")";
    }
  }
}

addMultiLikeOrFilter($whereClauses, $bindings, 'title', 'private_images.title', 'filter_title');
addMultiLikeOrFilter($whereClauses, $bindings, 'artist', 'users.artist', 'filter_artist');
addMultiLikeOrFilter($whereClauses, $bindings, 'character', 'private_images.characters', 'filter_char');
addMultiLikeOrFilter($whereClauses, $bindings, 'parody', 'private_images.parodies', 'filter_parody');
addMultiLikeOrFilter($whereClauses, $bindings, 'group', 'private_images."group"', 'filter_grp');
addMultiLikeOrFilter($whereClauses, $bindings, 'tag', 'private_images.tags', 'filter_tag');

if (!empty($_GET['language'])) {
  $whereClauses[] = "private_images.language = :filter_language";
  $bindings[':filter_language'] = trim($_GET['language']);
}

if (!empty($_GET['category'])) {
  $whereClauses[] = "private_images.categories LIKE :filter_category";
  $bindings[':filter_category'] = '%' . trim($_GET['category']) . '%';
}

if (!empty($_GET['type'])) {
  $whereClauses[] = "private_images.type = :filter_type";
  $bindings[':filter_type'] = trim($_GET['type']);
}

if (!empty($_GET['artwork_type'])) {
  $whereClauses[] = "private_images.artwork_type = :filter_artwork_type";
  $bindings[':filter_artwork_type'] = trim($_GET['artwork_type']);
}

if (!empty($_GET['uid']) && filter_var($_GET['uid'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
  $whereClauses[] = "users.id = :filter_uid";
  $bindings[':filter_uid'] = (int)$_GET['uid'];
}

// Date filters
function addDateInFilter(&$whereClauses, &$bindings, $paramName, $sqliteDatePart, $prefix) {
  if (!empty($_GET[$paramName])) {
    $values = array_filter(array_map('trim', explode(',', $_GET[$paramName])));
    $intValues = [];
    foreach ($values as $val) {
      if (ctype_digit($val)) {
        $intValues[] = intval($val);
      }
    }
    if (!empty($intValues)) {
      $placeholders = [];
      foreach ($intValues as $i => $intVal) {
        $ph = ":{$prefix}_{$i}";
        $placeholders[] = $ph;
        $bindings[$ph] = $intVal;
      }
      $whereClauses[] = "CAST(strftime('{$sqliteDatePart}', private_images.date) AS INTEGER) IN (" . implode(",", $placeholders) . ")";
    }
  }
}

function addDateRangeFilter(&$whereClauses, &$bindings, $startParam, $endParam, $sqliteDatePart, $prefix) {
  $startVal = isset($_GET[$startParam]) && ctype_digit($_GET[$startParam]) ? intval($_GET[$startParam]) : null;
  $endVal = isset($_GET[$endParam]) && ctype_digit($_GET[$endParam]) ? intval($_GET[$endParam]) : null;

  if ($startVal !== null && $endVal !== null) {
    if ($startVal > $endVal) {
      $tmp = $startVal;
      $startVal = $endVal;
      $endVal = $tmp;
    }
    $whereClauses[] = "CAST(strftime('{$sqliteDatePart}', private_images.date) AS INTEGER) BETWEEN :{$prefix}_start AND :{$prefix}_end";
    $bindings[":{$prefix}_start"] = $startVal;
    $bindings[":{$prefix}_end"] = $endVal;
  } elseif ($startVal !== null) {
    $whereClauses[] = "CAST(strftime('{$sqliteDatePart}', private_images.date) AS INTEGER) >= :{$prefix}_start";
    $bindings[":{$prefix}_start"] = $startVal;
  } elseif ($endVal !== null) {
    $whereClauses[] = "CAST(strftime('{$sqliteDatePart}', private_images.date) AS INTEGER) <= :{$prefix}_end";
    $bindings[":{$prefix}_end"] = $endVal;
  }
}

addDateInFilter($whereClauses, $bindings, 'year', '%Y', 'filter_year');
addDateInFilter($whereClauses, $bindings, 'month', '%m', 'filter_month');
addDateInFilter($whereClauses, $bindings, 'day', '%d', 'filter_day');

addDateRangeFilter($whereClauses, $bindings, 'year_start', 'year_end', '%Y', 'filter_year');
addDateRangeFilter($whereClauses, $bindings, 'month_start', 'month_end', '%m', 'filter_month');
addDateRangeFilter($whereClauses, $bindings, 'day_start', 'day_end', '%d', 'filter_day');

// SQL FROM and JOIN clauses
$joinClause = "LEFT JOIN private_favorites ON private_images.id = private_favorites.image_id";
$fromClause = "FROM private_images $joinClause";
if (!empty($_GET['uid']) || !empty($_GET['artist'])) {
  $fromClause .= " INNER JOIN users ON private_images.email = users.email";
}

// Always filter by favorites of logged-in user
$whereClauses[] = "private_favorites.email = :email";
$bindings[':email'] = $email;

// ADD THIS LINE to restrict results to only your images:
$whereClauses[] = "private_images.email = :email"; // already bound above

$sqlWhere = " WHERE " . implode(" AND ", $whereClauses);

// Total count
$totalQueryString = "SELECT COUNT(private_images.id) as total $fromClause $sqlWhere";
$totalStmt = $db->prepare($totalQueryString);
$total = 0;
if ($totalStmt !== false) {
  foreach ($bindings as $placeholder => $value) {
    $paramType = is_int($value) ? SQLITE3_INTEGER : SQLITE3_TEXT;
    $totalStmt->bindValue($placeholder, $value, $paramType);
  }
  $totalResult = $totalStmt->execute();
  if ($totalResult) {
    $totalRow = $totalResult->fetchArray(SQLITE3_ASSOC);
    if ($totalRow) {
      $total = (int)$totalRow['total'];
    }
  }
}

// Image query
$imagesQueryString = "SELECT private_images.* $fromClause $sqlWhere ORDER BY private_images.id DESC LIMIT :page_limit OFFSET :page_offset";
$stmt = $db->prepare($imagesQueryString);
$result = false;
if ($stmt !== false) {
  foreach ($bindings as $placeholder => $value) {
    $paramType = is_int($value) ? SQLITE3_INTEGER : SQLITE3_TEXT;
    $stmt->bindValue($placeholder, $value, $paramType);
  }
  $stmt->bindValue(':page_limit', $limit, SQLITE3_INTEGER);
  $stmt->bindValue(':page_offset', $offset, SQLITE3_INTEGER);
  $result = $stmt->execute();
}
?>

    <?php include('image_card_advance_search.php')?>