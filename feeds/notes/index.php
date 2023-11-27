<?php
require_once('../../auth.php');

$email = $_SESSION['email'];

try {
  // Connect to the SQLite database
  $dbPathN = $_SERVER['DOCUMENT_ROOT'] . '/database.sqlite';
  $db = new PDO("sqlite:$dbPathN");
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Create the posts table if not exists
  $db->exec("CREATE TABLE IF NOT EXISTS posts (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, content TEXT NOT NULL, email INTEGER NOT NULL, tags TEXT NOT NULL, date DATETIME, FOREIGN KEY (email) REFERENCES users(id))");

  $posts_per_page = 10;
  $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
  $start_index = ($page - 1) * $posts_per_page;

  // Use prepared statements to prevent SQL injection
  $query = "SELECT * FROM posts WHERE email = :email ORDER BY id DESC LIMIT :start_index, :posts_per_page";
  $stmt = $db->prepare($query);
  $stmt->bindParam(':email', $email, PDO::PARAM_STR);
  $stmt->bindParam(':start_index', $start_index, PDO::PARAM_INT);
  $stmt->bindParam(':posts_per_page', $posts_per_page, PDO::PARAM_INT);
  $stmt->execute();
  $posts = $stmt->fetchAll();

  // Get total posts count for pagination
  $count_query = "SELECT COUNT(*) FROM posts WHERE email = :email";
  $stmt = $db->prepare($count_query);
  $stmt->bindParam(':email', $email, PDO::PARAM_STR);
  $stmt->execute();
  $total_posts = $stmt->fetchColumn();
  $total_pages = ceil($total_posts / $posts_per_page);
} catch (PDOException $e) {
  // Handle database connection errors
  die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <title>ArtCODE - Notes</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <main id="swup" class="transition-main">
    <?php include('header.php'); ?>
    <div class="container-fluid my-4">
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-md-4 g-3">
        <?php foreach ($posts as $post): ?>
          <div class="col">
            <a class="content text-decoration-none" href="view.php?id=<?php echo $post['id'] ?>">
              <div class="card shadow-sm h-100 position-relative">
                <div class="d-flex justify-content-center align-items-center text-center">
                  <i class="bi bi-book-half display-1 p-5 text-secondary border-bottom w-100"></i>
                </div>
                <h5 class="text-center w-100 p-3"><?php echo $post['title']; ?></h5>
                <div class="mt-5">
                  <div class="btn-group position-absolute bottom-0 start-0 m-2">
                    <button onclick="location.href='view.php?id=<?php echo $post['id'] ?>'" class="btn btn-sm btn-outline-secondary fw-medium">View</button>
                    <button onclick="location.href='edit.php?id=<?php echo $post['id'] ?>'" class="btn btn-sm btn-outline-secondary fw-medium">Edit</button>
                  </div>
                  <small class="text-body-secondary position-absolute bottom-0 end-0 m-2 fw-medium"><?php echo $post['date']; ?></small>
                </div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="pagination my-4 justify-content-center gap-2">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm fw-bold btn-primary" href="?page=<?php echo $page - 1 ?>">Prev</a>
      <?php endif ?>

      <?php
      $start_page = max(1, $page - 2);
      $end_page = min($total_pages, $page + 2);

      for ($i = $start_page; $i <= $end_page; $i++):
      ?>
        <a class="btn btn-sm fw-bold btn-primary <?php echo ($i == $page) ? 'active' : ''; ?>" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
      <?php endfor ?>

      <?php if ($page < $total_pages): ?>
        <a class="btn btn-sm fw-bold btn-primary" href="?page=<?php echo $page + 1 ?>">Next</a>
      <?php endif ?>
    </div>
    </main>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>