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
    
    $url_comment = "../../comments_preview.php?imageid=" . $image_id;
    $url_preview = "preview.php?artworkid=" . $image_id;
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

      html.is-changing .transition-main {
        transition: opacity 2ms ease-in-out;
      }
        
      html.is-animating .transition-main {
        opacity: 0;
      }

      .offcanvas-backdrop {
        box-shadow: none !important;
        background-color: transparent !important;
      }
    </style>
  </head>
  <body>
    <div class="bg-dark-subtle">
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

                // Previous page link with an assigned id
                if ($currentPage > 1) {
                  $prevPage = $currentPage - 1;
                  echo '<a id="prevPageLink" class="position-absolute top-0 start-0 w-25 h-100 text-decoration-none" href="?artworkid=' . urlencode($image_id) . '&page=' . $prevPage . '"></a>';
                } else {
                  echo '<a id="prevPageLink" class="position-absolute top-0 start-0 w-25 h-100 text-decoration-none" href="/image.php?artworkid=' . $_GET['artworkid'] . '"></a>';
                }
              ?>
              <!-- Preload previous image (invisible for swup transition) -->
              <img class="d-none" src="<?= $prevRender; ?>" alt="<?= $image_details['title']; ?>">
              <!-- Main manga image -->
              <img class="mangaImage" id="mainMangaImage" src="<?= $imageSource; ?>" alt="<?= $image_details['title']; ?>">
              <!-- Preload next image (invisible for swup transition) -->
              <img class="d-none" src="<?= $nextRender; ?>" alt="<?= $image_details['title']; ?>">
              <?php
                // Next page link with an assigned id
                if ($currentPage < $totalPages) {
                  $nextPage = $currentPage + 1;
                  echo '<a id="nextPageLink" class="position-absolute top-0 end-0 w-25 h-100 text-decoration-none" href="?artworkid=' . urlencode($image_id) . '&page=' . $nextPage . '"></a>';
                } else {
                  echo '<a id="nextPageLink" class="position-absolute top-0 end-0 w-25 h-100 text-decoration-none" href="/image.php?artworkid=' . $_GET['artworkid'] . '"></a>';
                }
              ?>
            </div>
          </main>
        </div>
      </div>
    </div>
    <div class="position-fixed bottom-0 start-0 z-2 m-2 ms-3">
      <h6 class="small d-flex"><main id="swup" class="transition-main me-1"><?php echo $currentPage; ?></main> / <?php echo $totalPages; ?></h6>
    </div>
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
              function isActive($pageNumber, $currentPage) {
                return $pageNumber == $currentPage ? 'active' : '';
              }
            ?>
            <main id="swup" class="transition-main">
              <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                <a class="w-100 btn btn-outline-light border-0 fw-bold p-3 text-start my-1 <?= isActive($i, $currentPage) ?>" href="?artworkid=<?= urlencode($image_id) ?>&page=<?= $i ?>">
                  Page <?= $i ?>
                </a>
              <?php endfor; ?>
            </main>
          </div>
        </div>
      </div>
    </div>
    <div class="offcanvas offcanvas-end border-0 rounded-start-4 bg-dark" tabindex="-1" id="offcanvasMenu" aria-labelledby="offcanvasMenuLabel" style="box-shadow: none;">
      <div class="container">
        <div class="d-flex justify-content-center align-items-center vh-100">
          <div class="w-100">
            <h5 class="fw-bold mb-3 px-3 pb-3"><?= $image_details['title']; ?></h5>
            <div class="d-flex justify-content-center align-items-center container my-2">
              <?php
                echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?artworkid=' . urlencode($image_id) . '&page=1"><i class="bi bi-chevron-double-left text-stroke"></i></a></main>';
              
                if ($currentPage > 1) {
                  $prevPage = $currentPage - 1;
                  echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?artworkid=' . urlencode($image_id) . '&page=' . $prevPage . '"><i class="bi bi-chevron-left text-stroke"></i></a></main>';
                } else {
                  echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="/image.php?artworkid=' . $_GET['artworkid'] . '"><i class="bi bi-reply-fill"></i></a></main>';
                }
              
                echo '<main id="swup" class="transition-main"><h6 class="pt-1">' . $currentPage . ' / ' . $totalPages . '</h6></main>';
              
                if ($currentPage < $totalPages) {
                  $nextPage = $currentPage + 1;
                  echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?artworkid=' . urlencode($image_id) . '&page=' . $nextPage . '"><i class="bi bi-chevron-right text-stroke"></i></a></main>';
                } else {
                  echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="/image.php?artworkid=' . $_GET['artworkid'] . '"><i class="bi bi-reply-fill"></i></a></main>';
                }
              
                echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?artworkid=' . urlencode($image_id) . '&page=' . $totalPages . '"><i class="bi bi-chevron-double-right text-stroke"></i></a></main>';
              ?>
            </div>
            <div class="container my-2">
              <button type="button" class="btn p-3 bg-body-tertiary link-body-emphasis border-0 fw-medium d-flex justify-content-between align-items-center w-100" data-bs-toggle="modal" data-bs-target="#pageModal">
                <div class="text-start d-flex justify-content-center gap-1">
                  Page <main id="swup" class="transition-main "><?php echo $currentPage; ?></main>
                </div>
                <div class="text-end">
                  <i class="bi bi-chevron-down text-stroke"></i>
                </div>
              </button>
            </div>
            <div class="container my-2">
              <button type="button" class="btn w-100 p-3 text-start bg-body-tertiary link-body-emphasis fw-bold" data-bs-toggle="modal" data-bs-target="#commentsModal">
                <i class="bi bi-chat-square-text me-2"></i> View Comments
              </button>
            </div>
            <div class="container my-2">
              <a class="btn w-100 p-3 text-start bg-body-tertiary link-body-emphasis fw-bold" href="<?= $imageSource; ?>" download>
                <i class="bi bi-download me-2"></i> Download Current Image
              </a>
            </div>
            <div class="container my-2">
              <a class="btn w-100 p-3 text-start bg-body-tertiary link-body-emphasis fw-bold" href="/download_images.php?artworkid=<?= urlencode($image_id) ?>">
                <i class="bi bi-file-earmark-arrow-down me-2"></i> Download Batch
              </a>
            </div>
            <div class="container my-2">
              <button type="button" class="btn w-100 p-3 text-start bg-body-tertiary link-body-emphasis fw-bold" data-bs-toggle="modal" data-bs-target="#previewMangaModal">
                All Previews
              </button>
            </div>
            <div class="container my-2">
              <button type="button" class="btn w-100 p-3 text-start bg-body-tertiary link-body-emphasis fw-bold" data-bs-dismiss="offcanvas">
                Close Menu
              </button>
            </div>
            <div class="container my-2">
              <button type="button" class="btn w-100 p-3 text-start bg-body-tertiary link-body-emphasis fw-bold" onclick="window.location.href='/image.php?artworkid=<?= urlencode($image_id) ?>'">
                Back to Artwork
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include('view_comments_modal.php'); ?>
    <?php include('preview_modal.php'); ?>
    <script>
      document.addEventListener('keydown', function(e) {
        // Skip if focused on an input or textarea
        if (['input', 'textarea'].includes(e.target.tagName.toLowerCase())) return;
        
        if (e.key === 'ArrowLeft') {
          const prevLink = document.getElementById('prevPageLink');
          if (prevLink) {
            // Trigger click for swup transition
            prevLink.click();
          }
        } else if (e.key === 'ArrowRight') {
          const nextLink = document.getElementById('nextPageLink');
          if (nextLink) {
            // Trigger click for swup transition
            nextLink.click();
          }
        }
      });
    </script>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>