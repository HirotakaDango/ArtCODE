<?php
try {
  if (isset($_GET['title']) && isset($_GET['id']) && isset($_GET['page'])) {
    $episode_name = $_GET['title']; // Keep episode_name!
    $image_id     = $_GET['id'];     // Use 'id' as the primary identifier
    $page         = (int)$_GET['page'];

    // Connect to the SQLite database
    $db = new PDO('sqlite:../../database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query the current chapter (image) details including user info
    $query = "
      SELECT
        images.*,
        users.id   AS userid,
        users.artist
      FROM images
      JOIN users ON images.email = users.email
      WHERE artwork_type  = 'manga'
        AND episode_name  = :episode_name
        AND images.id     = :image_id -- Use images.id here
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':episode_name', $episode_name);
    $stmt->bindParam(':image_id',     $image_id);
    $stmt->execute();
    $image_details = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$image_details) {
      // It's good practice to check if image_details exists before trying to access its elements
      echo '<p>Chapter not found.</p>';
      // Maybe redirect or show a more user-friendly error
      exit;
    }

    // Now we know $image_details exists, we can safely access its elements
    $user_id = $image_details['userid']; // Get user ID for URLs

    // child pages of this chapter
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

    $totalPages    = count($image_child) + 1;
    $currentPage   = $page;

    // all chapters in this episode by this artist
    $query_all = "
      SELECT
        images.*,
        users.id AS userid,
        users.artist
      FROM images
      JOIN users ON images.email = users.email
      WHERE artwork_type = 'manga'
        AND episode_name = :episode_name
        AND images.email = :email -- Filter by the correct user's email
      ORDER BY images.id ASC -- Or maybe ORDER BY images.created_at ? Ensure consistent chapter order
    ";
    $stmt_all = $db->prepare($query_all);
    $stmt_all->bindParam(':episode_name', $episode_name);
    $stmt_all->bindParam(':email',         $image_details['email']);
    $stmt_all->execute();
    $all_chapters = $stmt_all->fetchAll(PDO::FETCH_ASSOC);

    // find index of current chapter
    $current_chapter_index = -1;
    foreach ($all_chapters as $i => $chap) {
      if ($chap['id'] == $image_details['id']) {
        $current_chapter_index = $i;
        break;
      }
    }

    $backLink = 'title.php?title=' . urlencode($episode_name) . '&uid=' . urlencode($user_id);

    if ($currentPage > 1) {
      // Previous page within the same chapter
      $prevLink = '?title=' . urlencode($episode_name) . '&uid=' . urlencode($user_id) . '&id=' . urlencode($image_id) . '&page=' . ($currentPage - 1);
    } elseif ($currentPage == 1 && $current_chapter_index > 0) {
      // At first page of the current chapter; link to LAST page of the PREVIOUS chapter
      $prevChapter = $all_chapters[$current_chapter_index - 1];
      // Get the total pages of the previous chapter
      $query_prev_count = "SELECT COUNT(*) AS total FROM image_child WHERE image_id = :prev_chapter_id";
      $stmt_prev_count = $db->prepare($query_prev_count);
      $stmt_prev_count->bindParam(':prev_chapter_id', $prevChapter['id']);
      $stmt_prev_count->execute();
      $prevCount = $stmt_prev_count->fetch(PDO::FETCH_ASSOC);
      $prevChapterTotalPages = ($prevCount['total'] ?? 0) + 1; // Default to 1 page if count fails
      $prevLink = '?title=' . urlencode($episode_name) . '&uid=' . urlencode($user_id) . '&id=' . urlencode($prevChapter['id']) . '&page=' . $prevChapterTotalPages;
    } else {
      // At the first page of the first chapter, link back
      $prevLink = $backLink;
    }

    // --- Next Link Logic ---
    if ($currentPage < $totalPages) {
      // Next page in the current chapter
      $nextLink = '?title=' . urlencode($episode_name) . '&uid=' . urlencode($user_id) . '&id=' . urlencode($image_id) . '&page=' . ($currentPage + 1);
    } elseif ($currentPage == $totalPages && $current_chapter_index < count($all_chapters) - 1) {
      // At the last page of current chapter; link to FIRST page of the NEXT chapter
      $nextChapter = $all_chapters[$current_chapter_index + 1];
      $nextLink = '?title=' . urlencode($episode_name) . '&uid=' . urlencode($user_id) . '&id=' . urlencode($nextChapter['id']) . '&page=1';
    } else {
      // At the last page of the last chapter, link back
      $nextLink = $backLink;
    }
    // Specific Page Links
    $firstPageLink = '?title=' . urlencode($episode_name) . '&uid=' . urlencode($user_id) . '&id=' . urlencode($image_id) . '&page=1';
    $lastPageLink = '?title=' . urlencode($episode_name) . '&uid=' . urlencode($user_id) . '&id=' . urlencode($image_id) . '&page=' . $totalPages;


    $url_comment = "../../comments_preview.php?imageid=" . urlencode($image_id);

    $url_preview = "preview.php?title=" . urlencode($episode_name) . "&uid=" . urlencode($user_id) . "&id=" . urlencode($image_id);

    $url_back_to_artwork = "./title.php?title=" . urlencode($episode_name) . "&uid=" . urlencode($user_id);

  } else {
    echo '<p>Error: Missing required parameters (title, id, page).</p>';
    // Consider logging this error
    exit;
  }
} catch (PDOException $e) {
  error_log('Database Error: ' . $e->getMessage()); // Example logging
  echo '<p>An error occurred while retrieving chapter details. Please try again later.</p>';
  exit;
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $image_details['title'] ?? 'Manga Chapter'; ?></title>
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
            $imageSource = ($currentPage == 1) ?
              ('../../images/' . ($image_details['filename'] ?? '/icon/bg.png')) :
              ('../../images/' . ($image_child[$currentPage - 2]['filename'] ?? '/icon/bg.png'));

            // Basic Preload Setup
            $prevPageSrc = '';
            if ($currentPage > 1) {
              $prevPageIndex = $currentPage - 2;
              $prevPageSrc = ('../../images/' . ($image_child[$prevPageIndex]['filename'] ?? ''));
            }

            $nextPageSrc = '';
            if ($currentPage < $totalPages) {
              $nextPageIndex = $currentPage - 1;
              $nextPageSrc = ('../../images/' . ($image_child[$nextPageIndex]['filename'] ?? ''));
            }
          ?>
          <div class="bg-body-tertiary py-1 d-md-none">
            <div class="d-flex justify-content-center align-items-center container">
              <?php
                // Use calculated links $firstPageLink, $prevLink, $nextLink, $lastPageLink, $backLink
                echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="' . $firstPageLink . '"><i class="bi bi-chevron-double-left text-stroke"></i></a></main>';
    
                $prevIconMobile = ($prevLink == $backLink) ? 'bi-reply-fill' : 'bi-chevron-left';
                echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="' . $prevLink . '"><i class="bi ' . $prevIconMobile . ' text-stroke"></i></a></main>';
    
                echo '<main id="swup" class="transition-main"><h6 class="pt-1">' . $currentPage . '/' . $totalPages . '</h6></main>';
    
                $nextIconMobile = ($nextLink == $backLink) ? 'bi-reply-fill' : 'bi-chevron-right';
                echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="' . $nextLink . '"><i class="bi ' . $nextIconMobile . ' text-stroke"></i></a></main>';
    
                echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="' . $lastPageLink . '"><i class="bi bi-chevron-double-right text-stroke"></i></a></main>';
              ?>
            </div>
          </div>
          <main id="swup" class="transition-main">
            <div class="position-relative d-flex justify-content-center w-100">
              <?php echo '<a id="prevPageLink" class="position-absolute top-0 start-0 w-25 h-100 text-decoration-none" href="' . $prevLink . '"></a>'; ?>

              <?php if (!empty($prevPageSrc)): ?>
                <link rel="preload" href="<?= $prevPageSrc ?>" as="image">
              <?php endif; ?>
              <img class="mangaImage" id="mainMangaImage" src="<?= $imageSource; ?>" alt="<?= $image_details['title'] ?? 'Manga Page'; ?>">
              <?php if (!empty($nextPageSrc)): ?>
                <link rel="preload" href="<?= $nextPageSrc ?>" as="image">
              <?php endif; ?>

              <?php echo '<a id="nextPageLink" class="position-absolute top-0 end-0 w-25 h-100 text-decoration-none" href="' . $nextLink . '"></a>'; ?>
            </div>
          </main>
          <div class="bg-body-tertiary py-1 d-md-none">
            <div class="d-flex justify-content-center align-items-center container">
              <?php
                // Use calculated links $firstPageLink, $prevLink, $nextLink, $lastPageLink, $backLink
                echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="' . $firstPageLink . '"><i class="bi bi-chevron-double-left text-stroke"></i></a></main>';
    
                $prevIconMobile = ($prevLink == $backLink) ? 'bi-reply-fill' : 'bi-chevron-left';
                echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="' . $prevLink . '"><i class="bi ' . $prevIconMobile . ' text-stroke"></i></a></main>';
    
                echo '<main id="swup" class="transition-main"><h6 class="pt-1">' . $currentPage . '/' . $totalPages . '</h6></main>';
    
                $nextIconMobile = ($nextLink == $backLink) ? 'bi-reply-fill' : 'bi-chevron-right';
                echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="' . $nextLink . '"><i class="bi ' . $nextIconMobile . ' text-stroke"></i></a></main>';
    
                echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="' . $lastPageLink . '"><i class="bi bi-chevron-double-right text-stroke"></i></a></main>';
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="position-fixed bottom-0 start-0 z-2 m-2 ms-3">
      <h6 class="small d-flex">
        <main id="swup" class="transition-main">
          <?php echo $currentPage; ?> / <?php echo $totalPages; ?>
        </main>
      </h6>
    </div>

    <div class="modal fade" id="allEpisodesModal" tabindex="-1" aria-labelledby="allEpisodesModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow border-0">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="allEpisodesModalLabel">All Chapters</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <?php if (is_array($all_chapters) && !empty($all_chapters)): ?>
              <main id="swup" class="transition-main">
                <?php foreach ($all_chapters as $chapter): ?>
                  <?php
                    $chapter_link = '?title=' . urlencode($episode_name) . '&uid=' . urlencode($user_id) . '&id=' . urlencode($chapter['id']) . '&page=1';
                    $is_active = ($chapter['id'] == $image_id) ? 'active' : '';
                  ?>
                  <a class="w-100 btn btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold p-3 text-start my-1 <?= $is_active ?>" href="<?= $chapter_link ?>">
                    <?= $chapter['title']; ?>
                  </a>
                <?php endforeach; ?>
              </main>
            <?php else: ?>
              <p>No other chapters found for this title and user.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="offcanvas offcanvas-end border-0 rounded-start-4 bg-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?> shadow-sm" tabindex="-1" id="offcanvasMenu" aria-labelledby="offcanvasMenuLabel" style="box-shadow: none; max-width: 300px;">
      <div class="container">
        <div class="d-flex justify-content-center align-items-center vh-100">
          <div class="w-100">
            <input type="text" class="form-control-plaintext fw-bold mb-3 px-3 pb-3 fs-3" value="<?= $image_details['title'] ?? 'Chapter'; ?>" readonly>
            <div class="d-flex justify-content-center align-items-center container my-2">
              <?php
                $firstPageLink = '?title=' . urlencode($episode_name) . '&uid=' . urlencode($user_id) . '&id=' . urlencode($image_id) . '&page=1';
                echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis shadow-sm border-0 fw-medium" href="' . $firstPageLink . '"><i class="bi bi-chevron-double-left text-stroke"></i></a></main>';

                $prevIcon = ($prevLink == $backLink) ? 'bi-reply-fill' : 'bi-chevron-left';
                echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis shadow-sm border-0 fw-medium" href="' . $prevLink . '"><i class="bi ' . $prevIcon . ' text-stroke"></i></a></main>';

                echo '<main id="swup" class="transition-main"><h6 class="pt-1">' . $currentPage . ' / ' . $totalPages . '</h6></main>';

                 $nextIcon = ($nextLink == $backLink) ? 'bi-reply-fill' : 'bi-chevron-right';
                echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis shadow-sm border-0 fw-medium" href="' . $nextLink . '"><i class="bi ' . $nextIcon . ' text-stroke"></i></a></main>';

                $lastPageLink = '?title=' . urlencode($episode_name) . '&uid=' . urlencode($user_id) . '&id=' . urlencode($image_id) . '&page=' . $totalPages;
                echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis shadow-sm border-0 fw-medium" href="' . $lastPageLink . '"><i class="bi bi-chevron-double-right text-stroke"></i></a></main>';
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
            <div class="container my-2 gap-2">
              <button type="button" class="rounded btn w-100 p-3 text-start bg-body-tertiary link-body-emphasis shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#shareLink">
                <i class="bi bi-share-fill"></i> Share
              </button>
            </div>
            <div class="container my-2">
              <main id="swup" class="transition-main">
                <a class="btn w-100 p-3 text-start bg-body-tertiary link-body-emphasis shadow-sm fw-bold" href="<?= $imageSource; ?>" download>
                  <i class="bi bi-download"></i> Download Current Image
                </a>
              </main>
            </div>
            <div class="container my-2">
              <a class="btn w-100 p-3 text-start bg-body-tertiary link-body-emphasis shadow-sm fw-bold" href="/download_images.php?id=<?= urlencode($image_id) ?>">
                <i class="bi bi-file-earmark-arrow-down"></i> Download Batch
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
              <button type="button" class="btn w-100 p-3 text-start bg-body-tertiary link-body-emphasis shadow-sm fw-bold" onclick="window.location.href='<?= $url_back_to_artwork ?>'">
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
              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php
                  $page_link = '?title=' . urlencode($episode_name) . '&uid=' . urlencode($user_id) . '&id=' . urlencode($image_id) . '&page=' . $i;
                  $is_active = ($i == $currentPage) ? 'active' : '';
                ?>
                <a class="w-100 btn btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> border-0 fw-bold p-3 text-start my-1 <?= $is_active ?>" href="<?= $page_link ?>">
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
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>