<?php
$stmt = $db->prepare("SELECT COUNT(*) FROM images WHERE REPLACE(`group`, ' ', '') LIKE :groupWithoutSpaces ESCAPE '\\' OR REPLACE(`group`, ' ', '') LIKE :group_start ESCAPE '\\' OR REPLACE(`group`, ' ', '') LIKE :group_end ESCAPE '\\' OR `group` = :group_exact");
$stmt->bindValue(':groupWithoutSpaces', "{$groupWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':group_start', "%,{$groupWithoutSpaces}", SQLITE3_TEXT);
$stmt->bindValue(':group_end', "%,{$groupWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':group_exact', $group, SQLITE3_TEXT);
$count = $stmt->execute()->fetchArray()[0];

// Define the limit and offset for the query
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT); // Assuming $email is the email you want to search for
$resultNum = $queryNum->execute();
$user = $resultNum->fetchArray(SQLITE3_ASSOC);

$numpage = $user['numpage'];

// Set the limit of images per page
$limit = empty($numpage) ? 50 : $numpage;
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Get the current page number from the URL parameter
$offset = ($page - 1) * $limit; // Calculate the offset based on the page number and limit

// Retrieve the total number of images with the specified group
$stmt = $db->prepare("SELECT COUNT(*) FROM images WHERE REPLACE(`group`, ' ', '') LIKE :groupWithoutSpaces ESCAPE '\\' OR REPLACE(`group`, ' ', '') LIKE :group_start ESCAPE '\\' OR REPLACE(`group`, ' ', '') LIKE :group_end ESCAPE '\\' OR `group` = :group_exact");
$stmt->bindValue(':groupWithoutSpaces', "{$groupWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':group_start', "%,{$groupWithoutSpaces}", SQLITE3_TEXT);
$stmt->bindValue(':group_end', "%,{$groupWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':group_exact', $group, SQLITE3_TEXT);
$total = $stmt->execute()->fetchArray()[0];

// Retrieve the images for the current page
$stmt = $db->prepare("SELECT images.*, COUNT(favorites.id) AS favorite_count 
                       FROM images 
                       LEFT JOIN favorites ON images.id = favorites.image_id 
                       WHERE REPLACE(images.`group`, ' ', '') LIKE :groupWithoutSpaces ESCAPE '\\' 
                          OR REPLACE(images.`group`, ' ', '') LIKE :group_start ESCAPE '\\' 
                          OR REPLACE(images.`group`, ' ', '') LIKE :group_end ESCAPE '\\' 
                          OR images.`group` = :group_exact 
                       GROUP BY images.id 
                       ORDER BY favorite_count DESC 
                       LIMIT :limit OFFSET :offset");
$stmt->bindValue(':groupWithoutSpaces', "{$groupWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':group_start', "%,{$groupWithoutSpaces}", SQLITE3_TEXT);
$stmt->bindValue(':group_end', "%,{$groupWithoutSpaces},%", SQLITE3_TEXT);
$stmt->bindValue(':group_exact', $group, SQLITE3_TEXT);
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();
?>

    <?php include('image_card_group.php')?>
    <?php
      $totalPages = ceil($total / $limit);
      $prevPage = $page - 1;
      $nextPage = $page + 1;
    ?>
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=view&group=<?php echo isset($_GET['group']) ? $_GET['group'] : ''; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
      <?php endif; ?>

      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=view&group=<?php echo isset($_GET['group']) ? $_GET['group'] : ''; ?>&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
      <?php endif; ?>

      <?php
        $group = isset($_GET['group']) ? 'group=' . $_GET['group'] . '&' : '';

        // Calculate the range of page numbers to display
        $startPage = max($page - 2, 1);
        $endPage = min($page + 2, $totalPages);

        // Display page numbers within the range
        for ($i = $startPage; $i <= $endPage; $i++) {
          if ($i === $page) {
            echo '<span class="btn btn-sm btn-primary active fw-bold">' . $i . '</span>';
          } else {
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?by=view&' . $group . 'page=' . $i . '">' . $i . '</a>';
          }
        }
      ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=view&group=<?php echo isset($_GET['group']) ? $_GET['group'] : ''; ?>&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=view&group=<?php echo isset($_GET['group']) ? $_GET['group'] : ''; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>
    <script>
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      let imageContainer = document.getElementById("image-container");

      // Set the default placeholder image
      const defaultPlaceholder = "../icon/bg.png";

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