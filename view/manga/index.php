<?php
require_once '../../auth.php';

try {
  // Require artworkid and page parameters.
  if (isset($_GET['artworkid']) && isset($_GET['page'])) {
    $image_id = $_GET['artworkid'];
    $page = (int) $_GET['page'];

    // Connect to the SQLite database.
    $db = new PDO('sqlite:../../database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get main image details.
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
    $image_details = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get image_child records.
    $query_child = "
        SELECT * 
        FROM image_child 
        WHERE image_id = :image_id
        ORDER BY id ASC
      ";
    $stmt_child = $db->prepare($query_child);
    $stmt_child->bindParam(':image_id', $image_id);
    $stmt_child->execute();
    $image_child = $stmt_child->fetchAll(PDO::FETCH_ASSOC);
  } else {
    echo '<p>Missing artworkid or page parameter.</p>';
    exit();
  }
} catch (PDOException $e) {
  echo '<p>Error: ' . $e->getMessage() . '</p>';
  exit();
}

// Build an array of all image paths.
// Page 1: the main image; subsequent pages: the children.
$allSlides = [];
$allSlides[] = '../../images/' . $image_details['filename'];
foreach ($image_child as $child) {
  $allSlides[] = '../../images/' . $child['filename'];
}
$totalPages = count($allSlides);

// We only want to render five images at a time starting at the current page.
$startIndex = max(0, $page - 1);
$segmentCount = 5;
$endIndex = min($totalPages, $startIndex + $segmentCount);
$segmentSlides = array_slice($allSlides, $startIndex, $endIndex - $startIndex);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($image_details['title']); ?></title>
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="icon" type="image/png" href="/icon/favicon.png" />
    <style>
      /* Basic text stroke */
      .text-stroke {
        -webkit-text-stroke: 1px;
      }
      /* Carousel image: full viewport height, contain */
      .mangaImage {
        width: 100%;
        height: 100vh;
        object-fit: contain;
      }
      @media (max-width: 767px) {
        .mangaImage {
          height: 100%;
        }
      }
      /* Small fixed counter outside the menu */
      #pageDisplay {
        font-size: 0.8rem;
        padding: 0.3rem 0.6rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
      }
      /* Offcanvas menu counter style */
      #carouselCounter {
        font-size: 1.25rem;
        padding: 0.5rem 1rem;
      }
      /* Make the carousel container clickable (but not the image itself) */
      .carousel-container {
        position: relative;
      }
      .clickLayer {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10;
      }
      /* Disable transition for instant slide change */
      .carousel-item {
        transition: none !important;
      }
    </style>
  </head>
  <body>
    <!-- Fixed counter outside the menu with download icon.
         The download icon is an anchor that will download the current image.
         Its href is updated via JavaScript based on the carousel current slide. -->
    <div class="position-fixed bottom-0 start-0 z-2 m-2">
      <h6 id="pageDisplay" class="bg-body-tertiary p-2 rounded">
        <a id="downloadCurrent" download class="text-decoration-none text-reset">
          <i class="bi bi-download"></i>
        </a>
        <span id="pageText"><?php echo $page . ' / ' . $totalPages; ?></span>
      </h6>
    </div>

    <!-- Offcanvas menu trigger -->
    <div class="position-fixed bottom-0 end-0 z-2">
      <button class="btn bg-body-tertiary border-0 link-body-emphasis fw-bold m-2"
              data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu" aria-controls="offcanvasMenu">
        <i class="bi bi-list text-stroke"></i> Menu
      </button>
    </div>

    <!-- Carousel container with a separate click layer for edge navigation -->
    <div class="d-flex justify-content-center align-items-center" style="height:100vh;">
      <div class="w-100 carousel-container">
        <!-- Render only the current segment (max 5 images) -->
        <div id="mangaCarousel" class="carousel" data-bs-interval="false" data-bs-touch="true">
          <div class="carousel-inner" id="carouselInner">
            <?php foreach ($segmentSlides as $index => $src): 
              // The first slide in the segment is active.
              $active = ($index === 0) ? 'active' : '';
            ?>
              <div class="carousel-item <?php echo $active; ?>">
                <img src="<?php echo htmlspecialchars($src); ?>" class="d-block w-100 mangaImage" alt="<?php echo htmlspecialchars($image_details['title']); ?>" />
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <!-- Transparent layer to capture clicks for edge navigation -->
        <div class="clickLayer"></div>
      </div>
    </div>

    <!-- Offcanvas Menu with navigation buttons and counter -->
    <div class="offcanvas offcanvas-end border-0" tabindex="-1" id="offcanvasMenu" aria-labelledby="offcanvasMenuLabel">
      <div class="container">
        <div class="d-flex justify-content-center align-items-center vh-100">
          <div class="w-100">
            <div class="d-flex justify-content-center align-items-center container my-3">
              <!-- Navigation buttons -->
              <button id="btn-first" class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium me-auto" type="button">
                <i class="bi bi-chevron-double-left text-stroke"></i>
              </button>
              <button id="btn-prev" class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium me-auto" type="button">
                <i class="bi bi-chevron-left text-stroke"></i>
              </button>
              <span id="carouselCounter" class="pt-1"><?php echo $page . ' / ' . $totalPages; ?></span>
              <button id="btn-next" class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium ms-auto" type="button">
                <i class="bi bi-chevron-right text-stroke"></i>
              </button>
              <button id="btn-last" class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium ms-auto" type="button">
                <i class="bi bi-chevron-double-right text-stroke"></i>
              </button>
            </div>
            <div class="container my-3">
              <button type="button" class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium d-flex justify-content-between align-items-center w-100" data-bs-toggle="modal" data-bs-target="#pageModal">
                <div class="text-start d-flex justify-content-center gap-1">
                  Page <span id="modalPage"><?php echo $page; ?></span>
                </div>
                <div class="text-end">
                  <i class="bi bi-chevron-down text-stroke"></i>
                </div>
              </button>
            </div>
            <div class="container my-3">
              <a class="btn w-100 bg-body-tertiary link-body-emphasis fw-bold" href="/download_images.php?artworkid=<?php echo urlencode($image_id); ?>">Download Batch</a>
            </div>
            <div class="container my-3">
              <button type="button" class="btn w-100 bg-body-tertiary link-body-emphasis fw-bold" data-bs-dismiss="offcanvas">Close Menu</button>
            </div>
            <div class="container my-3">
              <a class="btn w-100 bg-body-tertiary link-body-emphasis fw-bold" href="/image.php?artworkid=<?php echo urlencode($image_id); ?>">Back to Artwork</a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal for All Pages -->
    <div class="modal fade" id="pageModal" tabindex="-1" aria-labelledby="pageModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow border-0">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="pageModalLabel">All Pages</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <a class="w-100 btn btn-outline-light border-0 fw-bold p-3 text-start my-1 <?php echo ($i === $page) ? 'active' : ''; ?>" href="?artworkid=<?php echo urlencode($image_id); ?>&page=<?php echo $i; ?>">
                Page <?php echo $i; ?>
              </a>
            <?php endfor; ?>
          </div>
        </div>
      </div>
    </div>

    <?php include('../../bootstrapjs.php'); ?>

    <!-- JavaScript: dynamic segment loading, instant slide transitions,
         History API pushState for swipe/back gestures, and edge-click navigation -->
    <script>
      (function() {
        // All image paths passed from PHP.
        var allSlides = <?php echo json_encode($allSlides); ?>;
        var totalPages = allSlides.length;
        var segmentSize = 5;
        // currentSegmentStart: 1-based page number of first image in current segment.
        var currentSegmentStart = <?php echo $page; ?>;
        // currentOverall: current page overall.
        var currentOverall = <?php echo $page; ?>;
        
        // Reference elements.
        var carouselEl = document.getElementById('mangaCarousel');
        var carouselInnerEl = document.getElementById('carouselInner');
        var pageDisplayEl = document.getElementById('pageText');
        var carouselCounterEl = document.getElementById('carouselCounter');
        var modalPageEl = document.getElementById('modalPage');
        var downloadLink = document.getElementById('downloadCurrent');
        var clickLayer = document.querySelector('.clickLayer');
        
        // Initialize Bootstrap carousel with instant transition (transition disabled via CSS).
        var carouselInstance = new bootstrap.Carousel(carouselEl, {
          interval: false,
          wrap: false
        });
        
        // Update the history state so the URL reflects the current overall page.
        function updateHistory(newPage) {
          var currentUrl = new URL(window.location);
          currentUrl.searchParams.set('page', newPage);
          // Only push state if it is different from current state.
          if (!history.state || history.state.page !== newPage) {
            history.pushState({page: newPage}, '', currentUrl);
          }
        }
        
        // Function: update counters, download link, and history.
        function updateCounters(overall) {
          pageDisplayEl.textContent = overall + ' / ' + totalPages;
          carouselCounterEl.textContent = overall + ' / ' + totalPages;
          modalPageEl.textContent = overall;
          downloadLink.setAttribute('href', allSlides[overall - 1]);
          updateHistory(overall);
        }
        
        // Function: rebuild the carousel with images from segmentStart to segmentEnd.
        function renderSegment(segmentStart, activeIndex) {
          currentSegmentStart = segmentStart;
          var segmentEnd = Math.min(totalPages, segmentStart + segmentSize - 1);
          var html = '';
          for (var i = segmentStart - 1; i < segmentEnd; i++) {
            var activeClass = (i === (segmentStart - 1 + activeIndex)) ? 'active' : '';
            html += '<div class="carousel-item ' + activeClass + '">';
            html += '<img src="' + allSlides[i] + '" class="d-block w-100 mangaImage" alt="" />';
            html += '</div>';
          }
          carouselInnerEl.innerHTML = html;
          // Dispose and reinitialize to apply the instant transition settings.
          carouselInstance.dispose();
          carouselInstance = new bootstrap.Carousel(carouselEl, {
            interval: false,
            wrap: false
          });
          currentOverall = segmentStart + activeIndex;
          updateCounters(currentOverall);
        }
        
        // When a slide transition completes, update the counters and history.
        carouselEl.addEventListener('slid.bs.carousel', function(e) {
          var newIndex = e.to;
          currentOverall = currentSegmentStart + newIndex;
          updateCounters(currentOverall);
        });
        
        // Edge click handling for navigation.
        clickLayer.addEventListener('click', function(e) {
          var rect = clickLayer.getBoundingClientRect();
          var clickX = e.clientX - rect.left;
          var width = rect.width;
          // Left edge click.
          if (clickX < width * 0.25) {
            if (currentOverall === 1) {
              window.location.href = "/image.php?artworkid=<?php echo urlencode($image_id); ?>";
            } else {
              var activeItems = carouselEl.querySelectorAll('.carousel-item');
              var activeIndex = Array.from(activeItems).findIndex(function(item) {
                return item.classList.contains('active');
              });
              if (activeIndex === 0) {
                var newSegmentStart = Math.max(1, currentSegmentStart - segmentSize);
                var newActiveIndex = Math.min(segmentSize - 1, currentOverall - newSegmentStart);
                renderSegment(newSegmentStart, newActiveIndex);
              } else {
                carouselInstance.prev();
              }
            }
          }
          // Right edge click.
          else if (clickX > width * 0.75) {
            if (currentOverall === totalPages) {
              window.location.href = "/image.php?artworkid=<?php echo urlencode($image_id); ?>";
            } else {
              var activeItems = carouselEl.querySelectorAll('.carousel-item');
              var activeIndex = Array.from(activeItems).findIndex(function(item) {
                return item.classList.contains('active');
              });
              if (activeIndex === activeItems.length - 1) {
                var newSegmentStart = currentOverall + 1;
                renderSegment(newSegmentStart, 0);
              } else {
                carouselInstance.next();
              }
            }
          }
        });
        
        // Offcanvas menu button events.
        document.getElementById('btn-first').addEventListener('click', function() {
          if (currentOverall !== 1) {
            renderSegment(1, 0);
          } else {
            window.location.href = "/image.php?artworkid=<?php echo urlencode($image_id); ?>";
          }
        });
        
        document.getElementById('btn-prev').addEventListener('click', function() {
          var activeItems = carouselEl.querySelectorAll('.carousel-item');
          var activeIndex = Array.from(activeItems).findIndex(function(item) {
            return item.classList.contains('active');
          });
          if (activeIndex === 0) {
            if (currentOverall === 1) {
              window.location.href = "/image.php?artworkid=<?php echo urlencode($image_id); ?>";
            } else {
              var newSegmentStart = Math.max(1, currentSegmentStart - segmentSize);
              var newActiveIndex = Math.min(segmentSize - 1, currentOverall - newSegmentStart);
              renderSegment(newSegmentStart, newActiveIndex);
            }
          } else {
            carouselInstance.prev();
          }
        });
        
        document.getElementById('btn-next').addEventListener('click', function() {
          var activeItems = carouselEl.querySelectorAll('.carousel-item');
          var activeIndex = Array.from(activeItems).findIndex(function(item) {
            return item.classList.contains('active');
          });
          if (activeIndex === activeItems.length - 1) {
            if (currentOverall === totalPages) {
              window.location.href = "/image.php?artworkid=<?php echo urlencode($image_id); ?>";
            } else {
              var newSegmentStart = currentOverall + 1;
              renderSegment(newSegmentStart, 0);
            }
          } else {
            carouselInstance.next();
          }
        });
        
        document.getElementById('btn-last').addEventListener('click', function() {
          if (currentOverall !== totalPages) {
            var newSegmentStart = Math.max(1, totalPages - segmentSize + 1);
            var newActiveIndex = totalPages - newSegmentStart;
            renderSegment(newSegmentStart, newActiveIndex);
          } else {
            window.location.href = "/image.php?artworkid=<?php echo urlencode($image_id); ?>";
          }
        });
        
        // Listen for popstate events so that the back/forward gesture navigates correctly.
        window.addEventListener('popstate', function(e) {
          if (e.state && typeof e.state.page === 'number') {
            var newPage = e.state.page;
            if (newPage !== currentOverall) {
              // If the newPage falls within the current segment, simply slide there.
              if (newPage >= currentSegmentStart && newPage < currentSegmentStart + segmentSize) {
                var slideIndex = newPage - currentSegmentStart;
                carouselInstance.to(slideIndex);
              } else {
                // Otherwise, re-render a new segment where the newPage is the first slide.
                renderSegment(newPage, 0);
              }
            }
          }
        });
        
        // Initialize the download link and history on page load.
        updateCounters(currentOverall);
        // Replace the initial history state.
        history.replaceState({page: currentOverall}, '', new URL(window.location));
      })();
    </script>
  </body>
</html>