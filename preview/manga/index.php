<?php
$db = new PDO('sqlite:../../database.sqlite');  
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  

// Build the base query for manga images
$query = "
  SELECT 
    images.*, 
    users.id as userid, 
    users.artist
  FROM images
  JOIN users ON images.email = users.email
  LEFT JOIN favorites ON images.id = favorites.image_id
  WHERE artwork_type = 'manga'
";

// Initialize conditions and parameters arrays
$conditions = [];
$params = [];

// Filter by artist if provided
if (isset($_GET['artist'])) {
  $conditions[] = 'users.artist LIKE :artist';
  $params[':artist'] = '%' . $_GET['artist'] . '%';
}

// Filter by user id if provided
if (isset($_GET['uid'])) {
  $conditions[] = 'users.id = :user_id';
  $params[':user_id'] = $_GET['uid'];
}

// Filter by tag if provided
if (isset($_GET['tag'])) {
  $conditions[] = "(',' || images.tags || ',' LIKE :tag)";
  $params[':tag'] = '%,' . $_GET['tag'] . ',%';
}

// Filter by parody if provided
if (isset($_GET['parody'])) {
  $conditions[] = "(',' || images.parodies || ',' LIKE :parody)";
  $params[':parody'] = '%,' . $_GET['parody'] . ',%';
}

// Filter by character if provided
if (isset($_GET['character'])) {
  $conditions[] = "(',' || images.characters || ',' LIKE :character)";
  $params[':character'] = '%,' . $_GET['character'] . ',%';
}

// Filter by group if provided
if (isset($_GET['group'])) {
  $conditions[] = 'images.`group` = :group';
  $params[':group'] = $_GET['group'];
}

// Filter by categories if provided
if (isset($_GET['categories'])) {
  $conditions[] = 'images.categories = :categories';
  $params[':categories'] = $_GET['categories'];
}

// Filter by language if provided
if (isset($_GET['language'])) {
  $conditions[] = 'images.language = :language';
  $params[':language'] = $_GET['language'];
}

// Filter by search terms if provided
if (isset($_GET['search'])) {
  $searchTerms = explode(',', $_GET['search']);
  foreach ($searchTerms as $index => $term) {
    $paramName = ":term$index";
    $conditions[] = "(images.title LIKE $paramName OR images.tags LIKE $paramName OR images.episode_name LIKE $paramName OR users.artist LIKE $paramName)";
    $params[$paramName] = '%' . trim($term) . '%';
  }
}

// Append conditions to the query if any exist
if ($conditions) {
  $query .= ' AND ' . implode(' AND ', $conditions);
}

// Ensure only the latest image per episode and user is selected
$query .= "
  AND images.id IN (
    SELECT MAX(images.id)
    FROM images
    JOIN users ON images.email = users.email
    WHERE artwork_type = 'manga'
    GROUP BY episode_name, users.id
  )
";

// Limit per page
$limit = 24;
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
      <?php
        if (isset($_GET['search'])) {
          echo 'Search: "' . $_GET['search'] . '"';
        } elseif (isset($_GET['artist'])) {
          echo 'Artist: "' . $_GET['artist'] . '"';
        } elseif (isset($_GET['uid'])) {
          // Get artist name by uid
          $stmt = $db->prepare("SELECT artist FROM users WHERE id = :uid LIMIT 1");
          $stmt->execute([':uid' => $_GET['uid']]);
          $user = $stmt->fetch(PDO::FETCH_ASSOC);
          if ($user && !empty($user['artist'])) {
            echo 'Artist: "' . $user['artist'] . '"';
          } else {
            echo 'Artist not found';
          }
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
    <?php include('../header_preview.php'); ?>
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
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
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
        case 'liked':
        include "index_liked.php";
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