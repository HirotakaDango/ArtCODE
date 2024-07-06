<?php
require_once('../auth.php');

$email = $_SESSION['email'];

// Connect to the SQLite database using parameterized query
$db = new SQLite3('../database.sqlite');

// Retrieve episode name and user ID from the URL
$episodeName = isset($_GET['title']) ? $_GET['title'] : '';
$userId = isset($_GET['uid']) ? $_GET['uid'] : '';

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $image_id = $_POST['image_id'];

  // Check if the image has already been favorited by the current user
  $existing_fav = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = $image_id");

  if ($existing_fav == 0) {
    $db->exec("INSERT INTO favorites (email, image_id) VALUES ('$email', $image_id)");
  }

  // Get the current page URL
  $currentUrl = $_SERVER['REQUEST_URI'];

  // Redirect to the current page to prevent duplicate form submissions
  header("Location: $currentUrl");
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $image_id = $_POST['image_id'];
  $db->exec("DELETE FROM favorites WHERE email = '$email' AND image_id = $image_id");

  // Get the current page URL
  $currentUrl = $_SERVER['REQUEST_URI'];

  // Redirect to the current page to prevent duplicate form submissions
  header("Location: $currentUrl");
  exit();
}

// Prepare the query to get the user's numpage
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, SQLITE3_TEXT);
$resultNum = $queryNum->execute();
$user = $resultNum->fetchArray(SQLITE3_ASSOC);

$numpage = $user['numpage'];

// Set the limit of images per page
$limit = empty($numpage) ? 50 : $numpage;

// Get the current page number, default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset based on the current page number and limit
$offset = ($page - 1) * $limit;

// Get the total number of images for the specified episode and user ID
$total = $db->querySingle("SELECT COUNT(*) FROM images WHERE episode_name = '$episodeName' AND email = (SELECT email FROM users WHERE id = $userId)");

// Count the total number of images for the specified episode and user ID with non-empty episode names
$countStmt = $db->prepare("
    SELECT COUNT(*) as count
    FROM images
    WHERE episode_name = :episodeName AND episode_name != '' AND email = (SELECT email FROM users WHERE id = :userId)
");
$countStmt->bindValue(':episodeName', $episodeName, SQLITE3_TEXT);
$countStmt->bindValue(':userId', $userId, SQLITE3_INTEGER);
$totalCount = $countStmt->execute()->fetchArray(SQLITE3_ASSOC)['count'];

// Prepare SQL query to count views for each image in the current episode and user ID
$countViewsStmt = $db->prepare("
    SELECT SUM(view_count) as total_views
    FROM images
    WHERE episode_name = :episodeName AND email = (SELECT email FROM users WHERE id = :userId)
");

$countViewsStmt->bindValue(':episodeName', $episodeName, SQLITE3_TEXT);
$countViewsStmt->bindValue(':userId', $userId, SQLITE3_INTEGER);
$totalViewsResult = $countViewsStmt->execute();
$totalViews = $totalViewsResult->fetchArray(SQLITE3_ASSOC)['total_views'];

// Prepare SQL query to count favorites for images in the current episode and user ID
$countFavoritesStmt = $db->prepare("
    SELECT COUNT(DISTINCT email) as total_favorites
    FROM favorites
    WHERE image_id IN (
        SELECT id
        FROM images
        WHERE episode_name = :episodeName AND email = (SELECT email FROM users WHERE id = :userId)
    )
");

$countFavoritesStmt->bindValue(':episodeName', $episodeName, SQLITE3_TEXT);
$countFavoritesStmt->bindValue(':userId', $userId, SQLITE3_INTEGER);
$totalFavoritesResult = $countFavoritesStmt->execute();
$totalFavorites = $totalFavoritesResult->fetchArray(SQLITE3_ASSOC)['total_favorites'];

// Query to get the first image based on episode_name and user ID
$firstImageStmt = $db->prepare("
    SELECT id, filename
    FROM images
    WHERE episode_name = :episodeName AND episode_name != '' AND email = (SELECT email FROM users WHERE id = :userId)
    ORDER BY id ASC
    LIMIT 1
");

$firstImageStmt->bindValue(':episodeName', $episodeName, SQLITE3_TEXT);
$firstImageStmt->bindValue(':userId', $userId, SQLITE3_INTEGER);
$firstImageResult = $firstImageStmt->execute();
$firstImage = $firstImageResult->fetchArray(SQLITE3_ASSOC);

// Check if there is a first image for the specified episode and user ID
if ($firstImage) {
  $firstEpisodeId = $firstImage['id'];
  $firstEpisode = $firstImage['filename'];
} else {
  $firstEpisode = null;  // or handle accordingly based on your requirements
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $episodeName; ?></title>
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <div class="container mt-2">
      <?php include('cover.php'); ?>
      <div class="dropdown">
        <button class="btn btn-sm fw-bold rounded-pill mb-2 btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-images"></i> sort by
        </button>
        <ul class="dropdown-menu">
          <li><a href="?by=newest&title=<?php echo $episodeName; ?>&uid=<?php echo $userId; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
          <li><a href="?by=oldest&title=<?php echo $episodeName; ?>&uid=<?php echo $userId; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
          <li><a href="?by=popular&title=<?php echo $episodeName; ?>&uid=<?php echo $userId; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'popular') echo 'active'; ?>">popular</a></li>
          <li><a href="?by=view&title=<?php echo $episodeName; ?>&uid=<?php echo $userId; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'view') echo 'active'; ?>">most viewed</a></li>
          <li><a href="?by=least&title=<?php echo $episodeName; ?>&uid=<?php echo $userId; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'least') echo 'active'; ?>">least viewed</a></li>
          <li><a href="?by=liked&title=<?php echo $episodeName; ?>&uid=<?php echo $userId; ?>&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'liked') echo 'active'; ?>">liked</a></li>
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
          }
        }
        else {
          include "index_desc.php";
        }
        
        ?>
    <div class="modal fade" id="shareLink" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 rounded-0">
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
        </div>
      </div>
    </div>
    <div class="modal fade" id="originalImage" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="modal-body position-relative">
            <img class="object-fit-contain h-100 w-100 rounded" src="/images/<?php echo $firstEpisode; ?>">
            <button type="button" class="btn border-0 position-absolute end-0 top-0 m-2" data-bs-dismiss="modal"><i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i></button>
            <a class="btn btn-primary fw-bold w-100 mt-2" href="/images/<?php echo $firstEpisode; ?>" download>Download Cover Image</a>
          </div>
        </div>
      </div>
    </div>
    <script>
      function copyToClipboard1() {
        var urlInput1 = document.getElementById('urlInput1');
        urlInput1.select();
        urlInput1.setSelectionRange(0, 99999); // For mobile devices

        document.execCommand('copy');
      }
    </script>
    <style>
      .ratio-cover {
        position: relative;
        width: 100%;
        height: 0;
        padding-top: 145%;
      }

      .ratio-cover > * {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
      }
    </style>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>
