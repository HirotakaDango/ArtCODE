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
              <h1 class="modal-title fs-5">11. Limitations</h1>
            </div>
            <div class="modal-body py-0">
              <ul>
                <li>
                  <strong>Upload Limits:</strong>
                  <p>
                    There are certain limits to consider when uploading images. Please keep in mind the following constraints:
                  </p>
                  <ul>
                    <li>
                      <strong>Limit 1:</strong> You can't upload more than 20 images.
                    </li>
                    <li>
                      <strong>Limit 2:</strong> Multiple or each uploaded image must be under 20 MB in size.
                    </li>
                    <li>
                      <strong>Limit 3:</strong> Exceeding these limits may result in failed uploads to the server.
                    </li>
                  </ul>
                  <p>
                    These limitations ensure efficient use of resources and optimal performance on our platform.
                  </p>
                </li>
                <li>
                  <strong>Exceeding Limits:</strong>
                  <p>
                    If you find yourself reaching these upload limits, we recommend the following solution:
                  </p>
                  <p>
                    <strong>Recommended Action:</strong> <a class="text-decoration-none link-dark fw-bold" href="/edit/upload.php" target="_blank">Upload New Images</a>
                  </p>
                  <p>
                    To add more images beyond the limit, consider <a class="text-decoration-none link-dark fw-bold" href="/edit/upload.php" target="_blank">uploading new images</a> and associating them with the current image ID. This allows you to seamlessly expand your collection without encountering upload restrictions.
                  </p>
                </li>
              </ul>
            </div>
            <div class="d-flex">
              <a class="ms-auto link-primary text-decoration-none" href="/tutorials/end_of_tutorials.php">next</a>
            </div>
          </div>
        </div>
        <div class="position-absolute start-50 top-50 translate-middle w-100 d-none d-md-block" style="max-width: 950px;">
          <div class="rounded-4 shadow border-0 bg-body-tertiary p-4">
            <div class="modal-header border-0 mb-2">
              <h1 class="modal-title fs-5">11. Limitations</h1>
            </div>
            <div class="modal-body py-0">
              <ul>
                <li>
                  <strong>Upload Limits:</strong>
                  <p>
                    There are certain limits to consider when uploading images. Please keep in mind the following constraints:
                  </p>
                  <ul>
                    <li>
                      <strong>Limit 1:</strong> You can't upload more than 20 images.
                    </li>
                    <li>
                      <strong>Limit 2:</strong> Multiple or each uploaded image must be under 20 MB in size.
                    </li>
                    <li>
                      <strong>Limit 3:</strong> Exceeding these limits may result in failed uploads to the server.
                    </li>
                  </ul>
                  <p>
                    These limitations ensure efficient use of resources and optimal performance on our platform.
                  </p>
                </li>
                <li>
                  <strong>Exceeding Limits:</strong>
                  <p>
                    If you find yourself reaching these upload limits, we recommend the following solution:
                  </p>
                  <p>
                    <strong>Recommended Action:</strong> <a class="text-decoration-none link-dark fw-bold" href="/edit/upload.php" target="_blank">Upload New Images</a>
                  </p>
                  <p>
                    To add more images beyond the limit, consider <a class="text-decoration-none link-dark fw-bold" href="/edit/upload.php" target="_blank">uploading new images</a> and associating them with the current image ID. This allows you to seamlessly expand your collection without encountering upload restrictions.
                  </p>
                </li>
              </ul>
            </div>
            <br>
            <a class="position-absolute end-0 bottom-0 m-3 link-primary text-decoration-none" href="/tutorials/end_of_tutorials.php">next</a>
          </div>
        </div>
      </div>
    </main>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>