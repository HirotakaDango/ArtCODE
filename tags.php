<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Check if a search tag is provided
$searchTags = isset($_GET['tag']) ? explode(',', $_GET['tag']) : [];

// Retrieve the count of images for each tag
$query = "SELECT tags, COUNT(*) as count FROM images";
if (!empty($searchTags)) {
  $tagConditions = [];
  foreach ($searchTags as $searchTag) {
    $searchTag = trim($searchTag);
    $tagConditions[] = "tags LIKE '%" . $searchTag . "%'";
  }
  $query .= " WHERE " . implode(" OR ", $tagConditions);
}
$query .= " GROUP BY tags";

$result = $db->query($query);

// Store the tag counts as an associative array
$tagCounts = [];
while ($row = $result->fetchArray()) {
  $tagList = explode(',', $row['tags']);
  foreach ($tagList as $tag) {
    $trimmedTag = trim($tag);
    if (!isset($tagCounts[$trimmedTag])) {
      $tagCounts[$trimmedTag] = 0;
    }
    $tagCounts[$trimmedTag] += $row['count'];
  }
}

// Sort the tags alphabetically and numerically
ksort($tagCounts, SORT_NATURAL | SORT_FLAG_CASE);

// Group the tags by the first character
$groupedTags = [];
foreach ($tagCounts as $tag => $count) {
  $firstChar = strtoupper(mb_substr($tag, 0, 1));
  $groupedTags[$firstChar][$tag] = $count;
}

// Pagination
$perPage = 500; // Number of tags per page
$totalTags = count($tagCounts);
$totalPages = ceil($totalTags / $perPage);

// Get the current page number
$currentpage = isset($_GET['page']) ? $_GET['page'] : 1;
if ($currentpage < 1) {
  $currentpage = 1;
} elseif ($currentpage > $totalPages) {
  $currentpage = $totalPages;
}

// Calculate the offset for the array slice
$offset = ($currentpage - 1) * $perPage;

// Retrieve the tags for the current page
$tagsPage = array_slice($tagCounts, $offset, $perPage, true);
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
    <div class="container-fluid ">
      <div class="input-group mb-3 mt-2">
        <input type="text" class="form-control fw-bold" placeholder="Search tag" id="search-input" maxlength="30" value="<?php echo isset($searchTags) ? implode(',', $searchTags) : ''; ?>" required onfocus="this.oldValue = this.value;" oninput="updatePlaceholder(this);">
        <button class="btn btn-primary fw-bold" onclick="searchTag()"><i class="bi bi-search" style="-webkit-text-stroke: 1px;"></i></button>
      </div>
    </div>
    <!-- Display the tags as grouped buttons -->
    <div class="container-fluid">
      <?php if (empty($searchTags)): ?>
        <p class="fw-semibold text-secondary text-start mt-1">All Tags</p>
      <?php else: ?>
        <div class="d-flex mb-1">
          <p class="fw-semibold text-secondary mb-1 mt-1 text-start">Search Results for "<?php echo implode(', ', $searchTags); ?>"</p>
          <button type="button" class="btn btn-sm btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#infoSearchA">
            <i class="bi bi-info-circle-fill"></i> 
          </button>
        </div>
        <div class="modal fade" id="infoSearchA" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5 fw-semibold" id="exampleModalLabel"><i class="bi bi-info-circle-fill"></i> Search Tips</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <p class="fw-semibold text-center">"You can search multiple tags using commas to get multiple results!"</p>
                <p class="fw-semibold">Example:</p>
                <input class="form-control text-dark fw-bold" placeholder="tags (e.g: white, sky)" readonly>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
      <?php foreach ($groupedTags as $group => $tags): ?>
        <h5 class="fw-bold text-secondary text-start mt-1">Category <?php echo $group; ?></h5>
        <div class="row">
          <?php foreach ($tags as $tag => $count): ?>
            <?php
              // Check if the tag has any associated images
              $stmt = $db->prepare("SELECT * FROM images WHERE tags LIKE ? ORDER BY id DESC LIMIT 1");
              $stmt->bindValue(1, '%' . $tag . '%');
              $imageResult = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
              if ($imageResult):
            ?>
            <div class="col-md-3 col-sm-6">
              <a href="tagged_images.php?tag=<?php echo urlencode($tag); ?>" class="m-1 tag-button opacity-75 d-block text-decoration-none">
                <div class="card text-bg-dark">
                  <img data-src="thumbnails/<?php echo $imageResult['filename']; ?>" alt="<?php echo $imageResult['title']; ?>" class="card-img object-fit-cover w-100" style="border-radius: 5px; height: 80px; object-position: top left;">
                  <div class="card-img-overlay d-flex align-items-center justify-content-center" style="height: 80px;">
                    <span class="fw-bold text-center" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);">
                      <?php echo $tag . ' (' . $count . ')'; ?>
                    </span>
                  </div>
                </div>
              </a>
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
      function updatePlaceholder(input) {
        input.setAttribute('placeholder', input.value.trim() !== '' ? input.value.trim() : 'Search by tags or title');
      }
    </script>
    <script>
      function searchTag() {
        var searchInput = document.getElementById("search-input").value;
        window.location.href = "tags.php?tag=" + encodeURIComponent(searchInput);
      }

      document.addEventListener("DOMContentLoaded", function() {
        var images = document.querySelectorAll("img[data-src]");
        var observerOptions = {
          root: null,
          rootMargin: "0px",
          threshold: 0.1
        };

        var loadImage = function(image) {
          image.setAttribute("src", image.getAttribute("data-src"));
          image.onload = function() {
            image.removeAttribute("data-src");
          };
        };

        var observer = new IntersectionObserver(function(entries, observer) {
          entries.forEach(function(entry) {
            if (entry.isIntersecting) {
              loadImage(entry.target);
              observer.unobserve(entry.target);
            }
          });
        }, observerOptions);

        images.forEach(function(image) {
          observer.observe(image);
        });
      });
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
