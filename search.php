<?php
require_once('auth.php');

$email = $_SESSION['email'];

// Establish a connection to the SQLite database
$database = new SQLite3('database.sqlite');

// Handle the search form submission
if (isset($_GET['search'])) {
  $searchTerm = $_GET['search'];

  // Check if the "year" parameter is set
  $yearFilter = isset($_GET['year']) ? $_GET['year'] : 'all';

  // Prepare the search term by removing leading/trailing spaces and converting to lowercase
  $searchTerm = trim(strtolower($searchTerm));

  // Split the search term by comma to handle multiple tags or titles
  $terms = array_map('trim', explode(',', $searchTerm));

  // Prepare the search query with placeholders for terms
  $query = "SELECT * FROM images WHERE ";

  // Create an array to hold the conditions for partial word matches
  $conditions = array();

  // Add conditions for tags and titles
  foreach ($terms as $index => $term) {
    $conditions[] = "(LOWER(tags) LIKE ? OR LOWER(title) LIKE ?)";
  }

  // Combine all conditions using OR
  $query .= implode(' OR ', $conditions);

  // Add the ORDER BY clause to order by ID in descending order
  $query .= " ORDER BY id DESC";

  // Prepare the SQL statement
  $statement = $database->prepare($query);

  // Bind the terms as parameters with wildcard matching for tags and titles
  $paramIndex = 1;
  foreach ($terms as $term) {
    $wildcardTerm = "%$term%";
    $statement->bindValue($paramIndex++, $wildcardTerm, SQLITE3_TEXT);
    $statement->bindValue($paramIndex++, $wildcardTerm, SQLITE3_TEXT);
  }

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

  // Redirect to the search page with the search term
  header("Location: search/?q=$searchTerm");
  exit(); // Ensure that no further code is executed after the redirect
} else {
  // Retrieve all images if no search term is provided
  $query = "SELECT * FROM images ORDER BY id DESC";
  $result = $database->query($query);
  $resultArray = array();
  while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $resultArray[] = $row;
  }
  $numImages = count($resultArray);
  
  // Redirect to the search page with the search term
  header("Location: search/?q=");
  exit(); // Ensure that no further code is executed after the redirect
}
?>
