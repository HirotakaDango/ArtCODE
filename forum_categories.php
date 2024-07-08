        <div class="container">
          <button type="button" class="btn bg-body-tertiary rounded-4 border-0 link-body-emphasis mb-1 w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#categoryModal">
            see all categories
          </button>
        </div>
        <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
            <div class="modal-content rounded-4 border-0">
              <div class="modal-header border-0">
                <h1 class="modal-title fs-5" id="categoryModal">All Categories</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body fw-medium">
                <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>
                  <div class="card p-3 rounded-3 border-0 shadow mt-2">
                    <a class="text-decoration-none link-body-emphasis" href="forum_category.php?q=<?php echo urlencode($row['category']); ?>">(<?php echo $row['post_count']; ?> posts) <?php echo str_replace('_', ' ', $row['category']); ?></a>
                  </div>
                <?php endwhile; ?>
              </div>
            </div>
          </div>
        </div>