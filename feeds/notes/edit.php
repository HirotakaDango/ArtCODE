<?php
require_once('../../auth.php');

$db = new PDO('sqlite:../../database.sqlite');

$email = $_SESSION['email'];

if (isset($_POST['submit'])) {
  $post_id = $_POST['post_id'];
  $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $tags = filter_input(INPUT_POST, 'tags', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
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
  $tags = filter_var($post['tags'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW); // encode tags
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
    <form method="post">
      <div class="container-fluid mt-3 mb-5">
        <div class="d-none d-md-block d-lg-block">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-3" style="height: 65px;">
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
              <li class="ms-auto">
                <div>
                  <div class="d-flex">
                    <div class="btn-group me-auto gap-2">
                      <button class="btn btn-outline-light fw-bold text-nowrap btn-sm rounded" type="submit" name="submit">save changes</button>
                      <button type="button" class="btn btn-outline-danger fw-bold text-nowrap btn-sm rounded" data-bs-toggle="modal" data-bs-target="#modalDelete">
                        delete this work
                      </button>
                    </div>
                  </div>
                </div>
              </li>
            </ol>
          </nav>
        </div>
        <div class="p-3 bg-body-tertiary rounded-3 d-md-none d-lg-none" style="height: 65px;">
          <div class="d-flex">
            <a class="btn btn-outline-light me-auto btn-sm" href="view.php?id=<?php echo $post_id; ?>"><i class="bi bi-arrow-left" style="-webkit-text-stroke: 1px;"></i></a>
            <div class="btn-group ms-auto gap-2">
              <button class="btn btn-outline-light fw-bold text-nowrap btn-sm rounded" type="submit" name="submit">save changes</button>
              <button type="button" class="btn btn-outline-danger fw-bold text-nowrap btn-sm rounded" data-bs-toggle="modal" data-bs-target="#modalDelete">
                delete this work
              </button>
            </div>
          </div>
        </div>
      </div>
      <div class="container-fluid my-4">
        <div class="modal fade" id="modalDelete" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
              <div class="modal-header border-bottom-0">
                <h1 class="modal-title fs-5">Delete <?php echo $post['title'] ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body py-0 text-center fw-medium">
                <p>Are you sure want to delete <strong><?php echo $post['title'] ?></strong> from your works?</p>
                <p class="small">(Warning: You can't restore back after you delete this!)</p>
                <div class="btn-group w-100 mb-3 gap-3">
                  <a class="btn btn-danger px-0 rounded-3 fw-medium" href="delete.php?id=<?php echo $post_id; ?>">delete this!</a>
                  <button type="button" class="btn btn-secondary px-4 rounded-3 fw-medium" data-bs-dismiss="modal">cancel</button>
                </div>
              </div>
            </div>
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
      </div>
    </form>
    </main>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>