<?php
session_start();

$db = new PDO('sqlite:database.db');
$db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT NOT NULL, password TEXT NOT NULL)");
$db->exec("CREATE TABLE IF NOT EXISTS posts (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, content TEXT NOT NULL, user_id INTEGER NOT NULL, date DATETIME, category TEXT NOT NULL, FOREIGN KEY (user_id) REFERENCES users(id))");
$db->exec("CREATE TABLE IF NOT EXISTS comments (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT, comment TEXT, date DATETIME, post_id TEXT)");

$posts_per_page = 20;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start_index = ($page - 1) * $posts_per_page;

// Get the category from the URL parameter and URL decode it
$category = isset($_GET['q']) ? urldecode($_GET['q']) : '';

// Use a custom variable for the category without URL encoding
$pageCategory = isset($_GET['q']) ? $_GET['q'] : '';

// Modify your existing query based on the selected sorting option and category filter
$sort_option = isset($_GET['sort']) ? $_GET['sort'] : 'latest';

switch ($sort_option) {
  case 'oldest':
    $order_by = 'ORDER BY posts.id ASC';
    break;
  case 'most_replied':
    $order_by = 'ORDER BY reply_count DESC, posts.id DESC';
    break;
  default:
  $order_by = 'ORDER BY posts.id DESC';
}

// Include the category filter in the query using prepared statements
$query = "SELECT posts.*, users.username, users.id AS userid, COUNT(comments.id) AS reply_count FROM posts JOIN users ON posts.user_id = users.id LEFT JOIN comments ON posts.id = comments.post_id WHERE posts.category = :category GROUP BY posts.id $order_by LIMIT $start_index, $posts_per_page";
$stmt = $db->prepare($query);
$stmt->bindParam(':category', $category, PDO::PARAM_STR);
$stmt->execute();
$posts = $stmt->fetchAll();

$count_query = "SELECT COUNT(*) FROM posts WHERE category = :category";
$stmtPostCount = $db->prepare($count_query);
$stmtPostCount->bindParam(':category', $category, PDO::PARAM_STR);
$stmtPostCount->execute();
$total_posts = $stmtPostCount->fetchColumn();
$total_pages = ceil($total_posts / $posts_per_page);

// Count the number of posts
$queryPostCount = "SELECT COUNT(*) FROM posts WHERE category = :category";
$stmtPostCount = $db->prepare($queryPostCount);
$stmtPostCount->bindParam(':category', $category, PDO::PARAM_STR);
$stmtPostCount->execute();
$postCount = $stmtPostCount->fetchColumn();

// Count the number of replies
$queryReplyCount = "SELECT COUNT(*) FROM comments WHERE post_id IN (SELECT id FROM posts WHERE category = :category)";
$stmtReplyCount = $db->prepare($queryReplyCount);
$stmtReplyCount->bindParam(':category', $category, PDO::PARAM_STR);
$stmtReplyCount->execute();
$replyCount = $stmtReplyCount->fetchColumn();

// Query to get distinct categories and count of posts for each category
$category_query = "SELECT category, COUNT(*) as post_count FROM (SELECT DISTINCT category, id FROM posts) AS distinct_categories GROUP BY category ORDER BY post_count DESC";
$categories = $db->query($category_query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <title><?php echo str_replace('_', ' ', $category); ?></title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include('../bootstrap.php'); ?>
    <?php include('../connection.php'); ?>
    <link rel="icon" type="image/png" href="<?php echo $web; ?>/icon/favicon.png">
	<meta property="og:url" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Forum">
    <meta property="og:description" content="This is just a simple forum.">
    <meta property="og:image" content="<?php echo $web; ?>/icon/favicon.png">
  </head>
  <body>
    <?php include('../header.php'); ?>
    <div class="container my-4">
      <h6 class="fw-bold mb-2 small">total posts: <?php echo $postCount; ?> posts</h6>
      <h6 class="fw-bold mb-2 small">total replies: <?php echo $replyCount; ?> replies</h6>
      <div class="mb-3 small">
        <form method="get" action="category.php" class="d-flex justify-content-start align-content-center align-items-center">
          <label for="sort" class="fw-bold">Sort by:</label>
          <select class="ms-2 form-select form-select-sm rounded-4" name="sort" id="sort" onchange="this.form.submit()" style="max-width: 130px;">
            <option value="latest" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'latest') ? 'selected' : ''; ?>>latest</option>
            <option value="oldest" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'oldest') ? 'selected' : ''; ?>>oldest</option>
            <option value="most_replied" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'most_replied') ? 'selected' : ''; ?>>most replied</option>
          </select>
          <input type="hidden" name="q" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
        </form>
      </div>
      <div class="row">
        <?php include('categories.php'); ?>
        <div class="col-md-8">
          <?php foreach ($posts as $post): ?>
            <div class="card border-0 shadow mb-1 position-relative bg-body-tertiary rounded-4">
              <div class="card-body">
                <div class="d-flex mb-3">
                  <small class="small fw-medium">Thread by <a class="link-body-emphasis text-decoration-none" href="user.php?id=<?php echo $post['userid']; ?>"><?php echo (mb_strlen($post['username']) > 15) ? mb_substr($post['username'], 0, 15) . '...' : $post['username']; ?></a>・<?php echo (new DateTime($post['date']))->format("Y/m/d - H:i:s"); ?></small>
                </div>
                <a class="btn btn-dark btn-sm fw-medium rounded-pill link-body-emphasis mb-2" href="category.php?q=<?php echo urlencode($post['category']); ?>"><?php echo str_replace('_', ' ', $post['category']); ?></a>
                <h5 class="mb-3 fw-bold"><?php echo $post['title']; ?></h5>
                <div>
                  <?php
                    if (!function_exists('getYouTubeVideoId')) {
                      function getYouTubeVideoId($urlComment)
                      {
                        $videoId = '';
                        $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
                        if (preg_match($pattern, $urlComment, $matches)) {
                          $videoId = $matches[1];
                        }
                        return $videoId;
                      }
                    }

                    $replyText = isset($post['content']) ? $post['content'] : '';

                    if (!empty($replyText)) {
                      // Truncate to 300 characters
                      $truncatedText = mb_strimwidth($replyText, 0, 300, '...');

                      $paragraphs = explode("\n", $truncatedText);

                      foreach ($paragraphs as $index => $paragraph) {
                        $textWithoutTags = strip_tags($paragraph);
                        $pattern = '/\bhttps?:\/\/\S+/i';

                        $formattedText = preg_replace_callback($pattern, function ($matches) {
                          $url = htmlspecialchars($matches[0]);

                          // Check if the URL ends with .png, .jpg, .jpeg, or .webp
                          if (preg_match('/\.(png|jpg|jpeg|webp)$/i', $url)) {
                            return '<a href="' . $url . '" target="_blank"><img class="img-fluid rounded-4" loading="lazy" src="' . $url . '" alt="Image"></a>';
                          } elseif (strpos($url, 'youtube.com') !== false) {
                            // If the URL is from YouTube, embed it as an iframe with a very low-resolution thumbnail
                            $videoId = getYouTubeVideoId($url);
                            if ($videoId) {
                              return '<div class="w-100 overflow-hidden position-relative ratio ratio-16x9"><iframe loading="lazy" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" class="rounded-4 position-absolute top-0 bottom-0 start-0 end-0 w-100 h-100 border-0 shadow" src="https://www.youtube.com/embed/' . $videoId . '" frameborder="0" allowfullscreen></iframe></div>';
                            } else {
                              return '<a href="' . $url . '">' . $url . '</a>';
                            }
                          } else {
                            return '<a href="' . $url . '">' . $url . '</a>';
                          }
                        }, $textWithoutTags);
                    
                        echo "<p style=\"white-space: break-spaces; overflow: hidden;\">$formattedText</p>";
                      }

                      // Add "Read more" button outside the loop
                      if (mb_strlen($replyText) > 300) {
                        echo '<p><a class="link-body-emphasis text-decoration-none" href="reply.php?id=' . $post['id'] . '">Read more</a></p>';
                      }
                    } else {
                      echo "Sorry, no text...";
                    }
                  ?>
                </div>
                <p class="me-auto fw-medium small"><?php echo $post['reply_count']; ?> replies</p>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['userid']): ?>
                  <a class="btn btn-sm link-body-emphasis border-0 m-2 position-absolute top-0 end-0" href="edit.php?id=<?php echo $post['id']; ?>"><i class="bi bi-pencil-fill"></i></a>
                <?php endif; ?>
                <br>
                <a class="btn btn-sm link-body-emphasis border-0 fw-medium m-2 position-absolute bottom-0 end-0" href="reply.php?id=<?php echo $post['id']; ?>"><i class="bi bi-reply-fill"></i> Reply this thread</a>
                <button type="button" class="btn btn-sm link-body-emphasis border-0 fw-medium m-2 position-absolute bottom-0 start-0" onclick="sharePost(<?php echo $post['id']; ?>)"><i class="bi bi-share-fill"></i></button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="pagination my-4 justify-content-center gap-2">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm fw-bold btn-outline-light" href="?q=<?php echo urlencode($pageCategory); ?>&page=<?php echo $page - 1 ?>">Prev</a>
      <?php endif ?>

      <?php
      $start_page = max(1, $page - 2);
      $end_page = min($total_pages, $page + 2);

      for ($i = $start_page; $i <= $end_page; $i++):
      ?>
        <a class="btn btn-sm fw-bold btn-outline-light <?php echo ($i == $page) ? 'active' : ''; ?>" href="?q=<?php echo urlencode($pageCategory); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
      <?php endfor ?>

      <?php if ($page < $total_pages): ?>
        <a class="btn btn-sm fw-bold btn-outline-light" href="?q=<?php echo urlencode($pageCategory); ?>&page=<?php echo $page + 1 ?>">Next</a>
      <?php endif ?>
    </div>
    <script>
      function sharePost(userId) {
        // Compose the share URL
        var shareUrl = 'reply.php?id=' + userId;

        // Check if the Share API is supported by the browser
        if (navigator.share) {
          navigator.share({
          url: shareUrl
        })
          .then(() => console.log('Shared successfully.'))
          .catch((error) => console.error('Error sharing:', error));
        } else {
          console.log('Share API is not supported in this browser.');
          // Provide an alternative action for browsers that do not support the Share API
          // For example, you can open a new window with the share URL
          window.open(shareUrl, '_blank');
        }
      }
    </script>
  </body>
</html>