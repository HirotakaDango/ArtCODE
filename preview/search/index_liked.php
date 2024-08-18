<?php
// Determine the number of items per page
$itemsPerPage = 12;

$yearFilter = isset($_GET['year']) ? $_GET['year'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

if (isset($_GET['q'])) {
  $searchTerm = $_GET['q'];

  // Prepare the search term by removing leading/trailing spaces and converting to lowercase
  $searchTerm = trim(strtolower($searchTerm));

  // Split the search term by comma to handle multiple tags or titles
  $terms = array_map('trim', explode(',', $searchTerm));

  // Prepare the search query with placeholders for terms
  $query = "SELECT DISTINCT images.*
            FROM images
            LEFT JOIN favorites ON images.id = favorites.image_id
            WHERE favorites.email = :email
            AND (";

  // Create an array to hold the conditions for partial word matches
  $conditions = array();

  // Add conditions for tags and titles
  foreach ($terms as $index => $term) {
    if (!empty($term)) {
      $conditions[] = "(LOWER(tags) LIKE :tag$index OR LOWER(title) LIKE :title$index OR LOWER(characters) LIKE :characters$index OR LOWER(parodies) LIKE :parodies$index or LOWER(`group`) LIKE :group$index)";
    }
  }

  // Combine all conditions using OR
  if (!empty($conditions)) {
    $query .= implode(' OR ', $conditions) . ")";
  } else {
    // If no search terms are provided, close the parenthesis
    $query .= "1=1)";
  }

  // Order by ID in descending order
  $query .= " ORDER BY images.id DESC";

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

  // Bind email for favorites
  $statement->bindValue(':email', $email, SQLITE3_TEXT);

  // Execute the query
  $result = $statement->execute();

  // Filter the images by year if a year value is provided
  if (!empty($yearFilter) && $yearFilter !== 'all') {
    $filteredImages = array();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
      $imageYear = date('Y', strtotime($row['date']));
      if (strtolower($imageYear) === $yearFilter) {
        $filteredImages[] = $row;
      }
    }
    $resultArray = $filteredImages;
  } else {
    // Retrieve all images
    $resultArray = array();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
      $resultArray[] = $row;
    }
  }

  // Count the number of images found
  $numImages = count($resultArray);
} else {
  // Retrieve all images if no search term is provided
  $query = "SELECT images.*, COUNT(favorites.id) AS favorite_count 
            FROM images 
            LEFT JOIN favorites ON images.id = favorites.image_id 
            GROUP BY images.id 
            ORDER BY favorite_count DESC";
  $result = $db->query($query);
  $resultArray = array();
  while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $resultArray[] = $row;
  }
  $numImages = count($resultArray);
}

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