<?php
require_once('auth.php');

// Connect to the database
$db = new SQLite3('database.sqlite');
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS forum (id INTEGER PRIMARY KEY, email TEXT, comment TEXT, title TEXT, category TEXT, created_at TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS reply_forum (id INTEGER PRIMARY KEY AUTOINCREMENT, comment_id INTEGER, email TEXT, reply TEXT, date DATETIME, FOREIGN KEY (comment_id) REFERENCES comments(id))");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS category_forum (id INTEGER PRIMARY KEY AUTOINCREMENT, category_name TEXT)");
$stmt->execute();

// Define categories
$categories = [
  'Anime_&_Japan',
  'Country',
  'Technology_&_Gadgets',
  'Music',
  'Movies_&_TV_Shows',
  'Books_&_Literature',
  'Science',
  'Food_&_Cooking',
  'Fitness_&_Health',
  'Travel_&_Adventure',
  'Art_&_Design',
  'History',
  'Fashion_&_Style',
  'Gaming',
  'Sports',
  'Programming_&_Coding',
  'Pets_&_Animals',
  'Nature',
  'Photography',
  'Social_Media_&_Internet',
  'Education',
  'Business',
  'Finance',
  'Politics',
  'Religion',
  'Self_Improvement',
  'Parenting',
  'Relationships',
  'Environment',
  'Motivation',
  'Career_Development',
  'Human_Rights',
  'Entertainment_News',
  'Medical',
  'Mental_Health',
  'Product_Reviews',
  'DIY_&_Crafts',
  'Celebrity_Gossip',
  'Automotive',
  'Home_Decor',
  'Outdoors',
  'Fitness_Tracking',
  
  // Additional Japanese culture categories
  'Japanese_Language',
  'Traditional_Arts',
  'Japanese_Festivals',
  'Japanese_Food',
  'Japanese_Fashion',
  'Japanese_History',
  'Anime_Cosplay',
  'Japanese_Music',
  'Manga',
  'Japanese_Traditions',
  'Samurai_Culture',
  'Geisha_Culture',
  'Japanese_Architecture',
  'Tea_Ceremony',
  'Kabuki_Theater',
  'Japanese_Gardens',
  'Sumo_Wrestling',
  'Sushi',
  'Karaoke',
  'Hanami_Festivals',
  
  // Additional anime, manga, light novel, video games, doujinshi categories
  'Anime_Reviews',
  'Manga_Reviews',
  'Light_Novels',
  'Video_Game_Reviews',
  'Anime_Recommendations',
  'Manga_Recommendations',
  'Video_Game_Recommendations',
  'Anime_Merchandise',
  'Manga_Merchandise',
  'Video_Game_Merchandise',
  'Cosplay',
  'Anime_Conventions',
  'Video_Game_Development',
  'Doujinshi_Creation',
  'Anime_Streaming_Services',
  'Manga_Artists',
  'Anime_Studio_News',
  'Visual_Novels',
  'Anime_Culture',
  'Otaku_Lifestyle',
];

// Insert categories if they don't already exist
foreach ($categories as $category) {
  $stmt = $db->prepare("SELECT category_name FROM category_forum WHERE category_name = :category");
  $stmt->bindValue(':category', $category, SQLITE3_TEXT);
  $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

  // Check if category exists
  if (!$result) {
    $stmt = $db->prepare("INSERT INTO category_forum (category_name) VALUES (:category)");
    $stmt->bindValue(':category', $category, SQLITE3_TEXT);
    $stmt->execute();
  }
}

// Check if the form was submitted for adding a new comment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
  // Get the comment from the form data
  $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $comment = nl2br($comment);
  $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $email = $_SESSION['email'];

  // Get the current time
  $currentDate  = date("Y/m/d");

  // Insert the comment into the database
  $stmt = $db->prepare("INSERT INTO forum (email, comment, title, category, created_at) VALUES (:email, :comment, :title, :category, :created_at)");
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':comment', $comment, SQLITE3_TEXT);
  $stmt->bindValue(':title', $title, SQLITE3_TEXT);
  $stmt->bindValue(':category', $category, SQLITE3_TEXT);
  $stmt->bindValue(':created_at', $currentDate , SQLITE3_TEXT);
  $stmt->execute();

  // Redirect back to the image page
  $currentURL = $_SERVER['REQUEST_URI'];
  $redirectURL = $currentURL;
  header("Location: $redirectURL");
  exit();
}

// Check if the form was submitted for updating or deleting a comment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
  $action = $_POST['action'];
  $comment_id = $_POST['comment_id'];

  // Get the email of the current user
  $email = $_SESSION['email'];

  // Check if the comment belongs to the current user
  $stmt = $db->prepare("SELECT * FROM forum WHERE id=:comment_id AND email=:email");
  $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $comment = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

  if ($comment) {
    if ($action == 'delete') {
      // Delete the comment from the comments table
      $stmt = $db->prepare("DELETE FROM forum WHERE id=:comment_id");
      $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
      $stmt->execute();

      // Delete the corresponding replies from the reply_comments table
      $stmt = $db->prepare("DELETE FROM reply_forum WHERE comment_id=:comment_id");
      $stmt->bindValue(':comment_id', $comment_id, SQLITE3_INTEGER);
      $stmt->execute();
    }
  }

  // Redirect back to the image page
  $currentURL = $_SERVER['REQUEST_URI'];
  $redirectURL = $currentURL;
  header("Location: $redirectURL");
  exit();
}

// Set the number of items to display per page
$items_per_page = 100;

// Get the current page from the URL, or default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the starting offset for the current page
$offset = ($page - 1) * $items_per_page;

// Get the total number of forum items
$total_items_stmt = $db->prepare("SELECT COUNT(*) FROM forum");
$total_items = $total_items_stmt->execute()->fetchArray()[0];

// Calculate the total number of pages
$total_pages = ceil($total_items / $items_per_page);

// Query to get categories
$stmt = $db->prepare('SELECT * FROM category_forum ORDER BY category_name ASC');
$results = $stmt->execute();

// Query to get distinct categories and count of posts for each category
$category_query = "SELECT category, COUNT(*) as post_count FROM (SELECT DISTINCT category, id FROM forum) AS distinct_categories GROUP BY category ORDER BY post_count DESC";
$result = $db->query($category_query);
$categories = array();
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <title>Forum</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <?php include('forum_categories.php'); ?>
    <form class="input-group container mt-1 mb-2" role="search" action="forum_search.php">
      <input class="form-control rounded-start-4 border-0 bg-body-tertiary focus-ring focus-ring-dark" name="q" type="search" placeholder="Search" aria-label="Search">
      <button class="btn rounded-end-4 border-0 bg-body-tertiary" type="submit"><i class="bi bi-search"></i></button>
    </form>
    <div class="dropdown container">
      <button class="btn btn-sm fw-bold rounded-pill mb-2 btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=newest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?by=oldest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
        <li><a href="?by=top&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'top') echo 'active'; ?>">top</a></li>
      </ul> 
    </div>
        <?php 
        if(isset($_GET['by'])){
          $sort = $_GET['by'];
 
          switch ($sort) {
            case 'newest':
            include "forum_desc.php";
            break;
            case 'oldest':
            include "forum_asc.php";
            break;
            case 'top':
            include "forum_top.php";
            break;
          }
        }
        else {
          include "forum_desc.php";
        }
        
        ?>
    <nav class="navbar fixed-bottom navbar-expand justify-content-center z-2">
      <div class="container">
        <button type="button" class="w-100 btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold rounded-3 border-0 rounded-0" data-bs-toggle="modal" data-bs-target="#forum">upload your post</button>
      </div>
    </nav>
    <div class="modal fade" id="forum" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content border-0 rounded-4 shadow">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Upload your post</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form class="form-control border-0 mb-2" action="" method="POST">
              <div class="form-floating">
                <input type="text" class="form-control fw-medium rounded-3 border-0 rounded-0 bg-body-tertiary mb-2" name="title" id="floatingInputInvalid" placeholder="Title" required>
                <label class="fw-bold" for="floatingTextarea">Title</label>
              </div>
              <div class="form-floating">
                <select class="form-select fw-medium rounded-3 border-0 rounded-0 bg-body-tertiary mb-2 py-0 text-start" name="category" required>
                  <option class="form-control" value="">Add category:</option>
                  <?php
                    // Loop through each category and create an option in the dropdown list
                    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                        $category_name = $row['category_name'];
                        $id = $row['id'];
                        echo '<option value="' . htmlspecialchars($category_name) . '">' . htmlspecialchars($category_name) . '</option>';
                    }
                  ?>
                </select>
              </div>
              <textarea type="text" class="form-control fw-medium rounded-3 border-0 rounded-0 bg-body-tertiary mb-2" style="height: 200px; max-height: 800px;" name="comment" placeholder="Type something..." aria-label="Type a message..." aria-describedby="basic-addon2" required></textarea>
              <button class="w-100 btn btn-primary rounded-3" type="submit"><i class="bi bi-send-fill"></i></button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <br><br><br>
    <style>
      .text-stroke {
        -webkit-text-stroke: 1px;
      }
      
      .text-view-none {
        display: none;
      }
    </style> 
    <script>
      function goBack() {
        window.location.href = "/";
      }
    </script>
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
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>