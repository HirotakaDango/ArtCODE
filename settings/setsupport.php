<?php
require_once('../auth.php');
?>

    <main id="swup" class="transition-main">
    <?php include('setheader.php'); ?>
    <h5 class="ms-2 mt-2 text-secondary fw-bold"><i class="bi bi-headset"></i> Support & Help Center</h5>
    <div class="container-fluid">
      <div class="row row-cols-1 row-cols-md-3 g-2 fw-bold">
        <div class="col">
          <div class="card h-100 container p-4">
            <i class="bi bi-telephone-fill text-secondary text-center" style="font-size: 100px;"></i>
            <div class="card-body">
              <h5 class="card-title fw-bold">Contact Us</h5>
              <p class="card-text text-secondary">If you have any problem or issue, contact us.</p>
              <a class="btn btn-primary rounded-pill fw-bold" href="tel:">Contact Us</a>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card h-100 container p-4">
            <i class="bi bi-envelope-at-fill text-secondary text-center" style="font-size: 100px;"></i>
            <div class="card-body">
              <h5 class="card-title fw-bold">Email Us</h5>
              <p class="card-text text-secondary">If you have any problem or issue, send us a mail.</p>
              <a class="btn btn-primary rounded-pill fw-bold" href="mailto:" class="btn btn-primary">Email Us</a>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card h-100 container p-4">
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