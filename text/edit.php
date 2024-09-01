<?php
require_once('../auth.php');

// Connect to the SQLite database
$db = new SQLite3('../database.sqlite');

$email = $_SESSION['email'];

// Get the text ID from the query parameters
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch the text content from the database
$stmt = $db->prepare('
  SELECT texts.*, users.artist, users.id AS uid 
  FROM texts 
  LEFT JOIN users ON texts.email = users.email 
  WHERE texts.id = :id
');
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

// Check if the user is the owner
if (!$result || $result['email'] !== $email) {
  header('Location: /text/');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['update'])) {
    // Sanitize and update the text
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING) ?: $result['title'];
    $tags = filter_input(INPUT_POST, 'tags', FILTER_SANITIZE_STRING) ?: $result['tags'];
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING) ?: $result['content'];

    // Convert newlines to <br> tags
    $content = nl2br($content);

    $stmt = $db->prepare('
      UPDATE texts 
      SET title = :title, tags = :tags, content = :content 
      WHERE id = :id
    ');
    $stmt->bindValue(':title', $title, SQLITE3_TEXT);
    $stmt->bindValue(':tags', $tags, SQLITE3_TEXT);
    $stmt->bindValue(':content', $content, SQLITE3_TEXT);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

    if ($stmt->execute()) {
      echo json_encode(['status' => 'success']);
    } else {
      echo json_encode(['status' => 'error', 'message' => 'Failed to update text.']);
    }
    exit();
  } elseif (isset($_POST['delete'])) {
    // Delete the text
    $stmt = $db->prepare('
      DELETE FROM texts 
      WHERE id = :id
    ');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

    if ($stmt->execute()) {
      header('Location: /text/');
      exit();
    } else {
      echo 'Failed to delete text.';
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit <?php echo $result['title']; ?></title>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
      $(document).ready(function() {
        $('#save-button').click(function(e) {
          e.preventDefault();
          saveContent();
        });

        $(document).keydown(function(e) {
          if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            saveContent();
          }
        });

        function saveContent() {
          $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
              update: true,
              title: $('#title').val(),
              tags: $('#tags').val(),
              content: $('#content').val()
            },
            success: function(response) {
              const result = JSON.parse(response);
              if (result.status === 'success') {
                alert('Changes saved successfully!');
              } else {
                alert('Failed to save changes: ' + result.message);
              }
            },
            error: function() {
              alert('An error occurred while saving.');
            }
          });
        }
      });
    </script>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <div class="container my-3">
      <nav aria-label="breadcrumb">
        <div class="d-none d-md-block d-lg-block">
          <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-3" style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);">
            <li class="breadcrumb-item">
              <a class="link-body-emphasis fw-medium text-decoration-none" href="/">
                Home
              </a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis fw-medium text-decoration-none" href="/text/">Text</a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis fw-medium text-decoration-none" href="/text/?uid=<?php echo $result['uid']; ?>"><?php echo $result['artist']; ?></a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis fw-medium text-decoration-none" href="/text/view.php?id=<?php echo $result['id']; ?>"><?php echo $result['title']; ?></a>
            </li>
            <?php if ($email === $result['email']): ?>
              <li class="breadcrumb-item active fw-bold" aria-current="page">
                Edit
              </li>
            <?php endif; ?>
          </ol>
        </div>
        <div class="d-md-none d-lg-none">
          <a class="btn fw-bold w-100 text-start rounded p-3 bg-body-tertiary mb-2" data-bs-toggle="collapse" href="#collapseModal" role="button" aria-expanded="false" aria-controls="collapseExample">
            <i class="bi bi-list" style="-webkit-text-stroke: 1px;"></i> Menu
          </a>
          <div class="collapse bg-body-tertiary mb-2 rounded" id="collapseModal">
            <div class="btn-group-vertical w-100">
              <a class="btn py-2 rounded text-start fw-medium" href="/">Home</a>
              <a class="btn py-2 rounded text-start fw-medium" href="/text/">Text</a>
              <a class="btn py-2 rounded text-start fw-medium" href="/text/?uid=<?php echo $result['uid']; ?>"><?php echo $result['artist']; ?></a>
              <a class="btn py-2 rounded text-start fw-mediun" href="view.php?id=<?php echo $id; ?>"><?php echo $result['title']; ?></a>
              <?php if ($email === $result['email']): ?>
                <a class="btn py-2 rounded text-start fw-bold" href="edit.php?id=<?php echo $result['id']; ?>">
                  <i class="bi bi-chevron-right small" style="-webkit-text-stroke: 2px;"></i> Edit
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </nav>
    </div>
    <div class="container mt-2">
      <h1 class="fw-bold text-center display-5 mb-5" style="word-wrap: break-word; overflow-wrap: break-word;">Edit <?php echo $result['title']; ?></h1>
      <form method="post">
        <div class="form-floating mb-2">
          <input class="form-control rounded-3 fw-medium border-0 shadow bg-body-tertiary" type="text" name="title" id="title" placeholder="Enter title for your text" maxlength="500" value="<?php echo $result['title']; ?>" required>
          <label for="title" class="fw-medium">Enter title for your text</label>
        </div>
        <div class="form-floating mb-2">
          <input class="form-control rounded-3 fw-medium border-0 shadow bg-body-tertiary" type="text" name="tags" id="tags" placeholder="Enter tags for your text" maxlength="500" value="<?php echo $result['tags']; ?>">
          <label for="tags" class="fw-medium">Enter tags for your text</label>
        </div>
        <div class="form-floating mb-2">
          <textarea class="form-control rounded-3 fw-medium border-0 shadow bg-body-tertiary vh-100" name="content" id="content" placeholder="Enter description for your text" required><?php echo strip_tags($result['content']); ?></textarea>
          <label for="content" class="fw-medium">Enter description</label>
        </div>
        <div class="btn-group w-100 gap-2">
          <button type="submit" name="update" class="btn btn-primary fw-medium w-50 rounded" id="save-button">UPDATE</button>
          <button type="submit" name="delete" class="btn btn-danger fw-medium w-50 rounded" onclick="return confirm('Are you sure you want to delete <?php echo $result['title']; ?>?')">DELETE</button>
        </div>
      </form>
    </div>
    <div class="mt-5"></div>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>