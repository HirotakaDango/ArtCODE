<?php
require_once('auth.php');

$db = new PDO('sqlite:../../database.sqlite');

$email = $_SESSION['email'];

if (isset($_POST['submit'])) {
  $post_id = $_POST['post_id'];
  $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $tags = filter_input(INPUT_POST, 'tags', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $content = nl2br($content);

  // Get the current date and time
  $currentDateTime = date('Y/m/d');

  // Use prepared statement to prevent SQL injection
  $query = "UPDATE posts SET title=:title, tags=:tags, content=:content, date=:date WHERE id=:post_id";
  $stmt = $db->prepare($query);
  $stmt->bindParam(':title', $title);
  $stmt->bindParam(':tags', $tags);
  $stmt->bindParam(':content', $content);
  $stmt->bindParam(':date', $currentDateTime);
  $stmt->bindParam(':post_id', $post_id);
  $stmt->execute();

  header('Location: view.php?id=' . $post_id);
}

if (isset($_GET['id'])) {
  $post_id = $_GET['id'];

  // Use prepared statement to prevent SQL injection
  $query = "SELECT * FROM posts WHERE id=:post_id AND email=:email";
  $stmt = $db->prepare($query);
  $stmt->bindParam(':post_id', $post_id);
  $stmt->bindParam(':email', $email);
  $stmt->execute();

  $post = $stmt->fetch();

  if (!$post) {
    header('Location: view.php?id=' . $post_id);
  }

  $tags = filter_var($post['tags'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW); // encode tags
} else {
  header('Location: view.php?id=' . $post_id);
}

$note_query = "
  SELECT title, id AS note_id, date
  FROM posts
  WHERE email = :email
  ORDER BY note_id DESC
";
$stmt = $db->prepare($note_query);
$stmt->bindParam(':email', $email);
$stmt->execute();
$notes = $stmt->fetchAll();

// Retrieve the list of categories associated with the current user's email
$stmt = $db->prepare('SELECT category_name FROM category WHERE email = :email ORDER BY category_name ASC');
$stmt->bindParam(':email', $email);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <div class="container-fluid">
      <div class="row">
        <?php include('note_list.php'); ?>
        <div class="col-md-9 vh-100 overflow-y-auto">
          <form method="post">
            <div class="mt-3">
              <div class="d-none d-md-block d-lg-block">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-4" style="height: 65px;">
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
            </div>
            <input type="hidden" name="post_id" value="<?php echo $post_id ?>">
            <div class="input-group gap-2 mb-2">
              <div class="form-floating">
                <input class="form-control rounded-4 bg-body-tertiary border-0 focus-ring focus-ring-dark" type="text" name="title" placeholder="Enter title" maxlength="100" required value="<?php echo $post['title'] ?>">  
                <label for="floatingInput" class="fw-bold"><small>Enter title</small></label>
              </div>
              <div class="form-floating">
                <input class="form-control rounded-4 bg-body-tertiary border-0 focus-ring focus-ring-dark" type="text" name="tags" placeholder="Enter genre" maxlength="50" required value="<?php echo $post['tags'] ?>">  
                <label for="floatingInput" class="fw-bold"><small>Enter genre</small></label>
              </div>
            </div>
            <div class="form-floating mb-2">
              <select class="form-select rounded-4 bg-body-tertiary border-0 fw-bold focus-ring focus-ring-dark py-0 text-start" name="category">
                <option class="form-control" value="">Add category:</option>
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
              <textarea class="form-control rounded-4 bg-body-tertiary border-0 focus-ring focus-ring-dark vh-100" name="content" oninput="stripHtmlTags(this)" placeholder="Enter content" required><?php echo strip_tags($post['content']) ?></textarea>
              <label for="floatingInput" class="fw-bold"><small>Enter content</small></label>
            </div>
            <br>
          </div>
        </form>
      </div>
    </div>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>