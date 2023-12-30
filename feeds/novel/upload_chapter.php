<?php
require_once('../../auth.php');

$db = new PDO('sqlite:../../database.sqlite');

// Check if the novel ID is provided in the URL
if (!isset($_GET['id'])) {
  // Redirect to an error page or handle it as appropriate for your application
  header("Location: error.php");
  exit();
}

$id = $_GET['id'];

// Fetch novel details using JOIN
$novelQuery = "SELECT novel.id, novel.email, novel.title as novel_title, chapter.title as chapter_title, chapter.content 
               FROM novel 
               LEFT JOIN chapter ON novel.id = chapter.novel_id 
               WHERE novel.id = :id";
$novelStatement = $db->prepare($novelQuery);
$novelStatement->bindParam(':id', $id);
$novelStatement->execute();
$novelDetails = $novelStatement->fetch(PDO::FETCH_ASSOC);

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Retrieve the form data
  $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

  // Insert the new chapter into the database
  $insertQuery = "INSERT INTO chapter (novel_id, email, title, content) VALUES (:novel_id, :email, :title, :content)";
  $insertStatement = $db->prepare($insertQuery);
  $insertStatement->bindParam(':novel_id', $id);
  $insertStatement->bindParam(':email', $_SESSION['email']);
  $insertStatement->bindParam(':title', $title);
  $insertStatement->bindParam(':content', $content);

  if ($insertStatement->execute()) {
    // Redirect to the novel viewing page after successful upload
    header("Location: view.php?id=" . $id);
    exit();
  } else {
    // Handle the error, e.g., display an error message
    $error = "Failed to upload chapter. Please try again.";
  }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <title>Upload Your Chapter</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container-fluid mt-3">
      <nav aria-label="breadcrumb">
        <div class="d-none d-md-block d-lg-block">
          <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-3" style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);">
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">
                ArtCODE
              </a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/novel/">Home</a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis py-2 text-decoration-none text-white" href="view.php?id=<?php echo $id; ?>"><?php echo $novelDetails['novel_title']; ?></a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis py-2 text-decoration-none text-white fw-bold" href="edit.php?id=<?php echo $novelDetails['id']; ?>">
                Upload new to <?php echo $novelDetails['novel_title']; ?>
              </a>
            </li>
          </ol>
        </div>
        <div class="d-md-none d-lg-none">
          <a class="btn bg-body-tertiary p-3 fw-bold w-100 text-start mb-2" data-bs-toggle="collapse" href="#collapseModal" role="button" aria-expanded="false" aria-controls="collapseExample">
            <i class="bi bi-list" style="-webkit-text-stroke: 1px;"></i> Menu
          </a>
          <div class="collapse bg-body-tertiary mb-2 rounded" id="collapseModal">
            <div class="btn-group-vertical w-100">
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">ArtCODE</a>
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/novel/">Home</a>
              <a class="btn py-2 rounded text-start fw-medium" href="view.php?id=<?php echo $id; ?>"><?php echo $novelDetails['novel_title']; ?></a>
              <a class="btn py-2 rounded text-start fw-bold" href="edit.php?id=<?php echo $novelDetails['id']; ?>">
                <i class="bi bi-chevron-right small" style="-webkit-text-stroke: 2px;"></i> Upload new to <?php echo $novelDetails['novel_title']; ?>
              </a>
            </div>
          </div>
        </div>
      </nav>
      <h2 class="mb-4 mt-3 text-center">Upload new to <?php echo $novelDetails['novel_title']; ?></h2>
      <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
          <?= $error ?>
        </div>
      <?php endif; ?>
      <form class="mb-5" method="post">
        <div class="form-floating mb-2">
          <input class="form-control border border-3 rounded-4" type="text" id="floatingInput" placeholder="title" id="title" name="title" required>
          <label class="fw-medium" for="floatingInput">title</label>
        </div>
        <div class="form-floating mb-2">
          <textarea class="form-control vh-100 border border-3 rounded-4" type="text" id="floatingTextarea" placeholder="content" oninput="stripHtmlTags(this)" id="content" name="content" required></textarea>
          <label class="fw-medium" for="floatingTextarea">content</label>
        </div>
        <button type="submit" class="btn btn-secondary w-100 border border-3 rounded-4 fw-medium">upload chapter</button>
      </form>
    </div>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
