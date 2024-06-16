        <div class="col-md-4 d-none d-md-block">
          <div class="card border-0 shadow mb-1 position-relative bg-body-tertiary rounded-4">
            <div class="card-body fw-medium">
              <h4>Categories</h4>
              <ul>
                <?php foreach ($categories as $category): ?>
                  <li>
                    <a class="text-decoration-none link-body-emphasis" href="category.php?q=<?php echo urlencode($category['category']); ?>"><?php echo str_replace('_', ' ', $category['category']); ?> (<?php echo $category['post_count']; ?> posts)</a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>
        <div class="container d-md-none">
          <button type="button" class="btn bg-body-tertiary rounded-4 border-0 link-body-emphasis mb-3 w-100 fw-bold d-md-none" data-bs-toggle="modal" data-bs-target="#categoryModal">
            see all categories
          </button>
        </div>
        <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0">
              <div class="modal-header border-0">
                <h1 class="modal-title fs-5" id="categoryModal">All Categories</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body fw-medium">
                <ul>
                  <?php foreach ($categories as $category): ?>
                    <li>
                      <a class="text-decoration-none link-body-emphasis" href="category.php?q=<?php echo urlencode($category['category']); ?>"><?php echo str_replace('_', ' ', $category['category']); ?> (<?php echo $category['post_count']; ?> posts)</a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          </div>
        </div>