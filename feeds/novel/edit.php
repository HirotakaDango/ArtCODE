<?php
require_once('../../auth.php');

$db = new PDO('sqlite:../../database.sqlite');

// Ensure the user is logged in
if (!isset($_SESSION['email'])) {
  // Redirect to the specified URL for non-logged-in users
  $redirect_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/feeds/novel/';
  header('Location: ' . $redirect_url);
  exit;
}

$email = $_SESSION['email'];

if (isset($_POST['submit'])) {
  $post_id = $_POST['post_id'];
  $title = htmlspecialchars($_POST['title']);
  $tags = htmlspecialchars($_POST['tags']);
  $description = htmlspecialchars($_POST['description']);
  $content = htmlspecialchars($_POST['content']);

  // Update the novel entry in the database only if the logged-in user is the owner
  $stmt = $db->prepare("UPDATE novel SET title = :title, tags = :tags, description = :description, content = :content WHERE id = :post_id AND email = :email");
  $stmt->bindValue(':title', $title);
  $stmt->bindValue(':tags', $tags);
  $stmt->bindValue(':description', $description);
  $stmt->bindValue(':content', $content);
  $stmt->bindValue(':post_id', $post_id);
  $stmt->bindValue(':email', $email);

  $stmt->execute();

  header('Location: view.php?id=' . $post_id);
  exit;
}

if (isset($_GET['id'])) {
  $post_id = $_GET['id'];
  $query = "SELECT * FROM novel WHERE id=:post_id AND email=:email";
  $stmt = $db->prepare($query);
  $stmt->bindValue(':post_id', $post_id);
  $stmt->bindValue(':email', $email);
  $stmt->execute();
  $novel = $stmt->fetch();

  if (!$novel) {
    // Redirect to the specified URL for users who try to edit novels that don't belong to them
    $redirect_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/feeds/novel/';
    header('Location: ' . $redirect_url);
    exit;
  }
} else {
  header('Location: view.php?id=' . $post_id);
  exit;
}
?>

<!DOCTYPE html>
<html data-bs-theme="dark">
  <head>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit <?php echo $novel['title']; ?></title>
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include ('header.php'); ?>
    <div class="container">
      <h2 class="text-center fw-bold mb-4">EDIT <?php echo $novel['title']; ?></h2>
      <form method="post" enctype="multipart/form-data">
        <div class="row featurette">
          <div class="col-md-7 order-md-2">
            <img class="d-block border border-2 object-fit-cover rounded mb-2" src="thumbnails/<?php echo $novel['filename']; ?>" style="height: 340px; width: 100%;">
          </div>
          <div class="col-md-5 order-md-1">
            <!-- Populate form fields with existing data -->
            <input type="hidden" name="post_id" value="<?php echo $novel['id']; ?>">
            <input class="form-control mb-2" type="text" placeholder="Title" id="title" name="title" value="<?php echo htmlspecialchars($novel['title']); ?>" required>
            <div class="input-group mb-2 gap-2">
              <input class="form-control rounded" type="text" placeholder="Tags" id="tags" name="tags" value="<?php echo htmlspecialchars($novel['tags']); ?>" required>
            </div>
            <textarea class="form-control mb-2" type="text" placeholder="Description" oninput="stripHtmlTags(this)" id="description" style="height: 247px;" name="description" required><?php echo strip_tags($novel['description']); ?></textarea>
          </div>
        </div>
        <textarea class="form-control mb-2 vh-100" type="text" placeholder="Content" oninput="stripHtmlTags(this)" id="content" name="content" required><?php echo strip_tags($novel['content']); ?></textarea>
        <div class="btn-group w-100 gap-2">
          <button class="btn btn-outline-light fw-bold text-nowrap rounded" type="submit" name="submit">save changes</button>
          <button type="button" class="btn btn-outline-danger fw-bold text-nowrap rounded" data-bs-toggle="modal" data-bs-target="#modalDelete">
            delete this work
          </button>
        </div>
      </form>
    </div>
    <br>
    <div class="modal fade" id="modalDelete" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
          <div class="modal-header border-bottom-0">
            <h1 class="modal-title fs-5">Delete <?php echo $novel['title'] ?></h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body py-0 text-center fw-medium">
            <p>Are you sure want to delete <strong><?php echo $novel['title'] ?></strong> from your works?</p>
            <p class="small">(Warning: You can't restore back after you delete this!)</p>
            <div class="btn-group w-100 mb-3 gap-3">
              <a class="btn btn-danger px-0 rounded-3 fw-medium" href="delete.php?id=<?php echo $post_id; ?>">delete this!</a>
              <button type="button" class="btn btn-secondary px-4 rounded-3 fw-medium" data-bs-dismiss="modal">cancel</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
