<?php
require_once('../../auth.php');
$db = new PDO('sqlite:../../database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$email = $_SESSION['email'];

$ownerStmt = $db->prepare('SELECT id, artist FROM users WHERE email = :email');
$ownerStmt->bindValue(':email', $email, PDO::PARAM_STR);
$ownerStmt->execute();
$ownerRow = $ownerStmt->fetch(PDO::FETCH_ASSOC);
if (!$ownerRow) {
  header("Location: ../");
  exit();
}
$ownerId = $ownerRow['id'];
$ownerArtist = $ownerRow['artist'];

$query = "
  SELECT
    private_images.*,
    users.id as userid,
    users.artist
  FROM private_images
  JOIN users ON private_images.email = users.email
  LEFT JOIN favorites ON private_images.id = favorites.image_id
  WHERE artwork_type = 'manga'
    AND users.email = :email
";

$conditions = [];
$params = [':email' => $email];

if (isset($_GET['tag'])) {
  $conditions[] = "((',' || private_images.tags || ',') LIKE :tag)";
  $params[':tag'] = '%,' . $_GET['tag'] . ',%';
}

if (isset($_GET['parody'])) {
  $conditions[] = "((',' || private_images.parodies || ',') LIKE :parody)";
  $params[':parody'] = '%,' . $_GET['parody'] . ',%';
}

if (isset($_GET['character'])) {
  $conditions[] = "((',' || private_images.characters || ',') LIKE :character)";
  $params[':character'] = '%,' . $_GET['character'] . ',%';
}

if (isset($_GET['group'])) {
  $conditions[] = 'private_images.`group` = :group';
  $params[':group'] = $_GET['group'];
}

if (isset($_GET['categories'])) {
  $conditions[] = 'private_images.categories = :categories';
  $params[':categories'] = $_GET['categories'];
}

if (isset($_GET['language'])) {
  $conditions[] = 'private_images.language = :language';
  $params[':language'] = $_GET['language'];
}

if (isset($_GET['search'])) {
  $searchInput = str_replace(',', ' ', $_GET['search']);
  $searchTerms = preg_split('/\s+/', $searchInput, -1, PREG_SPLIT_NO_EMPTY);

  foreach ($searchTerms as $index => $term) {
    $paramName = ":term$index";
    $conditions[] = "(private_images.title LIKE $paramName OR private_images.tags LIKE $paramName OR private_images.episode_name LIKE $paramName OR users.artist LIKE $paramName)";
    $params[$paramName] = '%' . trim($term) . '%';
  }
}

if ($conditions) {
  $query .= ' AND ' . implode(' AND ', $conditions);
}

$query .= "
  AND private_images.id IN (
    SELECT MAX(private_images.id)
    FROM private_images
    JOIN users ON private_images.email = users.email
    WHERE artwork_type = 'manga' AND users.email = :email
    GROUP BY episode_name, users.id
  )
";

$limit = 24;

// To show owner name (artist):
$ownerNameToShow = $ownerArtist;
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
      <?php
        if (isset($_GET['search'])) {
          echo 'Search: "' . $_GET['search'] . '"';
        } elseif (isset($_GET['tag'])) {
          echo 'Tag: "' . $_GET['tag'] . '"';
        } elseif (isset($_GET['parody'])) {
          echo 'Parody: "' . $_GET['parody'] . '"';
        } elseif (isset($_GET['character'])) {
          echo 'Character: "' . $_GET['character'] . '"';
        } elseif (isset($_GET['group'])) {
          echo 'Group: "' . $_GET['group'] . '"';
        } elseif (isset($_GET['categories'])) {
          echo 'Categories: "' . $_GET['categories'] . '"';
        } elseif (isset($_GET['language'])) {
          echo 'Language: "' . $_GET['language'] . '"';
        } else {
          echo 'ArtCODE - Manga';
        }
      ?>
    </title>
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
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
    <?php
      $currentPage = isset($_GET['page']) ? $_GET['page'] : '1';
      $currentBy = isset($_GET['by']) ? $_GET['by'] : 'newest';
    
      function buildSortLink($sortType) {
        $queryParams = array_merge($_GET, ['by' => $sortType, 'page' => isset($_GET['page']) ? $_GET['page'] : '1']);
        return '?' . http_build_query($queryParams);
      }
    
      function isActive($sortType) {
        return (!isset($_GET['by']) && $sortType === 'newest') || (isset($_GET['by']) && $_GET['by'] === $sortType);
      }
    ?>
    <?php include('./header_manga.php'); ?>
    <div class="dropdown mt-1">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <?php
        $sortOptions = [
          'newest' => 'newest',
          'oldest' => 'oldest',
          'popular' => 'popular',
          'view' => 'most viewed',
          'least' => 'least viewed',
          'order_asc' => 'from A to Z',
          'order_desc' => 'from Z to A',
          'daily' => 'daily',
          'week' => 'week',
          'month' => 'month',
          'year' => 'year'
        ];
    
        foreach ($sortOptions as $key => $label):
        ?>
          <li>
            <a href="<?php echo buildSortLink($key); ?>" class="dropdown-item fw-bold <?php echo isActive($key) ? 'active' : ''; ?>">
              <?php echo $label; ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php 
    if(isset($_GET['by'])){
      $sort = $_GET['by'];

      switch ($sort) {
        case 'newest':
        include "index_desc.php";
        break;
        case 'oldest':
        include "index_asc.php";
        break;
        case 'popular':
        include "index_pop.php";
        break;
        case 'view':
        include "index_view.php";
        break;
        case 'least':
        include "index_least.php";
        break;
        case 'order_asc':
        include "index_order_asc.php";
        break;
        case 'order_desc':
        include "index_order_desc.php";
        break;
        case 'daily':
        include "index_daily.php";
        break;
        case 'week':
        include "index_week.php";
        break;
        case 'month':
        include "index_month.php";
        break;
        case 'year':
        include "index_year.php";
        break;
      }
    }
    else {
      include "index_desc.php";
    }
    
    ?>
    <div class="container-fluid mb-5 mt-3">
      <div class="pagination d-flex gap-1 justify-content-center mt-3">
        <?php if (isset($page) && isset($totalPages)): ?>
          <a class="btn btn-sm btn-primary fw-bold <?php if($page <= 1) echo 'd-none'; ?>" href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>"><i class="bi text-stroke bi-chevron-double-left"></i></a>
        <?php endif; ?>
        <?php if (isset($page) && $page > 1): ?>
          <a class="btn btn-sm btn-primary fw-bold" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
        <?php endif; ?>
        <?php
        if (isset($page) && isset($totalPages)) {
          $startPage = max($page - 2, 1);
          $endPage = min($page + 2, $totalPages);
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
        <?php if (isset($page) && isset($totalPages)): ?>
          <a class="btn btn-sm btn-primary fw-bold <?php if($totalPages <= 1) echo 'd-none'; ?>" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
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