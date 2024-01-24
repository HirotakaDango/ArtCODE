<?php
require_once('auth.php');

$email = $_SESSION['email'];

$db = new PDO('sqlite:../../database.sqlite');

// Retrieve the list of categories associated with the current user's email
$stmt = $db->prepare('SELECT category_name FROM category WHERE email = :email ORDER BY category_name ASC');
$stmt->bindParam(':email', $email);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['submit'])) {
  // Sanitize and filter user inputs
  $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $tags = filter_input(INPUT_POST, 'tags', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $content = nl2br($content);
  $date = date('Y/m/d'); // format the current date as "YYYY-MM-DD"

  // Prepare and execute the SQL query
  $stmt = $db->prepare("INSERT INTO posts (title, content, category, tags, email, date) VALUES (:title, :content, :category, :tags, :email, :date)");
  $stmt->execute(array(':title' => $title, ':content' => $content, ':tags' => $tags, ':category' => $category, ':email' => $_SESSION['email'], ':date' => $date));

  // Redirect to the desired location
  header('Location: ' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/feeds/notes/');
  exit(); // Add this line to stop script execution after redirect
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
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <title>Upload</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container-fluid">
      <div class="row">
        <?php include('note_list.php'); ?>
        <div class="col-md-9 vh-100 overflow-y-auto">
          <div class="mt-3">
            <nav aria-label="breadcrumb">
              <div class="d-md-none d-lg-none">
                <a class="btn bg-body-tertiary p-3 fw-bold w-100 text-start mb-2" data-bs-toggle="collapse" href="#collapseModal" role="button" aria-expanded="false" aria-controls="collapseExample">
                  <i class="bi bi-list" style="-webkit-text-stroke: 1px;"></i> Menu
                </a>
                <div class="collapse bg-body-tertiary mb-2 rounded" id="collapseModal">
                  <div class="btn-group-vertical w-100">
                    <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">ArtCODE</a>
                    <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/notes/">Home</a>
                    <a class="btn py-2 rounded text-start fw-bold" href="upload.php"><i class="bi bi-chevron-right small" style="-webkit-text-stroke: 2px;"></i> Upload</a>
                  </div>
                </div>
              </div>
            </nav>
          </div>
          <form method="post" enctype="multipart/form-data" class="container-fluid mt-3">
            <div class="input-group gap-2 mb-2">
              <div class="form-floating">
                <input class="form-control rounded-4 bg-body-tertiary border-0 focus-ring focus-ring-dark" type="text" name="title" placeholder="Enter title" maxlength="100" required>  
                <label for="floatingInput" class="fw-bold"><small>Enter title</small></label>
              </div>
              <div class="form-floating">
                <input class="form-control rounded-4 bg-body-tertiary border-0 focus-ring focus-ring-dark" type="text" name="tags" placeholder="Enter genre" maxlength="50" required>  
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
                    echo '<option value="' . htmlspecialchars($category_name) . '">' . htmlspecialchars($category_name) . '</option>';
                  }
                ?>
              </select>
            </div>
            <div class="form-floating mb-2">
              <textarea class="form-control rounded-4 bg-body-tertiary border-0 focus-ring focus-ring-dark vh-100" name="content" onkeydown="if(event.keyCode == 13) { document.execCommand('insertHTML', false, '<br><br>'); return false; }" placeholder="Enter content" required></textarea>
              <label for="floatingInput" class="fw-bold"><small>Enter content</small></label>
            </div>
            <button class="btn bg-body-tertiary link-body-emphasis border-0 py-2 fw-bold mb-5 w-100 rounded-4" type="submit" name="submit">Submit</button>
          </form>
        </div>
      </div>
    </div>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
