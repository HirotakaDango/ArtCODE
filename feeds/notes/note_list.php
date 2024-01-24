        <div class="col-md-3 vh-100 bg-black d-none d-md-block d-lg-block container-fluid overflow-y-auto">
          <div class="btn-group gap-2 mt-3 w-100">
            <a class="w-50 btn bg-body-tertiary rounded-4 border-0 shadow link-body-emphasis w-100 fw-medium py-3" href="../notes/"><i class="bi bi-house-fill"></i> home</a>
            <a class="w-50 btn bg-body-tertiary rounded-4 border-0 shadow link-body-emphasis w-100 fw-medium py-3" href="upload.php"><i class="bi bi-pencil-fill"></i> new note</a>
          </div>
          <hr class="border border-3 rounded-pill">
          <h5 class="mb-3">All notes</h5>
          <?php foreach ($notes as $note): ?>
            <div class="card border-0 shadow bg-body-tertiary rounded-4 mt-2">
              <div class="card-body p-3 fw-medium position-relative">
                <a class="text-decoration-none link-body-emphasis" href="view.php?id=<?php echo urlencode($note['note_id']); ?>">
                  <h6 class="link-body-emphasis text-start fw-bold"><?php echo (!is_null($note['title']) && strlen($note['title']) > 20) ? substr($note['title'], 0, 20) . '...' : str_replace('_', ' ', $note['title']); ?></h6>
                  <div>
                    <h6 class="small">created at <?php echo (new DateTime($note['date']))->format("Y/m/d"); ?></h6>
                  </div>
                </a>
                <a class="btn border-0 link-body-emphasis position-absolute end-0 top-50 translate-middle-y me-2" href="edit.php?id=<?php echo $note['note_id'] ?>">
                  <i class="bi bi-pencil-fill"></i>
                </a>
              </div>
            </div>
          <?php endforeach; ?>
          <br>
        </div>