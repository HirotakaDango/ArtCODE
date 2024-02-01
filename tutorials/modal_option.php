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
              <h1 class="modal-title fs-5">3. Modal Menu Option</h1>
            </div>
            <div class="modal-body py-0">
              <div class="row">
                <div class="col-md-4">
                  <img class="w-100 h-100 object-fit-contain rounded shadow" src="examples/chrome_screenshot_2024_01_31 1_12_57 GMT+07_00.png">
                </div>
                <div class="col-md-8 mt-4">
                  <ul>
                    <li>
                      <strong>Option 1:</strong> Upload Images
                      <p>
                        To upload images, click on the <a class="text-decoration-none link-dark fw-bold" href="/upload/" target="_blank">upload</a> link. This will open a new window where you can select and upload your desired images.
                      </p>
                    </li>
                    <li>
                      <strong>Option 2:</strong> Favorites Section
                      <p>
                        If you want to manage your favorite content, navigate to the <a class="text-decoration-none link-dark fw-bold" href="/feeds/favorites/" target="_blank">favorites</a> section. Here, you can view, organize, and interact with your favorite items.
                      </p>
                    </li>
                    <li>
                      <strong>Option 3:</strong> Album Section
                      <p>
                        Access your personalized albums by clicking on the <a class="text-decoration-none link-dark fw-bold" href="/album.php" target="_blank">album</a> link. This section allows you to create, edit, and organize albums to better manage your content.
                      </p>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="d-flex">
              <a class="ms-auto link-primary text-decoration-none" href="/tutorials/header_option.php">next</a>
            </div>
          </div>
        </div>
        <div class="position-absolute start-50 top-50 translate-middle w-100 d-none d-md-block" style="max-width: 950px;">
          <div class="rounded-4 shadow border-0 bg-body-tertiary p-4">
            <div class="modal-header border-0 mb-2">
              <h1 class="modal-title fs-5">3. Modal Menu Option</h1>
            </div>
            <div class="modal-body py-0">
              <div class="row">
                <div class="col-md-4">
                  <img class="w-100 h-100 object-fit-contain rounded shadow" src="examples/chrome_screenshot_2024_01_31 1_12_57 GMT+07_00.png">
                </div>
                <div class="col-md-8">
                  <ul>
                    <li>
                      <strong>Option 1:</strong> Upload Images
                      <p>
                        To upload images, click on the <a class="text-decoration-none link-dark fw-bold" href="/upload/" target="_blank">upload</a> link. This will open a new window where you can select and upload your desired images.
                      </p>
                    </li>
                    <li>
                      <strong>Option 2:</strong> Favorites Section
                      <p>
                        If you want to manage your favorite content, navigate to the <a class="text-decoration-none link-dark fw-bold" href="/feeds/favorites/" target="_blank">favorites</a> section. Here, you can view, organize, and interact with your favorite items.
                      </p>
                    </li>
                    <li>
                      <strong>Option 3:</strong> Album Section
                      <p>
                        Access your personalized albums by clicking on the <a class="text-decoration-none link-dark fw-bold" href="/album.php" target="_blank">album</a> link. This section allows you to create, edit, and organize albums to better manage your content.
                      </p>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <br>
            <a class="position-absolute end-0 bottom-0 m-3 link-primary text-decoration-none" href="/tutorials/header_option.php">next</a>
          </div>
        </div>
      </div>
    </main>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>