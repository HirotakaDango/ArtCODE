    <div class="container-fluid px-1 mt-2">
      <?php if (empty($displayImages)): ?>
        <h5 class="position-absolute top-50 start-50 translate-middle fw-bold">No images found</h5>
      <?php else: ?>
        <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 g-1">
          <?php foreach ($displayImages as $image): ?>
            <div class="col">
              <div class="card border-0 rounded-4">
                <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal<?= $image['id']; ?>">
                  <div class="ratio ratio-1x1">
                    <img class="rounded object-fit-cover lazy-load" data-src="<?= $websiteUrl . '/' . $thumbPath . '/' . $image['filename']; ?>" alt="<?= $image['title']; ?>">
                  </div>
                </a>
              </div>
            </div>
            <?php include('view.php'); ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>