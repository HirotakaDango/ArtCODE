<?php
require_once('../../../auth.php');

try {
  // Validate required parameters for artwork navigation
  if (isset($_GET['artworkid']) && isset($_GET['page'])) {
    $image_id = $_GET['artworkid'];
    $page = $_GET['page'];

    // Connect to the SQLite database
    $db = new PDO('sqlite:../../../database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query the current chapter details from the private_images table
    $query = "
      SELECT 
        private_images.*, 
        users.id AS userid, 
        users.artist
      FROM private_images
      JOIN users ON private_images.email = users.email
      WHERE private_images.id = :image_id
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':image_id', $image_id);
    $stmt->execute();
    $image_details = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$image_details) {
      echo '<p>Chapter not found.</p>';
      exit;
    }

    // Query for the child pages of the current chapter
    $query_child = "
      SELECT * 
      FROM private_image_child 
      WHERE image_id = :image_id
      ORDER BY id ASC
    ";
    $stmt_child = $db->prepare($query_child);
    $stmt_child->bindParam(':image_id', $image_id);
    $stmt_child->execute();
    $image_child = $stmt_child->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total pages for the current chapter
    $totalPages = count($image_child) + 1;
    $currentPage = $page;

    // Query to get all chapters for the same series (episode) and for the same artist (via users.email)
    $query_all_chapters = "
      SELECT 
        private_images.*, 
        users.id AS userid, 
        users.artist
      FROM private_images
      JOIN users ON private_images.email = users.email
      WHERE artwork_type = 'manga'
      AND episode_name = :episode_name
      AND private_images.email = :email
      ORDER BY private_images.id ASC
    ";
    $stmt_all_chapters = $db->prepare($query_all_chapters);
    $stmt_all_chapters->bindParam(':episode_name', $image_details['episode_name']);
    $stmt_all_chapters->bindParam(':email', $image_details['email']);
    $stmt_all_chapters->execute();
    $all_chapters = $stmt_all_chapters->fetchAll(PDO::FETCH_ASSOC);

    // Determine the index of the current chapter within the list of chapters
    $current_chapter_index = -1;
    foreach ($all_chapters as $index => $chapter) {
      if ($chapter['id'] == $image_details['id']) {
        $current_chapter_index = $index;
        break;
      }
    }

    // Prepare previous and next navigation links
    $backLink = '/image.php?artworkid=' . urlencode($image_id);
    // Previous link
    if ($currentPage > 1) {
      // Previous page within the same chapter
      $prevLink = '?artworkid=' . urlencode($image_id) . '&page=' . ($currentPage - 1);
    } elseif ($currentPage == 1 && $current_chapter_index > 0) {
      // At first page of the current chapter; use previous chapter
      $prevChapter = $all_chapters[$current_chapter_index - 1];
      // Get the total pages of the previous chapter
      $query_prev_count = "SELECT COUNT(*) AS total FROM private_image_child WHERE image_id = :prev_chapter_id";
      $stmt_prev_count = $db->prepare($query_prev_count);
      $stmt_prev_count->bindParam(':prev_chapter_id', $prevChapter['id']);
      $stmt_prev_count->execute();
      $prevCount = $stmt_prev_count->fetch(PDO::FETCH_ASSOC);
      $prevChapterTotalPages = $prevCount['total'] + 1;
      $prevLink = '?artworkid=' . urlencode($prevChapter['id']) . '&page=' . $prevChapterTotalPages;
    } else {
      $prevLink = $backLink;
    }

    // Next link
    if ($currentPage < $totalPages) {
      // Next page in the current chapter
      $nextLink = '?artworkid=' . urlencode($image_id) . '&page=' . ($currentPage + 1);
    } elseif ($currentPage == $totalPages && $current_chapter_index < count($all_chapters) - 1) {
      // At the last page of current chapter; use next chapter
      $nextChapter = $all_chapters[$current_chapter_index + 1];
      $nextLink = '?artworkid=' . urlencode($nextChapter['id']) . '&page=1';
    } else {
      $nextLink = $backLink;
    }

    // URLs for preview (if needed)
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
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($image_details['title']); ?></title>
    <?php include('../../../bootstrapcss.php'); ?>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <script type="module" src="../../../swup/swup.js"></script>
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
      .offcanvas-backdrop {
        box-shadow: none !important;
        background-color: transparent !important;
      }
    </style>
  </head>
  <body>
    <div class="bg-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>-subtle">
      <div class="position-fixed bottom-0 end-0 z-2">
        <a class="btn bg-body-tertiary border-0 link-body-emphasis fw-bold m-2 shadow-sm" data-bs-toggle="offcanvas" href="#offcanvasMenu" role="button" aria-controls="offcanvasMenu">
          <i class="bi bi-list text-stroke"></i> Menu
        </a>
      </div>
      <div class="d-flex justify-content-center align-items-center vh-100-sm">
        <div class="w-100">
          <?php
            // Preload private_images for previous and next (with chapter navigation applied)
            $prevRender = ($prevLink !== $backLink) ? '../../../private_images/' . (($currentPage > 1) ?
                           (($currentPage == 2) ? $image_details['filename'] : $image_child[$currentPage - 3]['filename'])
                           : $image_details['filename']) : '';
            $nextRender = ($nextLink !== $backLink) ? '../../../private_images/' . (($currentPage < $totalPages) ?
                           $image_child[$currentPage - 1]['filename']
                           : $image_details['filename']) : '';

            // Determine the image source based on the current page within the chapter
            $imageSource = ($currentPage == 1) ? '../../../private_images/' . $image_details['filename'] : '../../../private_images/' . $image_child[$currentPage - 2]['filename'];
          ?>
          <main id="swup" class="transition-main">
            <div class="position-relative d-flex justify-content-center w-100">
              <?php
                // Previous page link with assigned id
                echo '<a id="prevPageLink" class="position-absolute top-0 start-0 w-25 h-100 text-decoration-none" href="' . $prevLink . '"></a>';
              ?>
              <!-- Preload previous image (invisible, for transition) -->
              <img class="d-none" src="<?= htmlspecialchars($prevRender); ?>" alt="<?= htmlspecialchars($image_details['title']); ?>">
              <!-- Main manga image -->
              <img class="mangaImage" id="mainMangaImage" src="<?= htmlspecialchars($imageSource); ?>" alt="<?= htmlspecialchars($image_details['title']); ?>">
              <!-- Preload next image (invisible, for transition) -->
              <img class="d-none" src="<?= htmlspecialchars($nextRender); ?>" alt="<?= htmlspecialchars($image_details['title']); ?>">
              <?php
                // Next page link with assigned id
                echo '<a id="nextPageLink" class="position-absolute top-0 end-0 w-25 h-100 text-decoration-none" href="' . $nextLink . '"></a>';
              ?>
            </div>
          </main>
        </div>
      </div>
    </div>
    <div class="position-fixed bottom-0 start-0 z-2 m-2 ms-3">
      <h6 class="small d-flex">
        <main id="swup" class="transition-main me-1"><?php echo $currentPage; ?></main>
        / <?php echo $totalPages; ?>
      </h6>
    </div>
    <!-- Modal for All Chapters (Episodes) -->
    <div class="modal fade" id="allEpisodesModal" tabindex="-1" aria-labelledby="allEpisodesModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow border-0">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="allEpisodesModalLabel">All Episodes</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <?php
              if (is_array($all_chapters) && !empty($all_chapters)) {
                ?>
                <main id="swup" class="transition-main">
                  <?php foreach ($all_chapters as $chapter) : ?>
                    <a class="w-100 btn btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold p-3 text-start my-1 <?php echo ($chapter['id'] == $image_details['id']) ? 'active' : ''; ?>" href="?title=<?= urlencode($image_details['episode_name']) ?>&uid=<?= urlencode($image_details['userid']) ?>&artworkid=<?= urlencode($chapter['id']) ?>&page=1">
                      <?= htmlspecialchars($chapter['title']); ?>
                    </a>
                  <?php endforeach; ?>
                </main>
                <?php
              } else {
                echo '<p>No episodes found.</p>';
              }
            ?>
          </div>
        </div>
      </div>
    </div>
    <!-- Offcanvas Menu -->
    <div class="offcanvas offcanvas-end border-0 rounded-start-4 bg-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?> shadow-sm" tabindex="-1" id="offcanvasMenu" aria-labelledby="offcanvasMenuLabel" style="box-shadow: none; max-width: 300px;">
      <div class="container">
        <div class="d-flex justify-content-center align-items-center vh-100">
          <div class="w-100">
            <input type="text" class="form-control-plaintext fw-bold mb-3 px-3 pb-3 fs-3" value="<?= $image_details['title']; ?>" readonly>
            <div class="d-flex justify-content-center align-items-center container my-2">
              <?php
                echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis shadow-sm border-0 fw-medium" href="?artworkid=' . urlencode($image_id) . '&page=1"><i class="bi bi-chevron-double-left text-stroke"></i></a></main>';
                // Previous navigation button (using our computed prevLink)
                if ($currentPage > 1 || $current_chapter_index > 0) {
                  echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis shadow-sm border-0 fw-medium" href="' . $prevLink . '"><i class="bi bi-chevron-left text-stroke"></i></a></main>';
                } else {
                  echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis shadow-sm border-0 fw-medium" href="' . $backLink . '"><i class="bi bi-reply-fill"></i></a></main>';
                }
                echo '<main id="swup" class="transition-main"><h6 class="pt-1">' . $currentPage . ' / ' . $totalPages . '</h6></main>';
                // Next navigation button (using our computed nextLink)
                if ($currentPage < $totalPages || $current_chapter_index < count($all_chapters) - 1) {
                  echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis shadow-sm border-0 fw-medium" href="' . $nextLink . '"><i class="bi bi-chevron-right text-stroke"></i></a></main>';
                } else {
                  echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis shadow-sm border-0 fw-medium" href="' . $backLink . '"><i class="bi bi-reply-fill"></i></a></main>';
                }
                echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis shadow-sm border-0 fw-medium" href="?artworkid=' . urlencode($image_id) . '&page=' . $totalPages . '"><i class="bi bi-chevron-double-right text-stroke"></i></a></main>';
              ?>
            </div>
            <div class="container my-2">
              <button type="button" class="btn p-3 bg-body-tertiary link-body-emphasis shadow-sm border-0 fw-medium d-flex justify-content-between align-items-center w-100" data-bs-toggle="modal" data-bs-target="#pageModal">
                <div class="text-start d-flex justify-content-center gap-1">
                  Page <main id="swup" class="transition-main"><?php echo $currentPage; ?></main>
                </div>
                <div class="text-end">
                  <i class="bi bi-chevron-down text-stroke"></i>
                </div>
              </button>
            </div>
            <div class="container my-2">
              <main id="swup" class="transition-main">
                <a class="btn w-100 p-3 text-start text-nowrap bg-body-tertiary link-body-emphasis shadow-sm fw-bold" href="<?= htmlspecialchars($imageSource); ?>" download>
                  <i class="bi bi-download me-2"></i> Download Current Image
                </a>
              </main>
            </div>
            <div class="container my-2">
              <a class="btn w-100 p-3 text-start bg-body-tertiary link-body-emphasis shadow-sm fw-bold" href="/private_download_images.php?artworkid=<?= urlencode($image_id) ?>">
                <i class="bi bi-file-earmark-arrow-down me-2"></i> Download Batch
              </a>
            </div>
            <div class="container btn-group gap-2">
              <button type="button" class="rounded text-nowrap btn w-50 p-3 bg-body-tertiary link-body-emphasis shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#allEpisodesModal">
                All Chapters
              </button>
              <button type="button" class="rounded text-nowrap btn w-50 p-3 bg-body-tertiary link-body-emphasis shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#previewMangaModal">
                All Previews
              </button>
            </div>
            <div class="container my-2">
              <button type="button" class="btn w-100 p-3 text-start bg-body-tertiary link-body-emphasis shadow-sm fw-bold" data-bs-dismiss="offcanvas">
                Close Menu
              </button>
            </div>
            <div class="container my-2">
              <button type="button" class="btn w-100 p-3 text-start bg-body-tertiary link-body-emphasis shadow-sm fw-bold" onclick="window.location.href='/image.php?artworkid=<?= urlencode($image_id) ?>'">
                Back to Artwork
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="pageModal" tabindex="-1" aria-labelledby="pageModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow border-0">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="pageModalLabel">All Pages</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <main id="swup" class="transition-main">
              <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                <a class="w-100 btn btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> border-0 fw-bold p-3 text-start my-1 <?= ($i == $currentPage) ? 'active' : ''; ?>" href="?artworkid=<?= urlencode($image_id) ?>&page=<?= $i ?>">
                  Page <?= $i ?>
                </a>
              <?php endfor; ?>
            </main>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="shareLink" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <main id="swup" class="transition-main">
            <div class="card rounded-4 p-4">
              <p class="text-start fw-bold">share to:</p>
              <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
                <!-- Twitter -->
                <a class="btn rounded-start-4" href="https://twitter.com/intent/tweet?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                  <i class="bi bi-twitter"></i>
                </a>
      
                <!-- Line -->
                <a class="btn" href="https://social-plugins.line.me/lineit/share?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                  <i class="bi bi-line"></i>
                </a>
      
                <!-- Email -->
                <a class="btn" href="mailto:?body=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
                  <i class="bi bi-envelope-fill"></i>
                </a>
      
                <!-- Reddit -->
                <a class="btn" href="https://www.reddit.com/submit?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                  <i class="bi bi-reddit"></i>
                </a>
      
                <!-- Instagram -->
                <a class="btn" href="https://www.instagram.com/?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                  <i class="bi bi-instagram"></i>
                </a>
      
                <!-- Facebook -->
                <a class="btn rounded-end-4" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                  <i class="bi bi-facebook"></i>
                </a>
              </div>
              <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
                <!-- WhatsApp -->
                <a class="btn rounded-start-4" href="https://wa.me/?text=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                  <i class="bi bi-whatsapp"></i>
                </a>
      
                <!-- Pinterest -->
                <a class="btn" href="https://pinterest.com/pin/create/button/?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                  <i class="bi bi-pinterest"></i>
                </a>
      
                <!-- LinkedIn -->
                <a class="btn" href="https://www.linkedin.com/shareArticle?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                  <i class="bi bi-linkedin"></i>
                </a>
      
                <!-- Messenger -->
                <a class="btn" href="https://www.facebook.com/dialog/send?link=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                  <i class="bi bi-messenger"></i>
                </a>
      
                <!-- Telegram -->
                <a class="btn" href="https://telegram.me/share/url?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                  <i class="bi bi-telegram"></i>
                </a>
      
                <!-- Snapchat -->
                <a class="btn rounded-end-4" href="https://www.snapchat.com/share?url=<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" target="_blank" rel="noopener noreferrer">
                  <i class="bi bi-snapchat"></i>
                </a>
              </div>
              <div class="input-group">
                <input type="text" id="urlInput1" value="<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" class="form-control border-2 fw-bold" readonly>
                <button class="btn btn-secondary opacity-50 fw-bold" onclick="copyToClipboard1()">
                  <i class="bi bi-clipboard-fill"></i>
                </button>
              </div>
            </div>
          </main>
        </div>
      </div>
    </div>
    <?php include('view_comments_modal.php'); ?>
    <?php include('preview_modal.php'); ?>
    <script>
      function copyToClipboard1() {
        var urlInput1 = document.getElementById('urlInput1');
        urlInput1.select();
        urlInput1.setSelectionRange(0, 99999); // For mobile devices

        document.execCommand('copy');
      }

      document.addEventListener('keydown', function(e) {
        // Skip if focused on an input or textarea
        if (['input', 'textarea'].includes(e.target.tagName.toLowerCase())) return;
        if (e.key === 'ArrowLeft') {
          const prevLink = document.getElementById('prevPageLink');
          if (prevLink) {
            prevLink.click();
          }
        } else if (e.key === 'ArrowRight') {
          const nextLink = document.getElementById('nextPageLink');
          if (nextLink) {
            nextLink.click();
          }
        }
      });
    </script>
    <?php include('../../../bootstrapjs.php'); ?>
  </body>
</html>