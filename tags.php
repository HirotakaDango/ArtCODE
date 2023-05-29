<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Retrieve all tags used in the images table and sort them
$result = $db->query("SELECT DISTINCT tags FROM images");

// Store the tags as an array
$tags = [];
while ($row = $result->fetchArray()) {
  $tagList = explode(',', $row['tags']);
  foreach ($tagList as $tag) {
    $tags[] = trim($tag);
  }
}

// Filter out any empty tags
$tags = array_filter($tags);

// Remove duplicate tags
$tags = array_unique($tags);

// Sort the tags alphabetically and numerically
usort($tags, function($a, $b) {
    return strcasecmp($a, $b);
});

// Group the tags by the first character
$groupedTags = [];
foreach ($tags as $tag) {
  $firstChar = strtoupper(mb_substr($tag, 0, 1));
  $groupedTags[$firstChar][] = $tag;
}

// Pagination
$perPage = 300; // Number of tags per page
$totalTags = count($tags);
$totalPages = ceil($totalTags / $perPage);

// Get the current page number
$currentpage = isset($_GET['page']) ? $_GET['page'] : 1;
if ($currentpage < 1) {
  $currentpage = 1;
} elseif ($currentpage > $totalPages) {
  $currentpage = $totalPages;
}

// Calculate the offset for the SQL query
$offset = ($currentpage - 1) * $perPage;

// Retrieve the tags for the current page
$tagsPage = array_slice($tags, $offset, $perPage);

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tags</title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <?php include('taguserheader.php'); ?>
    <div class="input-group mb-3 mt-2">
      <input type="text" class="form-control me-2 ms-2" placeholder="Search tag" id="search-input">
    </div>
    <!-- Display the tags as grouped buttons -->
    <div class="container-fluid">
      <?php foreach ($groupedTags as $group => $tags): ?>
        <h5 class="fw-semibold text-secondary text-start">Category <?php echo $group; ?></h5>
        <div class="row">
          <?php foreach ($tags as $tag): ?>
            <?php
              // Check if the tag has any associated images
              $stmt = $db->prepare("SELECT COUNT(*) FROM images WHERE tags LIKE ?");
              $stmt->bindValue(1, '%' . $tag . '%');
              $countResult = $stmt->execute()->fetchArray()[0];
              if ($countResult > 0):
            ?>
              <div class="col-md-3 col-sm-6">
                <a href="tagged_images.php?tag=<?php echo urlencode($tag); ?>" class="opacity-75 btn tag-button btn-secondary mb-2 fw-bold text-start w-100"><?php echo $tag; ?></a>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="mt-5 mb-2 d-flex justify-content-center btn-toolbar container">
      <?php if ($currentpage > 1): ?>
        <a href="tags.php?page=<?php echo $currentpage - 1; ?>" class="btn rounded-pill fw-bold btn-secondary opacity-50 me-1"><i class="bi bi-arrow-left-circle-fill"></i> prev</a>
      <?php endif; ?>
      <?php if ($currentpage < $totalPages): ?>
        <a href="tags.php?page=<?php echo $currentpage + 1; ?>" class="btn rounded-pill fw-bold btn-secondary opacity-50 ms-1">next <i class="bi bi-arrow-right-circle-fill"></i></a>
      <?php endif; ?>
    </div>
    <script>
      // Get the search input element
      const searchInput = document.getElementById('search-input');

      // Get all the tag buttons
      const tagButtons = document.querySelectorAll('.tag-button');

      // Add an event listener to the search input field
      searchInput.addEventListener('input', () => {
        const searchTerm = searchInput.value.toLowerCase();

        // Filter the tag buttons based on the search term
        tagButtons.forEach(button => {
          const tag = button.textContent.toLowerCase();

          if (tag.includes(searchTerm)) {
            button.style.display = 'inline-block';
          } else {
            button.style.display = 'none';
          }
        });
      });
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
