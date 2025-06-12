<?php
require_once('../../auth.php');  
$db = new PDO('sqlite:../../database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Validate email or fallback to session
if (!$email && isset($_SESSION['email'])) {
  $email = $_SESSION['email'];
}
if (!$email) {
  die('No user specified.');
}

// Fetch artist associated with email (if available)
$user_stmt = $db->prepare('SELECT artist FROM users WHERE email = :email');
$user_stmt->bindParam(':email', $email, PDO::PARAM_STR);
$user_stmt->execute();
$user_result = $user_stmt->fetch(PDO::FETCH_ASSOC);
$artist = $user_result ? $user_result['artist'] : 'Unknown User';

// Pagination setup
$itemsPerPage = 24;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $itemsPerPage;

// Fetch user's manga favorites (episode_name, uid)
$stmt = $db->prepare('SELECT episode_name, uid FROM favorites_manga WHERE email = :email ORDER BY id DESC LIMIT :limit OFFSET :offset');
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total number of favorites for pagination
$countStmt = $db->prepare('SELECT COUNT(*) AS total FROM favorites_manga WHERE email = :email');
$countStmt->bindParam(':email', $email, PDO::PARAM_STR);
$countStmt->execute();
$totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalCount / $itemsPerPage);

// For lazy-load and card display, fetch info for each favorite by episode_name (latest manga image for that episode, any user)
$displayImages = [];
if (!empty($favorites)) {
  foreach ($favorites as $fav) {
    // Fetch the *latest* image for this episode_name (across all users)
    $imgStmt = $db->prepare(
      'SELECT images.*, users.id AS userid, users.artist
       FROM images
       JOIN users ON images.email = users.email
       WHERE images.artwork_type = "manga" AND images.episode_name = :episode_name
       ORDER BY images.id DESC LIMIT 1'
    );
    $imgStmt->bindParam(':episode_name', $fav['episode_name'], PDO::PARAM_STR);
    $imgStmt->execute();
    $cover = $imgStmt->fetch(PDO::FETCH_ASSOC);

    // Fallback values if no cover found
    $displayImages[] = [
      'episode_name' => $fav['episode_name'],
      'userid' => $cover['userid'] ?? $fav['uid'],
      'artist' => $cover['artist'] ?? '',
      'language' => $cover['language'] ?? '',
      'filename' => $cover['filename'] ?? '',
      'title' => $cover['title'] ?? $fav['episode_name'],
      'id' => $cover['id'] ?? '',
    ];
  }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorites</title>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <?php include('../../bootstrapcss.php'); ?>
    <style>
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
      .text-stroke { -webkit-text-stroke: 1px; }
    </style>
  </head>
  <body>
    <?php include('../../header.php'); ?>
    <?php include('./header_manga.php'); ?>
    <?php include('image_card_feeds_manga.php'); ?>
    <div class="container-fluid mb-5 mt-3">
      <div class="pagination d-flex gap-1 justify-content-center mt-3">
        <?php
        if (isset($page) && isset($totalPages)) {
          $startPage = max($page - 2, 1);
          $endPage = min($page + 2, $totalPages);
        }
        ?>
        <?php if (isset($page) && isset($totalPages) && $startPage > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>"><i class="bi text-stroke bi-chevron-double-left"></i></a>
        <?php endif; ?>

        <?php if (isset($page) && $page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
        <?php endif; ?>

        <?php
        if (isset($page) && isset($totalPages)) {
          for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i === $page) {
              echo '<span class="btn btn-sm btn-primary active fw-bold">' . $i . '</span>';
            } else {
              echo '<a class="btn btn-sm btn-primary fw-bold" href="?' . http_build_query(array_merge($_GET, ['page' => $i])) . '">' . $i . '</a>';
            }
          }
        }
        ?>

        <?php if (isset($page) && isset($totalPages) && $page < $totalPages): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
        <?php endif; ?>

        <?php if (isset($page) && isset($totalPages) && $totalPages > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
        <?php endif; ?>
      </div>
    </div>
    <script>
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      const defaultPlaceholder = "/icon/bg.png";
      if ("IntersectionObserver" in window) {
        let imageObserver = new IntersectionObserver(function(entries) {
          entries.forEach(function(entry) {
            if (entry.isIntersecting) {
              let image = entry.target;
              image.src = image.dataset.src;
              imageObserver.unobserve(image);
            }
          });
        });
        lazyloadImages.forEach(function(image) {
          image.src = defaultPlaceholder;
          imageObserver.observe(image);
          image.style.filter = "blur(5px)";
          image.addEventListener("load", function() { image.style.filter = "none"; });
        });
      } else {
        let lazyloadThrottleTimeout;
        function lazyload() {
          if (lazyloadThrottleTimeout) { clearTimeout(lazyloadThrottleTimeout); }
          lazyloadThrottleTimeout = setTimeout(function() {
            let scrollTop = window.pageYOffset;
            lazyloadImages.forEach(function(img) {
              if (img.offsetTop < window.innerHeight + scrollTop) {
                img.src = img.dataset.src;
                img.classList.remove("lazy-load");
              }
            });
            lazyloadImages = Array.from(lazyloadImages).filter(function(image) {
              return image.classList.contains("lazy-load");
            });
            if (lazyloadImages.length === 0) {
              document.removeEventListener("scroll", lazyload);
              window.removeEventListener("resize", lazyload);
              window.removeEventListener("orientationChange", lazyload);
            }
          }, 20);
        }
        document.addEventListener("scroll", lazyload);
        window.addEventListener("resize", lazyload);
        window.addEventListener("orientationChange", lazyload);
      }
    </script>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>