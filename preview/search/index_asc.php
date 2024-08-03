<?php
// Determine the number of items per page
$limit  = 12;

$yearFilter = isset($_GET['year']) ? $_GET['year'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Prepare the search term by removing leading/trailing spaces and converting to lowercase
$searchTerm = isset($searchTerm) ? trim(strtolower($searchTerm)) : '';

// Split the search term by comma to handle multiple tags or titles
$terms = array_map('trim', explode(',', $searchTerm));

// Prepare the search query with placeholders for terms
$query = "SELECT * FROM images WHERE 1=1";

// Create an array to hold the conditions for partial word matches
$conditions = array();

// Add conditions for tags, titles, characters, parodies, and group
foreach ($terms as $index => $term) {
  if (!empty($term)) {
    $conditions[] = "(LOWER(tags) LIKE :term{$index} OR LOWER(title) LIKE :term{$index} OR LOWER(characters) LIKE :term{$index} OR LOWER(parodies) LIKE :term{$index} OR LOWER(`group`) LIKE :term{$index})";
  }
}

if (!empty($conditions)) {
  $query .= " AND (" . implode(' OR ', $conditions) . ")";
}

// Check if q (search term) is empty
if (empty($searchTerm)) {
  // If q is empty, order by id ASC
  $query .= " ORDER BY id ASC";
} else {
  // Otherwise, order by id ASC
  $query .= " ORDER BY id ASC";
}

// Prepare the SQL statement
$statement = $db->prepare($query);

// Bind the terms as parameters with wildcard matching for tags and titles
foreach ($terms as $index => $term) {
  if (!empty($term)) {
    $wildcardTerm = "%$term%";
    $statement->bindValue(":term{$index}", $wildcardTerm, PDO::PARAM_STR);
  }
}

// Execute the query
try {
  $statement->execute();
} catch (PDOException $e) {
  // Handle the exception
  echo 'Query failed: ' . $e->getMessage();
  $resultArray = [];
}

// Retrieve all images and filter by year if necessary
$resultArray = [];
while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
  $imageYear = date('Y', strtotime($row['date']));
  if ($yearFilter === 'all' || strtolower($imageYear) === $yearFilter) {
    $resultArray[] = $row;
  }
}

// Count the number of images found
$numImages = count($resultArray);

// Calculate total pages
$totalPages = $limit > 0 ? ceil($numImages / $limit) : 0;

// Slice the array to get the items for the current page
$resultArray = array_slice($resultArray, $offset, $limit);
?>

    <div class="w-100 px-2">
      <div class="mb-2">
        <form action="" method="GET">
          <div class="input-group">
            <input type="text" name="q" class="form-control text-lowercase fw-bold" placeholder="Search tags or title" value="<?php echo htmlspecialchars(isset($searchTerm) ? $searchTerm : '', ENT_QUOTES, 'UTF-8'); ?>" maxlength="30" required onfocus="this.oldValue = this.value;" oninput="updatePlaceholder(this);" onkeyup="debouncedShowSuggestions(this, 'suggestions3')" />
            <button type="submit" class="btn btn-primary"><i class="bi bi-search text-stroke"></i></button>
          </div>
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
              while ($yearRow = $yearsResult->fetch(PDO::FETCH_ASSOC)) {
                $year = $yearRow['year'];
                $selected = ($year == $yearFilter) ? 'selected' : '';
                echo '<option value="' . htmlspecialchars($year, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>' . htmlspecialchars($year, ENT_QUOTES, 'UTF-8') . '</option>';
              }
              ?>
            </select>
            <input type="hidden" name="q" value="<?php echo htmlspecialchars(isset($searchTerm) ? $searchTerm : '', ENT_QUOTES, 'UTF-8'); ?>">
            <div class="input-group-prepend">
              <span class="input-group-text rounded-start-0">
                <i class="bi bi-calendar-fill"></i>
              </span>
            </div>
          </div>
        </form>
      </div>
      <div class="d-flex mb-1">
        <p class="fw-bold mb-1 mt-1">search for "<?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>"</p>
        <button type="button" class="btn btn-sm btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#infoSearchA">
          <i class="bi bi-info-circle-fill"></i>
        </button>
      </div>
      <h6 class="badge bg-primary"><?php echo htmlspecialchars($numImages, ENT_QUOTES, 'UTF-8'); ?> images found</h6>
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
    <?php include('image_card_search_preview.php')?>