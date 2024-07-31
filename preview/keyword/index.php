<?php
// Connect to the SQLite database
$db = new PDO('sqlite:' . $_SERVER['DOCUMENT_ROOT'] . '/database.sqlite');

// Retrieve the URL parameter
$filter = '';
$filterValue = '';

if (isset($_GET['character'])) {
  $filter = 'characters';
  $filterValue = $_GET['character'];
} elseif (isset($_GET['parody'])) {
  $filter = 'parodies';
  $filterValue = $_GET['parody'];
} elseif (isset($_GET['group'])) {
  $filter = '`group`';
  $filterValue = $_GET['group'];
} elseif (isset($_GET['tag'])) {
  $filter = 'tags';
  $filterValue = $_GET['tag'];
} elseif (isset($_GET['q'])) {
  $filter = 'characters LIKE :q OR parodies LIKE :q OR `group` LIKE :q OR tags LIKE :q';
  $filterValue = $_GET['q'];
}

$output = '';
if (isset($_GET['character'])) {
  $output .= 'Character: ' . $_GET['character'] . ' ';
}
if (isset($_GET['parody'])) {
  $output .= 'Parody: ' . $_GET['parody'] . ' ';
}
if (isset($_GET['group'])) {
  $output .= 'Group: ' . $_GET['group'] . ' ';
}
if (isset($_GET['tag'])) {
  $output .= 'Tag: ' . $_GET['tag'] . ' ';
}
if (isset($_GET['q'])) {
  $output .= 'Search: ' . $_GET['q'] . ' ';
}
if (empty($output)) {
  $output = 'All images';
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>
      <?php echo $output; ?>
    </title>
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/png" href="/icon/favicon.png">
  </head>
  <body>
    <?php include('../header_preview.php'); ?>
    <div class="dropdown">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=newest<?php echo isset($_GET['character']) ? '&character=' . $_GET['character'] : ''; ?><?php echo isset($_GET['parody']) ? '&parody=' . $_GET['parody'] : ''; ?><?php echo isset($_GET['group']) ? '&group=' . $_GET['group'] : ''; ?><?php echo isset($_GET['tag']) ? '&tag=' . $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?by=oldest<?php echo isset($_GET['character']) ? '&character=' . $_GET['character'] : ''; ?><?php echo isset($_GET['parody']) ? '&parody=' . $_GET['parody'] : ''; ?><?php echo isset($_GET['group']) ? '&group=' . $_GET['group'] : ''; ?><?php echo isset($_GET['tag']) ? '&tag=' . $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?by=popular<?php echo isset($_GET['character']) ? '&character=' . $_GET['character'] : ''; ?><?php echo isset($_GET['parody']) ? '&parody=' . $_GET['parody'] : ''; ?><?php echo isset($_GET['group']) ? '&group=' . $_GET['group'] : ''; ?><?php echo isset($_GET['tag']) ? '&tag=' . $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">popular</a></li>
        <li><a href="?by=view<?php echo isset($_GET['character']) ? '&character=' . $_GET['character'] : ''; ?><?php echo isset($_GET['parody']) ? '&parody=' . $_GET['parody'] : ''; ?><?php echo isset($_GET['group']) ? '&group=' . $_GET['group'] : ''; ?><?php echo isset($_GET['tag']) ? '&tag=' . $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'view') echo 'active'; ?>">most viewed</a></li>
        <li><a href="?by=least<?php echo isset($_GET['character']) ? '&character=' . $_GET['character'] : ''; ?><?php echo isset($_GET['parody']) ? '&parody=' . $_GET['parody'] : ''; ?><?php echo isset($_GET['group']) ? '&group=' . $_GET['group'] : ''; ?><?php echo isset($_GET['tag']) ? '&tag=' . $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'least') echo 'active'; ?>">least viewed</a></li>
        <li><a href="?by=order_asc<?php echo isset($_GET['character']) ? '&character=' . $_GET['character'] : ''; ?><?php echo isset($_GET['parody']) ? '&parody=' . $_GET['parody'] : ''; ?><?php echo isset($_GET['group']) ? '&group=' . $_GET['group'] : ''; ?><?php echo isset($_GET['tag']) ? '&tag=' . $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'order_asc') echo 'active'; ?>">from A to Z</a></li>
        <li><a href="?by=order_desc<?php echo isset($_GET['character']) ? '&character=' . $_GET['character'] : ''; ?><?php echo isset($_GET['parody']) ? '&parody=' . $_GET['parody'] : ''; ?><?php echo isset($_GET['group']) ? '&group=' . $_GET['group'] : ''; ?><?php echo isset($_GET['tag']) ? '&tag=' . $_GET['tag'] : ''; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'order_desc') echo 'active'; ?>">from Z to A</a></li>
      </ul> 
    </div>
    <h5 class="ms-2 my-2 fw-bold">
      <?php echo $output; ?>
    </h5>
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
      }
    }
    else {
      include "index_desc.php";
    }
    
    ?>
    <?php
      $totalPages = ceil($total / $limit);
      $prevPage = $page - 1;
      $nextPage = $page + 1;
      // Get the sorting parameter from the URL, default to 'newest' if not set
      $sortBy = isset($_GET['by']) ? $_GET['by'] : 'newest';
      
      // Build query string for filters
      $filterParams = '';
      if (isset($_GET['character'])) {
        $filterParams .= '&character=' . urlencode($_GET['character']);
      }
      if (isset($_GET['parody'])) {
        $filterParams .= '&parody=' . urlencode($_GET['parody']);
      }
      if (isset($_GET['group'])) {
        $filterParams .= '&group=' . urlencode($_GET['group']);
      }
      if (isset($_GET['tag'])) {
        $filterParams .= '&tag=' . urlencode($_GET['tag']);
      }
    ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo $sortBy; ?><?php echo $filterParams; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
      <?php endif; ?>
    
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo $sortBy; ?><?php echo $filterParams; ?>&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
      <?php endif; ?>
    
      <?php
        // Calculate the range of page numbers to display
        $startPage = max($page - 2, 1);
        $endPage = min($page + 2, $totalPages);
    
        // Display page numbers within the range
        for ($i = $startPage; $i <= $endPage; $i++) {
          if ($i === $page) {
            echo '<span class="btn btn-sm btn-primary active fw-bold">' . $i . '</span>';
          } else {
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?by=' . $sortBy . $filterParams . '&page=' . $i . '">' . $i . '</a>';
          }
        }
      ?>
    
      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo $sortBy; ?><?php echo $filterParams; ?>&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
      <?php endif; ?>
    
      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo $sortBy; ?><?php echo $filterParams; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>
    <script>
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      let imageContainer = document.getElementById("image-container");

      // Set the default placeholder image
      const defaultPlaceholder = "/icon/bg.png";

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
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>