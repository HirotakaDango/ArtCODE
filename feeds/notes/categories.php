    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
          <div class="d-flex position-relative">
            <h6 class="fw-bold text-start me-auto ms-3 mt-3">Categories</h6>
            <button type="button" class="btn border-0 link-body-emphasis ms-auto me-1" data-bs-dismiss="modal"><i class="bi bi-x text-stroke"></i></button>
          </div>
          <div class="modal-body">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-1">
              <?php foreach ($categoriesHeader as $categoryHeader): ?>
                <div class="col">
                  <a class="btn bg-body-tertiary rounded-3 shadow link-body-emphasis w-100" href="category.php?q=<?php echo urlencode($categoryHeader['category_name']); ?>">
                    <h6 class="link-body-emphasis text-start"><i class="bi bi-tags-fill"></i> <?php echo (!is_null($categoryHeader['category_name']) && strlen($categoryHeader['category_name']) > 20) ? substr($categoryHeader['category_name'], 0, 20) . '...' : str_replace('_', ' ', $categoryHeader['category_name']); ?></h6>
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
            <a class="btn bg-body-tertiary rounded-3 shadow link-body-emphasis w-100 mt-3 fw-bold" href="new_category.php">Add new category</a>
          </div>
        </div>
      </div>
    </div>