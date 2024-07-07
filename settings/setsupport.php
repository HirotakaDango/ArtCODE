<?php
require_once('../auth.php');
?>

    <main id="swup" class="transition-main">
      <?php include('setheader.php'); ?>
        <div class="container mt-4">
          <div class="d-md-none mb-4">
            <div class="d-flex">
              <a class="text-decoration-none text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" href="/settings/">
                <i class="bi bi-chevron-left" style="-webkit-text-stroke: 2px;"></i>
              </a>
            </div>
          </div>
          <h3 class="fw-bold mb-4">
            <i class="bi bi-headset"></i> Support & Help Center
          </h3>
          <div class="row row-cols-1 row-cols-md-3 g-2 fw-bold">
            <div class="col">
              <div class="card h-100 container p-4 bg-body-tertiary rounded-4 shadow-sm">
                <i class="bi bi-telephone-fill text-secondary text-center" style="font-size: 100px;"></i>
                <div class="card-body">
                  <h5 class="card-title fw-bold">Contact Us</h5>
                  <p class="card-text text-secondary">If you have any problem or issue, contact us.</p>
                  <a class="btn btn-primary rounded-pill fw-bold" href="tel:">Contact Us</a>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card h-100 container p-4 bg-body-tertiary rounded-4 shadow-sm">
                <i class="bi bi-envelope-at-fill text-secondary text-center" style="font-size: 100px;"></i>
                <div class="card-body">
                  <h5 class="card-title fw-bold">Email Us</h5>
                  <p class="card-text text-secondary">If you have any problem or issue, send us a mail.</p>
                  <a class="btn btn-primary rounded-pill fw-bold" href="mailto:" class="btn btn-primary">Email Us</a>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card h-100 container p-4 bg-body-tertiary rounded-4 shadow-sm">
                <i class="bi bi-chat-left-text-fill text-secondary text-center" style="font-size: 100px;"></i>
                <div class="card-body">
                  <h5 class="card-title fw-bold">Message Us</h5>
                  <p class="card-text text-secondary">If you have any problem or issue, send us a message.</p>
                  <a class="btn btn-primary rounded-pill fw-bold" href="sms:">Message Us</a>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="mt-5"></div>
      <?php include('end.php'); ?>
    </main>