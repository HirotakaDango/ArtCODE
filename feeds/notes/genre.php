<?php
require_once('auth.php');

$email = $_SESSION['email'];

$db = new PDO('sqlite:../../database.sqlite');

// set the number of posts per page
$posts_per_page = 10;

// get the tag from the URL parameter
$tag = isset($_GET['tag']) ? $_GET['tag'] : '';

// count the total number of posts with the given tag and current email
$stmt = $db->prepare('SELECT COUNT(*) FROM posts WHERE tags LIKE :tag AND email = :email');
$stmt->bindValue(':tag', '%' . $tag . '%');
$stmt->bindValue(':email', $email);
$stmt->execute();
$total_posts = $stmt->fetchColumn();

// calculate the total number of pages
$total_pages = ceil($total_posts / $posts_per_page);

// get the current page from the URL parameter
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// calculate the offset
$offset = ($page - 1) * $posts_per_page;

// query the database for the posts on the current page
$stmt = $db->prepare('SELECT * FROM posts WHERE tags LIKE :tag AND email = :email ORDER BY id DESC LIMIT :limit OFFSET :offset');
$stmt->bindValue(':tag', '%' . $tag . '%');
$stmt->bindValue(':email', $email);
$stmt->bindValue(':limit', $posts_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <title>Posts by Genre: <?php echo $tag ?></title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <main id="swup" class="transition-main">
    <div class="container-fluid mt-3 mb-5">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-3">
          <li class="breadcrumb-item">
            <a class="link-body-emphasis" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">
              <i class="bi bi-house-fill"></i>
            </a>
          </li>
          <li class="breadcrumb-item">
            <a class="link-body-emphasis fw-semibold text-decoration-none text-white fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/notes/">Home</a>
          </li>
          <li class="breadcrumb-item">
            <a class="link-body-emphasis fw-semibold text-decoration-none text-white fw-medium" href="#">Tag</a>
          </li>
          <li class="breadcrumb-item">
            <a class="link-body-emphasis fw-semibold text-decoration-none text-white fw-medium" href="genre.php?tag=<?php echo $tag; ?>"><?php echo $tag; ?></a>
          </li>
        </ol>
      </nav>
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-md-4 g-3">
        <?php foreach ($posts as $post): ?>
          <div class="col">
            <a class="content text-decoration-none" href="view.php?id=<?php echo $post['id'] ?>">
              <div class="card shadow-sm h-100 position-relative">
                <div class="d-flex justify-content-center align-items-center text-center">
                  <i class="bi bi-book-half display-1 p-5 text-secondary border-bottom w-100"></i>
                </div>
                <h5 class="text-center w-100 p-3"><?php echo $post['title']; ?></h5>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="pagination my-4 justify-content-center gap-2">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm fw-bold btn-primary" href="?tag=<?php echo $tag; ?>&page=<?php echo $page - 1 ?>">Prev</a>
      <?php endif ?>

      <?php
      $start_page = max(1, $page - 2);
      $end_page = min($total_pages, $page + 2);

      for ($i = $start_page; $i <= $end_page; $i++):
      ?>
        <a class="btn btn-sm fw-bold btn-primary <?php echo ($i == $page) ? 'active' : ''; ?>" href="?tag=<?php echo $tag; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
      <?php endfor ?>

      <?php if ($page < $total_pages): ?>
        <a class="btn btn-sm fw-bold btn-primary" href="?tag=<?php echo $tag; ?>&page=<?php echo $page + 1 ?>">Next</a>
      <?php endif ?>
    </div>
    </main>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>