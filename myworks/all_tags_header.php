<div class="px-2 d-flex">
  <button type="button" class="btn border-0 ms-auto fw-bold link-body-emphasis" data-bs-toggle="modal" data-bs-target="#allTagsModal">
    <i class="bi bi-filter-left fs-5" style="-webkit-text-stroke: 1px;"></i> All Tags
  </button>
</div>

<div class="modal fade" id="allTagsModal" tabindex="-1" aria-labelledby="allTagsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0">
      <div class="modal-body">
        <?php
        try {
          // Connect to the SQLite3 database
          $db = new SQLite3('../database.sqlite');
          
          // Get user ID from session (adjust if needed)
          $email = $_SESSION['email'];
          
          // SQL query to get all tags from the user's images
          $queryTags = "SELECT tags
                        FROM images
                        JOIN users ON images.email = users.email
                        WHERE users.id = :id";
          
          // Prepare the SQL statement
          $stmt = $db->prepare($queryTags);
          
          // Bind the id parameter
          $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
          
          // Execute the query
          $result = $stmt->execute();
          
          // Fetch the results into an array
          $tags = [];
          while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $tags[] = $row['tags'];
          }
          
          // Process tags
          $tagCounts = [];
          foreach ($tags as $tagList) {
            // Split tags by comma and trim whitespace
            $tagArray = array_map('trim', explode(',', $tagList));
            foreach ($tagArray as $tag) {
              if (!empty($tag)) {
                if (!isset($tagCounts[$tag])) {
                  $tagCounts[$tag] = 0;
                }
                $tagCounts[$tag]++;
              }
            }
          }
          
          // Sort the tags by count in descending order
          arsort($tagCounts);
          
        } catch (Exception $e) {
          // Handle any database connection or query errors
          $errorMessage = "Error: " . $e->getMessage();
        }
        ?>
      
        <?php if (isset($errorMessage)): ?>
          <div class="alert alert-danger">
            <?= htmlspecialchars($errorMessage) ?>
          </div>
        <?php endif; ?>
        <h6 class="fw-medium fw-bold" id="allTagsModalLabel">All Tags</h6>
        <?php if (!empty($tagCounts)): ?>
          <?php foreach ($tagCounts as $tag => $count): ?>
            <?php
              $escapedTag = htmlspecialchars($tag);
              $escapedCount = htmlspecialchars($count);
            ?>
            <a class="btn bg-body-secondary link-body-emphasis border-0 fw-medium d-flex justify-content-between align-items-center w-100 rounded mt-2 p-3" href="?by=<?php echo isset($_GET['by']) ? (strpos($_GET['by'], 'tagged_') === false ? 'tagged_' . $_GET['by'] : $_GET['by']) : 'newest'; ?>&tag=<?= urlencode($escapedTag) ?>">
              <div class="text-start d-flex justify-content-center gap-1">
                <?= $escapedTag ?>
              </div>
              <div class="text-end">
                <?= $escapedCount ?>
              </div>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>