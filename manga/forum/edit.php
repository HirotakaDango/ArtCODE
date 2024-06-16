<?php
session_start();
$db = new PDO('sqlite:database.db');
if (!isset($_SESSION['user_id'])) {
  header('Location: session.php');
}

$user_id = $_SESSION['user_id'];

// Retrieve the list of albums created by the current user
$stmt = $db->prepare('SELECT * FROM category ORDER BY category_name ASC');
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['submit'])) {
  $post_id = $_POST['post_id'];
  $title = htmlspecialchars($_POST['title']);
  $content = htmlspecialchars($_POST['content']);
  $content = nl2br($content);
  $category = htmlspecialchars($_POST['category']); // Add category handling
  $query = "UPDATE posts SET title='$title', content='$content', category='$category' WHERE id='$post_id' AND user_id='$user_id'";
  $db->exec($query);
  header("Location: reply.php?id=" . $post_id);
}

if (isset($_GET['id'])) {
  $post_id = $_GET['id'];
  $query = "SELECT * FROM posts WHERE id='$post_id' AND user_id='$user_id'";
  $post = $db->query($query)->fetch();
  if (!$post) {
    header("Location: index.php");
  }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <title>Edit <?php echo $post['title'] ?></title>
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
    <form method="post" class="container mt-3 mb-2">
      <input type="hidden" name="post_id" value="<?php echo $post_id ?>">
      <div class="form-floating mb-2">
        <input class="form-control rounded border-3 focus-ring focus-ring-dark" type="text" name="title" placeholder="Enter title" maxlength="100" required value="<?php echo $post['title'] ?>">  
        <label for="floatingInput" class="fw-bold"><small>Enter title</small></label>
      </div>
      <div class="form-floating mb-2">
        <select class="form-select border rounded border-3 fw-bold focus-ring focus-ring-dark py-0 text-start" name="category" required>
          <?php
            // Loop through each category and create an option in the dropdown list
            foreach ($results as $row) {
              $category_name = $row['category_name'];
              $id = $row['id'];
              $selected = ($category_name == $post['category']) ? 'selected' : '';
              echo '<option value="' . htmlspecialchars($category_name) . '" ' . $selected . '>' . htmlspecialchars($category_name) . '</option>';
            }
          ?>
        </select>
      </div>
      <div class="form-floating mb-2">
        <textarea class="form-control rounded border-3 focus-ring focus-ring-dark vh-100" name="content" oninput="stripHtmlTags(this)" placeholder="Enter content" required><?php echo strip_tags($post['content']) ?></textarea>
        <label for="floatingInput" class="fw-bold"><small>Enter content</small></label>
      </div>
      <div class="btn-group gap-2 w-100 mb-5">
        <a class="btn btn-danger fw-bold w-50 rounded" href="delete.php?id=<?php echo $post_id; ?>" onclick="return confirm('Are you sure?');">
          delete this thread
        </a>
        <button class="btn btn-primary fw-bold w-50 rounded" type="submit" name="submit">save changes</button>
      </div>
    </form>
  </body>
</html>