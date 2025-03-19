<?php
// Get the current server URL dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$url = $protocol . "://" . $_SERVER['HTTP_HOST'];

// Load the RSS feed from the current server
$rss = simplexml_load_file("$url/rss/index.php");
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE RSS Viewer</title>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <link rel="stylesheet" href="/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>    <style>
      .ratio-cover {
        position: relative;
        width: 100%;
        padding-bottom: 140%;
      }

      .ratio-cover img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
      }

      .text-stroke {
        -webkit-text-stroke: 1px;
      }
    </style>
  </head>
  <body>
    <div class="container my-5">
      <?php foreach ($rss->channel->item as $item): ?>
        <a class="text-decoration-none" href="<?php echo $item->link; ?>">
          <div class="row my-3">
            <div class="col-5">
              <div class="position-relative">
                <div class="ratio-cover rounded">
                  <img class="rounded shadow object-fit-cover" src="<?php echo $url . parse_url($item->enclosure['url'], PHP_URL_PATH); ?>" alt="<?php echo $item->title; ?>">
                </div>
              </div>
            </div>
            <div class="col-7 p-2">
              <div>
                <div class="text-truncate">
                  <h5 class="fw-bold text-truncate text-dark text-decoration-none"><?php echo $item->title; ?></h5>
                  <h6 class="my-3 fw-bold text-truncate text-dark text-decoration-none"><?php echo $item->description; ?></h6>
                  <small class="fw-medium link-body-emphasis">by <?php echo $item->author; ?></small>
                </div>
              </div>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </body>
</html>
