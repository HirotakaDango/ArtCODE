<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tutorials</title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="../swup/transitions.css" />
    <script type="module" src="../swup/swup.js"></script>
  </head>
  <body>
    <main id="swup" class="transition-main swup-container">
      <div class="container">
        <div class="d-md-none card my-4 rounded-4 bg-body-tertiary border-0 shadow">
          <div class="p-4">
            <div class="modal-header border-0 mb-2">
              <h1 class="modal-title fs-5">Tutorials</h1>
            </div>
            <div class="modal-body py-0">
              <p>Welcome to our website! Before you start exploring all the amazing features and functionalities it has to offer, let's take a moment to dive into this comprehensive tutorial together. This guide is designed to help you navigate through various options and make the most out of your experience on our platform.</p>
            </div>
            <div class="d-flex mt-2" style="margin: -5px;">
              <a class="me-auto link-primary text-decoration-none" href="/setup/">skip to setup</a>
              <a class="ms-auto link-primary text-decoration-none" href="/tutorials/beginners_guide.php">continue to next tutorials</a>
            </div>
          </div>
        </div>
        <div class="position-absolute start-50 top-50 translate-middle w-100 d-none d-md-block" style="max-width: 750px;">
          <div class="rounded-4 shadow border-0 bg-body-tertiary p-4">
            <div class="modal-header border-0 mb-2">
              <h1 class="modal-title fs-5">Tutorials</h1>
            </div>
            <div class="modal-body py-0">
              <p>Welcome to our website! Before you start exploring all the amazing features and functionalities it has to offer, let's take a moment to dive into this comprehensive tutorial together. This guide is designed to help you navigate through various options and make the most out of your experience on our platform.</p>
            </div>
            <br>
            <div class="d-flex" style="margin: -5px;">
              <a class="me-auto link-primary text-decoration-none" href="/setup/">skip to setup</a>
              <a class="ms-auto link-primary text-decoration-none" href="/tutorials/beginners_guide.php">continue to next tutorials</a>
            </div>
          </div>
        </div>
      </div>
    </main>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>