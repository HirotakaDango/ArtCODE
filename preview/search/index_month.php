<?php
// Calculate the first and last day of the current month
$startOfMonth = date('Y-m-01'); // First day of the month
$endOfMonth = date('Y-m-t');   // Last day of the month

// Determine the number of items per page
$itemsPerPage = 12;

$yearFilter = isset($_GET['year']) ? $_GET['year'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// Prepare the search term by removing leading/trailing spaces and converting to lowercase
$searchTerm = trim(strtolower($searchTerm));

// Split the search term by comma to handle multiple tags or titles
$terms = array_map('trim', explode(',', $searchTerm));

// Prepare the search query with placeholders for terms
$query = "SELECT images.*, users.artist, users.pic, users.id AS user_id, COALESCE(SUM(daily.views), 0) AS views
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN daily ON images.id = daily.image_id AND daily.date BETWEEN :startOfMonth AND :endOfMonth
  WHERE 1=1";

// Create an array to hold the conditions for partial word matches
$conditions = array();

// Add conditions for tags and titles
foreach ($terms as $index => $term) {
  if (!empty($term)) {
    $conditions[] = "(LOWER(tags) LIKE ? OR LOWER(title) LIKE ? OR LOWER(characters) LIKE ? OR LOWER(parodies) LIKE ? OR LOWER(`group`) LIKE ?)";
  }
}

if (!empty($conditions)) {
  $query .= " AND (" . implode(' OR ', $conditions) . ")";
}

// Group by image to aggregate views
$query .= " GROUP BY images.id";

// Check if q (search term) is empty
if (empty($searchTerm)) {
  // If q is empty, order by views DESC
  $query .= " ORDER BY views DESC, images.id DESC";
} else {
  // Otherwise, order by views DESC
  $query .= " ORDER BY views DESC, images.id DESC";
}

// Prepare the SQL statement
$statement = $db->prepare($query);

// Bind the terms as parameters with wildcard matching for tags and titles
$paramIndex = 1;
foreach ($terms as $term) {
  if (!empty($term)) {
    $wildcardTerm = "%$term%";
    for ($i = 0; $i < 5; $i++) {
      $statement->bindValue($paramIndex++, $wildcardTerm, SQLITE3_TEXT);
    }
  }
}

// Bind the start and end dates of the current month
$statement->bindValue(':startOfMonth', $startOfMonth, SQLITE3_TEXT);
$statement->bindValue(':endOfMonth', $endOfMonth, SQLITE3_TEXT);

// Execute the query
$result = $statement->execute();

// Retrieve all images and filter by year if necessary
$resultArray = array();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  $imageYear = date('Y', strtotime($row['date']));
  if ($yearFilter === 'all' || strtolower($imageYear) === $yearFilter) {
    $resultArray[] = $row;
  }
}

// Count the number of images found
$numImages = count($resultArray);

// Calculate total pages
$totalPages = ceil($numImages / $itemsPerPage);

// Slice the array to get the items for the current page
$resultArray = array_slice($resultArray, $offset, $itemsPerPage);
?>

    <div class="container-fluid">
      <div class="mb-2">
        <form action="" method="GET">
          <div class="input-group">
            <input type="text" name="q" class="form-control text-lowercase fw-bold" placeholder="Search tags or title" value="<?php echo isset($searchTerm) ? $searchTerm : ''; ?>" maxlength="30" required onfocus="this.oldValue = this.value;" oninput="updatePlaceholder(this);" onkeyup="debouncedShowSuggestions(this, 'suggestions3')" />
            <button type="submit" class="btn btn-primary"><i class="bi bi-search text-stroke"></i></button>
          </div>
          <div id="suggestions3"></div>
        </form>
      </div>
      <div class="mb-2">
        <form action="" method="GET">
          <div class="input-group">
            <select name="year" class="form-control fw-bold" onchange="this.form.submit()">
              <option value="all" <?php echo ($yearFilter === 'all') ? 'selected' : ''; ?>>All Years</option>
              <?php
                // Fetch distinct years from the "date" column in the images table
                $yearsQuery = "SELECT DISTINCT strftime('%Y', date) AS year FROM images";
                $yearsResult = $db->query($yearsQuery);
                while ($yearRow = $yearsResult->fetchArray(SQLITE3_ASSOC)) {
                  $year = $yearRow['year'];
                  $selected = ($year == $yearFilter) ? 'selected' : '';
                  echo '<option value="' . $year . '"' . $selected . '>' . $year . '</option>';
                }
              ?>
            </select>
            <input type="hidden" name="q" value="<?php echo isset($searchTerm) ? $searchTerm : ''; ?>">
            <div class="input-group-prepend">
              <span class="input-group-text rounded-start-0">
                <i class="bi bi-calendar-fill"></i>
              </span>
            </div>
          </div>
        </form>
      </div>
      <div class="d-flex mb-1">
        <p class="fw-bold mb-1 mt-1">search for "<?php echo $searchTerm; ?>"</p>
        <button type="button" class="btn btn-sm btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#infoSearchA">
          <i class="bi bi-info-circle-fill"></i> 
        </button>
      </div>
      <h6 class="badge bg-primary"><?php echo $numImages; ?> images found</h6>
      <div class="modal fade" id="infoSearchA" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header">
              <h1 class="modal-title fs-5 fw-semibold" id="exampleModalLabel"><i class="bi bi-info-circle-fill"></i> Search Tips</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p class="fw-semibold text-center">"You can search multi tags or title using comma to get multiple result!"</p>
              <p class="fw-semibold">example:</p>
              <input class="form-control text-dark fw-bold" placeholder="tags, title (e.g: white, sky)" readonly>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include('image_card_search_preview.php'); ?>