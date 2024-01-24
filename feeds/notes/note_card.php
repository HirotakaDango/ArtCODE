    <div class="container-fluid my-4">
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-xxl-6 g-1">
        <?php foreach ($posts as $post): ?>
          <div class="col">
            <a class="card bg-body-tertiary border-0 rounded-4 shadow text-decoration-none" href="view.php?id=<?php echo $post['id'] ?>">
              <div class="card-body h-100 position-relative p-0">
                <h5 class="w-100 p-3 fw-bold text-start"><?php echo $post['title']; ?></h5>
                <div class="mt-5">
                  <small class="text-body-secondary position-absolute bottom-0 start-0 m-3 fw-medium">created at: <?php echo (new DateTime($post['date']))->format("Y/m/d"); ?></small>
                </div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>