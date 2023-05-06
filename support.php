<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Help Center</title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  </head>
  <body>
    <?php include('header.php'); ?>
    <h5 class="ms-2 mt-2 text-secondary fw-bold"><i class="bi bi-headset"></i> Support & Help Center</h5>
    <div class="container-fluid">
      <div class="row row-cols-1 row-cols-md-3 g-4 fw-bold">
        <div class="col">
          <div class="card h-100">
            <i class="bi bi-telephone-fill text-secondary text-center" style="font-size: 100px;"></i>
            <div class="card-body">
              <h5 class="card-title fw-bold">Contact Us</h5>
              <p class="card-text text-secondary">If you have any problem or issue, contact us.</p>
              <a class="btn btn-primary rounded-pill fw-bold" href="tel:">Contact Us</a>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card h-100">
            <i class="bi bi-envelope-at-fill text-secondary text-center" style="font-size: 100px;"></i>
            <div class="card-body">
              <h5 class="card-title fw-bold">Email Us</h5>
              <p class="card-text text-secondary">If you have any problem or issue, send us a mail.</p>
              <a class="btn btn-primary rounded-pill fw-bold" href="mailto:" class="btn btn-primary">Email Us</a>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card h-100">
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
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script> 
  </body>
</html>