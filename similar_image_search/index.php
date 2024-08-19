<?php
require_once('../auth.php');

$email = $_SESSION['email'];

// Connect to the SQLite database
$db = new PDO('sqlite:../database.sqlite');

// Process any favorite/unfavorite requests
if (isset($_POST['favorite']) || isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  
  // Check if the image ID is valid
  $query = $db->prepare('SELECT COUNT(*) FROM images WHERE id = :id');
  $query->bindParam(':id', $image_id);
  $query->execute();
  $valid_image_id = $query->fetchColumn();
  
  if ($valid_image_id) {
    // Check if the image has already been favorited by the current user
    $query = $db->prepare('SELECT COUNT(*) FROM favorites WHERE email = :email AND image_id = :image_id');
    $query->bindParam(':email', $email);
    $query->bindParam(':image_id', $image_id);
    $query->execute();
    $existing_fav = $query->fetchColumn();

    if (isset($_POST['favorite'])) {
      if ($existing_fav == 0) {
        $query = $db->prepare('INSERT INTO favorites (email, image_id) VALUES (:email, :image_id)');
        $query->bindParam(':email', $email);
        $query->bindParam(':image_id', $image_id);
        $query->execute();
      }
    } elseif (isset($_POST['unfavorite'])) {
      if ($existing_fav > 0) {
        $query = $db->prepare('DELETE FROM favorites WHERE email = :email AND image_id = :image_id');
        $query->bindParam(':email', $email);
        $query->bindParam(':image_id', $image_id);
        $query->execute();
      }
    }
  }
  
  // Get the current page URL
  $currentUrl = $_SERVER['REQUEST_URI'];

  // Redirect to the current page to prevent duplicate form submissions
  header("Location: $currentUrl");
  exit();
}

// Function to convert image URL to base64
function urlToBase64($url) {
  if (file_exists($url)) {
    $imageData = file_get_contents($url);
    return base64_encode($imageData);
  } else {
    return false;
  }
}

// Function to calculate a simple similarity score between two base64 strings
function calculateSimilarity($base64_1, $base64_2) {
  $diff = abs(strlen($base64_1) - strlen($base64_2));
  $maxLength = max(strlen($base64_1), strlen($base64_2));
  return 1 - ($diff / $maxLength);
}

// Get the image URL
$imageUrl = isset($_GET['image']) ? $_GET['image'] : '';
$imageBase64 = false;
$resultArray = [];
$displayMessage = '';
$dropdownStyle = empty($imageUrl) ? 'display: none;' : '';
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8">
    <title>Similar Image Search</title>
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <div class="dropdown" style="<?php echo $dropdownStyle; ?>">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=newest&image=<?php echo urlencode($imageUrl); ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?by=oldest&image=<?php echo urlencode($imageUrl); ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?by=popular&image=<?php echo urlencode($imageUrl); ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">popular</a></li>
        <li><a href="?by=view&image=<?php echo urlencode($imageUrl); ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'view') echo 'active'; ?>">most viewed</a></li>
        <li><a href="?by=least&image=<?php echo urlencode($imageUrl); ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'least') echo 'active'; ?>">least viewed</a></li>
        <li><a href="?by=liked&image=<?php echo urlencode($imageUrl); ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'liked') echo 'active'; ?>">liked</a></li>
        <li><a href="?by=order_asc&image=<?php echo urlencode($imageUrl); ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'order_asc') echo 'active'; ?>">from A to Z</a></li>
        <li><a href="?by=order_desc&image=<?php echo urlencode($imageUrl); ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'order_desc') echo 'active'; ?>">from Z to A</a></li>
        <li><a href="?by=daily&image=<?php echo urlencode($imageUrl); ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'daily') echo 'active'; ?>">daily</a></li>
        <li><a href="?by=week&image=<?php echo urlencode($imageUrl); ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'week') echo 'active'; ?>">week</a></li>
        <li><a href="?by=month&image=<?php echo urlencode($imageUrl); ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'month') echo 'active'; ?>">month</a></li>
        <li><a href="?by=year&image=<?php echo urlencode($imageUrl); ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'year') echo 'active'; ?>">year</a></li>
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
        include "index_like.php";
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
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>