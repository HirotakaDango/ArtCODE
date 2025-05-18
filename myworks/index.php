<?php
require_once('../auth.php');

// Connect to the SQLite database
$db = new SQLite3('../database.sqlite');

// Get the artist name from the database
$email = $_SESSION['email'];
$stmt = $db->prepare("SELECT id, artist, pic, `desc`, bgpic, twitter, pixiv, other, region FROM users WHERE email = :email");
$stmt->bindValue(':email', $email);
$result = $stmt->execute();
$row = $result->fetchArray();
$user_id = $row['id'];
$artist = $row['artist'];
$pic = $row['pic'];
$desc = $row['desc'];
$bgpic = $row['bgpic'];
$twitter = $row['twitter'];
$pixiv = $row['pixiv'];
$other = $row['other'];
$region = $row['region'];

// Function to format numbers
function formatNumber($num) {
  if ($num >= 1000000) {
    return round($num / 1000000, 1) . 'm';
  } elseif ($num >= 100000) {
    return round($num / 1000) . 'k';
  } elseif ($num >= 10000) {
    return round($num / 1000, 1) . 'k';
  } elseif ($num >= 1000) {
    return round($num / 1000) . 'k';
  } else {
    return $num;
  }
}

// Count the number of followers
$stmt = $db->prepare("SELECT COUNT(*) AS num_followers FROM following WHERE following_email = :email");
$stmt->bindValue(':email', $email);
$result = $stmt->execute();
$row = $result->fetchArray();
$num_followers = $row['num_followers'];

// Count the number of following
$stmt = $db->prepare("SELECT COUNT(*) AS num_following FROM following WHERE follower_email = :email");
$stmt->bindValue(':email', $email);
$result = $stmt->execute();
$row = $result->fetchArray();
$num_following = $row['num_following'];

// Format the numbers
$formatted_followers = formatNumber($num_followers);
$formatted_following = formatNumber($num_following);

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $existing_fav = $db->query("SELECT COUNT(*) FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = $image_id")->fetchArray()[0];

  if ($existing_fav == 0) {
    $db->exec("INSERT INTO favorites (email, image_id) VALUES ('{$_SESSION['email']}', $image_id)");
  }

  // Get the current page URL
  $currentUrl = $_SERVER['REQUEST_URI'];

  // Redirect to the current page to prevent duplicate form submissions
  header("Location: $currentUrl");
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $db->exec("DELETE FROM favorites WHERE email = '{$_SESSION['email']}' AND image_id = $image_id");

  // Get the current page URL
  $currentUrl = $_SERVER['REQUEST_URI'];

  // Redirect to the current page to prevent duplicate form submissions
  header("Location: $currentUrl");
  exit();
}

// Count the number of images uploaded by the current user
$count = 0;
while ($image = $result->fetchArray()) {
  $count++;
}

$fav_result = $db->query("SELECT COUNT(*) FROM favorites WHERE email = '{$_SESSION['email']}'");
$fav_count = $fav_result->fetchArray()[0];

// Get all of the images uploaded by the current user
$stmt = $db->prepare("SELECT * FROM images WHERE email = :email ORDER BY id DESC");
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();

$totalImages = [];
while ($imageRow = $result->fetchArray(SQLITE3_ASSOC)) {
  $totalImages[] = $imageRow;
}
$totalImageCount = count($totalImages);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $artist; ?></title>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="/style.css">
  </head>
  <body>
    <?php include('../header.php'); ?>
    <?php
    $validTaggedFilters = ['tagged_oldest', 'tagged_newest', 'tagged_popular', 'tagged_view', 'tagged_least', 'tagged_liked', 'tagged_order_asc', 'tagged_order_desc', 'tagged_daily', 'tagged_week', 'tagged_month', 'tagged_year'];
    $validHeaderPages = ['myworks_tagged_asc.php', 'myworks_tagged_desc.php', 'myworks_tagged_pop.php', 'myworks_tagged_view.php', 'myworks_tagged_least.php', 'myworks_tagged_like.php', 'myworks_tagged_order_asc.php', 'myworks_tagged_order_desc.php', 'myworks_tagged_daily.php', 'myworks_tagged_week.php', 'myworks_tagged_month.php', 'myworks_tagged_year.php'];
    
    $isTagHidden = false;
    
    if (isset($_GET['by']) && in_array($_GET['by'], $validTaggedFilters)) {
      $isTagHidden = true;
    } else {
      foreach ($validHeaderPages as $page) {
        if (strpos($_SERVER['REQUEST_URI'], $page) !== false) {
          $isTagHidden = true;
          break;
        }
      }
    }
    ?>
    <div class="dropdown <?php echo $isTagHidden ? 'd-none' : ''; ?>">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=newest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?by=oldest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?by=popular&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">popular</a></li>
        <li><a href="?by=view&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'view') echo 'active'; ?>">most viewed</a></li>
        <li><a href="?by=least&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'least') echo 'active'; ?>">least viewed</a></li>
        <li><a href="?by=liked&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'liked') echo 'active'; ?>">liked</a></li>
        <li><a href="?by=order_asc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'order_asc') echo 'active'; ?>">from A to Z</a></li>
        <li><a href="?by=order_desc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'order_desc') echo 'active'; ?>">from Z to A</a></li>
        <li><a href="?by=daily&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'daily') echo 'active'; ?>">daily</a></li>
        <li><a href="?by=week&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'week') echo 'active'; ?>">week</a></li>
        <li><a href="?by=month&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'month') echo 'active'; ?>">month</a></li>
        <li><a href="?by=year&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'year') echo 'active'; ?>">year</a></li>
      </ul>
    </div> 

    <?php
    $validFilters = ['oldest', 'newest', 'popular', 'view', 'least', 'liked', 'order_asc', 'order_desc', 'daily', 'week', 'month', 'year'];
    $validPages = ['myworks_asc.php', 'myworks_desc.php', 'myworks_pop.php', 'myworks_view.php', 'myworks_least.php', 'myworks_like.php', 'myworks_order_asc.php', 'myworks_order_desc.php', 'myworks_daily.php', 'myworks_week.php', 'myworks_month.php', 'myworks_year.php'];
    
    $isHidden = false;
    
    if (isset($_GET['by']) && in_array($_GET['by'], $validFilters)) {
      $isHidden = true;
    } else {
      foreach ($validPages as $page) {
        if (strpos($_SERVER['REQUEST_URI'], $page) !== false) {
          $isHidden = true;
          break;
        }
      }
    }
    ?>
    <div class="dropdown <?php echo $isHidden ? 'd-none' : ''; ?>">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=tagged_newest&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'tagged_newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?by=tagged_oldest&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'tagged_oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?by=tagged_popular&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'tagged_popular') echo 'active'; ?>">popular</a></li>
        <li><a href="?by=tagged_view&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'tagged_view') echo 'active'; ?>">most viewed</a></li>
        <li><a href="?by=tagged_least&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'tagged_least') echo 'active'; ?>">least viewed</a></li>
        <li><a href="?by=tagged_liked&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'tagged_liked') echo 'active'; ?>">liked</a></li>
        <li><a href="?by=tagged_order_asc&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'tagged_order_asc') echo 'active'; ?>">from A to Z</a></li>
        <li><a href="?by=tagged_order_desc&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'tagged_order_desc') echo 'active'; ?>">from Z to A</a></li>
        <li><a href="?by=tagged_daily&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'tagged_daily') echo 'active'; ?>">daily</a></li>
        <li><a href="?by=tagged_week&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'tagged_week') echo 'active'; ?>">week</a></li>
        <li><a href="?by=tagged_month&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'tagged_month') echo 'active'; ?>">month</a></li>
        <li><a href="?by=tagged_year&tag=<?php echo isset($_GET['tag']) ? $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'tagged_year') echo 'active'; ?>">year</a></li>
      </ul>
    </div>
    <?php include('all_tags_header.php'); ?>
    <div class="overflow-x-auto container-fluid p-1 mb-2 hide-scrollbar" style="white-space: nowrap; overflow: auto;">
      <a href="?by=<?php echo isset($_GET['by']) ? str_replace('tagged_', '', $_GET['by']) : 'newest'; ?>&page=<?= isset($_GET['page']) ? htmlspecialchars($_GET['page']) : '1'; ?>" class="fw-medium btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-pill">all images</a>
      <?php
      try {
    
        // SQL query to get the most popular tags and their counts based on the user's email
        $queryTagsMyworks = "SELECT SUBSTR(tags, 1, INSTR(tags, ',') - 1) as first_tag, COUNT(*) as tag_count 
                            FROM images 
                            WHERE tags LIKE :patternMyworks AND email = :email 
                            GROUP BY first_tag 
                            ORDER BY tag_count ASC 
                            LIMIT 100";
    
        // Prepare the SQL statement
        $stmtMyworks = $db->prepare($queryTagsMyworks);
    
        // Bind the parameters
        $patternMyworks = "%,%";
        $stmtMyworks->bindValue(':patternMyworks', $patternMyworks, SQLITE3_TEXT);
        
        $email = $_SESSION['email']; // Use the user's email
        $stmtMyworks->bindValue(':email', $email, SQLITE3_TEXT);
    
        // Execute the query
        $resultMyworks = $stmtMyworks->execute();
    
        // Fetch the results into an array
        $tagsMyworks = [];
        while ($rowMyworks = $resultMyworks->fetchArray(SQLITE3_ASSOC)) {
          $tagsMyworks[] = $rowMyworks;
        }
        
      } catch (Exception $eMyworks) {
        // Handle any database connection or query errors
        $errorMessageMyworks = "Error: " . $eMyworks->getMessage();
      }
      ?>
      <?php if (isset($errorMessageMyworks)): ?>
        <div class="alert alert-danger">
          <?= htmlspecialchars($errorMessageMyworks) ?>
        </div>
      <?php endif; ?>
    
      <?php if (!empty($tagsMyworks)): ?>
        <?php foreach ($tagsMyworks as $rowMyworks): ?>
          <?php
            $firstTagMyworks = htmlspecialchars($rowMyworks['first_tag']);
            $tagCountMyworks = htmlspecialchars($rowMyworks['tag_count']);
          ?>
          <a class="fw-medium btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-pill" style="margin-right: 2px;" href="?by=<?php echo isset($_GET['by']) ? (strpos($_GET['by'], 'tagged_') === false ? 'tagged_' . $_GET['by'] : $_GET['by']) : 'newest'; ?>&tag=<?= urlencode($firstTagMyworks) ?>">
            <i class="bi bi-tags-fill"></i> <?= $firstTagMyworks ?>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <?php 
    if(isset($_GET['by'])){
      $sort = $_GET['by'];

      switch ($sort) {
        case 'newest':
        include "myworks_desc.php";
        break;
        case 'oldest':
        include "myworks_asc.php";
        break;
        case 'popular':
        include "myworks_pop.php";
        break;
        case 'view':
        include "myworks_view.php";
        break;
        case 'least':
        include "myworks_least.php";
        break;
        case 'liked':
        include "myworks_like.php";
        break;
        case 'order_asc':
        include "myworks_order_asc.php";
        break;
        case 'order_desc':
        include "myworks_order_desc.php";
        break;
        case 'daily':
        include "myworks_daily.php";
        break;
        case 'week':
        include "myworks_week.php";
        break;
        case 'month':
        include "myworks_month.php";
        break;
        case 'year':
        include "myworks_year.php";
        break;

        case 'tagged_newest':
        include "myworks_tagged_desc.php";
        break;
        case 'tagged_oldest':
        include "myworks_tagged_asc.php";
        break;
        case 'tagged_popular':
        include "myworks_tagged_pop.php";
        break;
        case 'tagged_view':
        include "myworks_tagged_view.php";
        break;
        case 'tagged_least':
        include "myworks_tagged_least.php";
        break;
        case 'tagged_liked':
        include "myworks_tagged_like.php";
        break;
        case 'tagged_order_asc':
        include "myworks_tagged_order_asc.php";
        break;
        case 'tagged_order_desc':
        include "myworks_tagged_order_desc.php";
        break;
        case 'tagged_daily':
        include "myworks_tagged_daily.php";
        break;
        case 'tagged_week':
        include "myworks_tagged_week.php";
        break;
        case 'tagged_month':
        include "myworks_tagged_month.php";
        break;
        case 'tagged_year':
        include "myworks_tagged_year.php";
        break;
      }
    }
    else {
      include "myworks_desc.php";
    }
    
    ?>
    <?php
      $totalPages = ceil($total / $limit);
      $prevPage = $page - 1;
      $nextPage = $page + 1;
    ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <?php
          $prevPageUrl = http_build_query(array_merge($_GET, ['page' => 1]));
          $prevUrl = "?$prevPageUrl";
          $prevPageUrl = http_build_query(array_merge($_GET, ['page' => $prevPage]));
          $prevPageUrl = "?$prevPageUrl";
        ?>
        <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $prevUrl; ?>"><i class="bi text-stroke bi-chevron-double-left"></i></a>
        <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $prevPageUrl; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
      <?php endif; ?>
    
      <?php
        // Calculate the range of page numbers to display
        $startPage = max($page - 2, 1);
        $endPage = min($page + 2, $totalPages);
    
        // Display page numbers within the range
        for ($i = $startPage; $i <= $endPage; $i++) {
          $queryParams = array_merge($_GET, ['page' => $i]);
          $pageUrl = http_build_query($queryParams);
          $url = "?$pageUrl";
          if ($i === $page) {
            echo '<span class="btn btn-sm btn-primary active fw-bold">' . $i . '</span>';
          } else {
            echo '<a class="btn btn-sm btn-primary fw-bold" href="' . $url . '">' . $i . '</a>';
          }
        }
      ?>
    
      <?php if ($page < $totalPages): ?>
        <?php
          $nextPageUrl = http_build_query(array_merge($_GET, ['page' => $nextPage]));
          $nextPageUrl = "?$nextPageUrl";
          $lastPageUrl = http_build_query(array_merge($_GET, ['page' => $totalPages]));
          $lastPageUrl = "?$lastPageUrl";
        ?>
        <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $nextPageUrl; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
        <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $lastPageUrl; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>
    <script>
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      let imageContainer = document.getElementById("image-container");

      // Set the default placeholder image
      const defaultPlaceholder = "icon/bg.png";

      if ("IntersectionObserver" in window) {
        let imageObserver = new IntersectionObserver(function(entries, observer) {
          entries.forEach(function(entry) {
            if (entry.isIntersecting) {
              let image = entry.target;
              image.src = image.dataset.src;
              imageObserver.unobserve(image);
            }
          });
        });

        lazyloadImages.forEach(function(image) {
          image.src = defaultPlaceholder; // Apply default placeholder
          imageObserver.observe(image);
          image.style.filter = "blur(5px)"; // Apply initial blur to all images

          // Remove blur and apply custom blur to NSFW images after they load
          image.addEventListener("load", function() {
            image.style.filter = ""; // Remove initial blur
            if (image.classList.contains("nsfw")) {
              image.style.filter = "blur(4px)"; // Apply blur to NSFW images
          
              // Add overlay with icon and text
              let overlay = document.createElement("div");
              overlay.classList.add("overlay", "rounded");
              let icon = document.createElement("i");
              icon.classList.add("bi", "bi-eye-slash-fill", "text-white");
              overlay.appendChild(icon);
              let text = document.createElement("span");
              text.textContent = "R-18";
              text.classList.add("shadowed-text", "fw-bold", "text-white");
              overlay.appendChild(text);
              image.parentNode.appendChild(overlay);
            }
          });
        });
      } else {
        let lazyloadThrottleTimeout;

        function lazyload() {
          if (lazyloadThrottleTimeout) {
            clearTimeout(lazyloadThrottleTimeout);
          }
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

      // Infinite scrolling
      let loading = false;

      function loadMoreImages() {
        if (loading) return;
        loading = true;

        // Simulate loading delay for demo purposes
        setTimeout(function() {
          for (let i = 0; i < 10; i++) {
            if (lazyloadImages.length === 0) {
              break;
            }
            let image = lazyloadImages[0];
            imageContainer.appendChild(image);
            lazyloadImages = Array.from(lazyloadImages).slice(1);
          }
          loading = false;
        }, 1000);
      }

      window.addEventListener("scroll", function() {
        if (window.innerHeight + window.scrollY >= imageContainer.clientHeight) {
          loadMoreImages();
        }
      });

      // Initial loading
      loadMoreImages();
    </script>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>