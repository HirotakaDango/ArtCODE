<?php
require_once('../../auth.php');

$db = new PDO('sqlite:../../database.sqlite');

$email = $_SESSION['email'];

if (isset($_POST['submit'])) {
  $post_id = $_POST['post_id'];
  $title = htmlspecialchars($_POST['title']);
  $tags = htmlspecialchars($_POST['tags']);
  $content = htmlspecialchars($_POST['content']);
  $content = nl2br($content);
  $query = "UPDATE posts SET title='$title', tags='$tags', content='$content' WHERE id='$post_id'";
  $db->exec($query);
  header('Location: view.php?id=' . $post_id);
}

if (isset($_GET['id'])) {
  $post_id = $_GET['id'];
  $query = "SELECT * FROM posts WHERE id='$post_id' AND email='$email'";
  $post = $db->query($query)->fetch();
  if (!$post) {
  header('Location: view.php?id=' . $post_id);
  }
  $tags = htmlspecialchars($post['tags']); // encode tags
} else {
  header('Location: view.php?id=' . $post_id);
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <title>Edit <?php echo $post['title'] ?></title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <main id="swup" class="transition-main">
    <div class="container mt-5">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-3">
          <li class="breadcrumb-item">
            <a class="link-body-emphasis" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">
              <i class="bi bi-house-fill"></i>
            </a>
          </li>
          <li class="breadcrumb-item">
            <a class="link-body-emphasis fw-semibold text-decoration-none fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/notes/">Home</a>
          </li>
          <li class="breadcrumb-item">
            <a class="link-body-emphasis fw-semibold text-decoration-none text-white fw-medium" href="view.php?id=<?php echo $post_id; ?>"><?php echo $post['title']; ?></a>
          </li>
          <li class="breadcrumb-item">
            <a class="link-body-emphasis border-bottom border-3 py-2 fw-semibold text-decoration-none fw-medium" href="edit.php?id=<?php echo $post_id; ?>">Edit <?php echo $post['title']; ?></a>
          </li>
        </ol>
      </nav>
    </div>
    <form method="post" class="container my-4">
      <div class="d-none d-md-block d-lg-block">
        <div class="d-flex">
          <div class="btn-group me-auto">
            <button class="btn btn-primary fw-bold mb-5" type="submit" name="submit">save changes</button>
            <a class="btn btn-danger fw-bold mb-5" href="delete.php?id=<?php echo $post_id; ?>">delete</a>
          </div>
          <a class="ms-auto btn btn-primary fw-bold mb-5" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">back to home</a>
        </div>
      </div>
      <input type="hidden" name="post_id" value="<?php echo $post_id ?>">
      <div class="input-group gap-3 mb-2">
        <div class="form-floating">
          <input class="form-control border-top-0 border-start-0 border-end-0 rounded-bottom-0 border-3 focus-ring focus-ring-dark" type="text" name="title" placeholder="Enter title" maxlength="100" required value="<?php echo $post['title'] ?>">  
          <label for="floatingInput" class="fw-bold"><small>Enter title</small></label>
        </div>
        <div class="form-floating">
          <input class="form-control border-top-0 border-start-0 border-end-0 rounded-bottom-0 border-3 focus-ring focus-ring-dark" type="text" name="tags" placeholder="Enter genre" maxlength="50" required value="<?php echo $post['tags'] ?>">  
          <label for="floatingInput" class="fw-bold"><small>Enter genre</small></label>
        </div>
      </div>
      <div class="form-floating mb-2">
        <textarea class="form-control rounded border-3 focus-ring focus-ring-dark vh-100" name="content" oninput="stripHtmlTags(this)" placeholder="Enter content" required><?php echo strip_tags($post['content']) ?></textarea>
        <label for="floatingInput" class="fw-bold"><small>Enter content</small></label>
      </div>
      <div class="d-flex d-md-none d-lg-none">
        <button class="me-auto btn btn-primary fw-bold mb-5" type="submit" name="submit">save changes</button>
        <a class="ms-auto btn btn-primary fw-bold mb-5" href="profile.php">back to profile</a>
      </div>
    </form>
    </main>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>