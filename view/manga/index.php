<?php
require_once('../../auth.php');

try {
  // Check if artworkid parameter is provided
  if (isset($_GET['artworkid']) && isset($_GET['page'])) {
    $image_id = $_GET['artworkid'];
    $page = $_GET['page'];
    
    // Connect to the SQLite database
    $db = new PDO('sqlite:../../database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Query to get the image details from the images table
    $query = "
      SELECT 
        images.*, 
        users.id AS userid, 
        users.artist
      FROM images
      JOIN users ON images.email = users.email
      WHERE images.id = :image_id
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':image_id', $image_id);
    $stmt->execute();
    
    // Fetch the result
    $image_details = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Query to get image_child for the current image ID
    $query_child = "
      SELECT * 
      FROM image_child 
      WHERE image_id = :image_id
    ";
    $stmt_child = $db->prepare($query_child);
    $stmt_child->bindParam(':image_id', $image_id);
    $stmt_child->execute();
    
    // Fetch all image_child results
    $image_child = $stmt_child->fetchAll(PDO::FETCH_ASSOC);
  } else {
    echo '<p>Missing artworkid or page parameter.</p>';
    exit;
  }
} catch (PDOException $e) {
  echo '<p>Error: ' . $e->getMessage() . '</p>';
  exit;
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $image_details['title']; ?></title>
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <link rel="stylesheet" href="../../swup/transitions.css" />
    <script type="module" src="../../swup/swup.js"></script>
    <style>
      .text-stroke {
        -webkit-text-stroke: 1px;
      }

      @media (max-width: 767px) {
        .vh-100-sm {
          height: 100vh;
        }

        .mangaImage {
          height: 100%;
          width: 100%;
          object-fit: contain;
        }
      }

      @media (min-width: 768px) {
        .mangaImage {
          height: 100vh;
        }
      }
    </style>
  </head>
  <body>
    <div class="">
      <div class="position-fixed bottom-0 end-0 z-2">
        <a class="btn bg-body-tertiary border-0 link-body-emphasis fw-bold m-2" data-bs-toggle="offcanvas" href="#offcanvasMenu" role="button" aria-controls="offcanvasMenu">
          <i class="bi bi-list text-stroke"></i> Menu
        </a>
      </div>
      <div class="d-flex justify-content-center align-items-center vh-100-sm">
        <div class="w-100">
          <?php
          // Determine the previous and next image sources
          $prevRender = ($page > 1) ? '../../images/' . ($page == 2 ? $image_details['filename'] : $image_child[$page - 3]['filename']) : '';
          $nextRender = ($page < count($image_child) + 1) ? '../../images/' . $image_child[$page - 1]['filename'] : '';

          // Determine the image source based on the page number
          $imageSource = ($page == 1) ? '../../images/' . $image_details['filename'] : '../../images/' . $image_child[$page - 2]['filename'];
          ?>
          <main id="swup" class="transition-main">
            <div class="position-relative d-flex justify-content-center w-100">
              <?php
                $totalPages = count($image_child) + 1;
                $currentPage = $page;

                // Previous page link
                if ($currentPage > 1) {
                  $prevPage = $currentPage - 1;
                  echo '<a class="position-absolute top-0 start-0 w-25 h-100 text-decoration-none" href="?artworkid=' . urlencode($image_id) . '&page=' . $prevPage . '"></a>';
                } else {
                  echo '<a class="position-absolute top-0 start-0 w-25 h-100 text-decoration-none" href="/image.php?artworkid=' . $_GET['artworkid'] . '"></a>';
                }
              ?>
              <img class="d-none" src="<?= htmlspecialchars($prevRender); ?>" alt="<?= htmlspecialchars($image_details['title']); ?>">
              <img class="mangaImage" id="mainMangaImage" src="<?= htmlspecialchars($imageSource); ?>" alt="<?= htmlspecialchars($image_details['title']); ?>">
              <img class="d-none" src="<?= htmlspecialchars($nextRender); ?>" alt="<?= htmlspecialchars($image_details['title']); ?>">
              <?php
                $totalPages = count($image_child) + 1;
                $currentPage = $page;

                // Next page link
                if ($currentPage < $totalPages) {
                  $nextPage = $currentPage + 1;
                  echo '<a class="position-absolute top-0 end-0 w-25 h-100 text-decoration-none" href="?artworkid=' . urlencode($image_id) . '&page=' . $nextPage . '"></a>';
                } else {
                  echo '<a class="position-absolute top-0 end-0 w-25 h-100 text-decoration-none" href="/image.php?artworkid=' . $_GET['artworkid'] . '"></a>';
                }
              ?>
            </div>
          </main>
        </div>
      </div>
      <div class="position-fixed bottom-0 start-0 z-2">
        <a class="btn bg-body-tertiary border-0 link-body-emphasis fw-bold m-2" href="#">
          <main id="swup" class="transition-main"><h6 class="pt-1 small"><?php echo $currentPage . ' / ' . $totalPages; ?></h6></main>
        </a>
      </div>
    </div>
    <!-- Page Modal -->
    <div class="modal fade" id="pageModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow border-0">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="exampleModalLabel">All Pages</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <?php
              $totalPages = count($image_child) + 1;
              $currentPage = $page;
            
              // Function to determine if a button should be active
              function isActive($pageNumber, $currentPage) {
                return $pageNumber == $currentPage ? 'active' : '';
              }
            ?>
            <main id="swup" class="transition-main ">
              <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                <a class="w-100 btn btn-outline-light border-0 fw-bold p-3 text-start my-1 <?= isActive($i, $currentPage) ?>" href="?">
                  Page <?= $i ?>
                </a>
              <?php endfor; ?>
            </main>
          </div>
        </div>
      </div>
    </div>
    <!-- All Episodes Modal -->
    <div class="offcanvas offcanvas-end border-0" tabindex="-1" id="offcanvasMenu" aria-labelledby="offcanvasMenuLabel">
      <div class="container">
        <div class="d-flex justify-content-center align-items-center vh-100">
          <div class="w-100">
            <div class="d-flex justify-content-center align-items-center container my-3">
              <?php
                $totalPages = count($image_child) + 1;
                $currentPage = $page;
              
                echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?artworkid=' . $_GET['artworkid'] . '&page=1"><i class="bi bi-chevron-double-left text-stroke"></i></a></main>';
              
                // Previous page link
                if ($currentPage > 1) {
                  $prevPage = $currentPage - 1;
                  echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?artworkid=' . $_GET['artworkid'] . '&page=' . $prevPage . '"><i class="bi bi-chevron-left text-stroke"></i></a></main>';
                } else {
                  echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="/image.php?artworkid=' . $_GET['artworkid'] . '"><i class="bi bi-reply-fill"></i></a></main>';
                }
              
                echo '<main id="swup" class="transition-main"><h6 class="pt-1">' . $currentPage . ' / ' . $totalPages . '</h6></main>';
              
                // Next page link
                if ($currentPage < $totalPages) {
                  $nextPage = $currentPage + 1;
                  echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?artworkid=' . $_GET['artworkid'] . '&page=' . $nextPage . '"><i class="bi bi-chevron-right text-stroke"></i></a></main>';
                } else {
                  echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="/image.php?artworkid=' . $_GET['artworkid'] . '"><i class="bi bi-reply-fill"></i></a></main>';
                }
              
                echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?artworkid=' . $_GET['artworkid'] . '&page=' . $totalPages . '"><i class="bi bi-chevron-double-right text-stroke"></i></a></main>';
              ?>
            </div>
            <div class="container my-3">
              <button type="button" class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium d-flex justify-content-between align-items-center w-100" data-bs-toggle="modal" data-bs-target="#pageModal">
                <div class="text-start d-flex justify-content-center gap-1">
                  Page <main id="swup" class="transition-main "><?php echo $currentPage; ?></main>
                </div>
                <div class="text-end">
                  <i class="bi bi-chevron-down text-stroke"></i>
                </div>
              </button>
            </div>
            <div class="container my-3">
              <a class="btn w-100 bg-body-tertiary link-body-emphasis fw-bold" href="/download_images.php?artworkid=<?php echo $_GET['artworkid']; ?>">Download Batch</a>
            </div>
            <div class="container my-3">
              <button type="button" class="btn w-100 bg-body-tertiary link-body-emphasis fw-bold" data-bs-dismiss="offcanvas">Close Menu</button>
            </div>
            <div class="container my-3">
              <a class="btn w-100 bg-body-tertiary link-body-emphasis fw-bold" href="/image.php?artworkid=<?php echo $_GET['artworkid']; ?>">Back to Artwork</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>