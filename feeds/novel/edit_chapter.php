<?php
require_once('auth.php');

$db = new PDO('sqlite:../../database.sqlite');

// Check if the novel and chapter IDs are provided in the URL
if (!isset($_GET['novel_id']) || !isset($_GET['chapter_id'])) {
  // Redirect to an error page or handle it as appropriate for your application
  header("Location: error.php");
  exit();
}

$novel_id = $_GET['novel_id'];
$chapter_id = $_GET['chapter_id'];

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  // Redirect to the novel reading page if the user is not logged in
  header("Location: read.php?novel_id=" . $novel_id . "&chapter_id=" . $chapter_id);
  exit();
}

$email = $_SESSION['email'];

// Check if the logged-in user is the creator of the chapter
$chapterQuery = "SELECT email FROM chapter WHERE novel_id = :novel_id AND id = :chapter_id";
$chapterStatement = $db->prepare($chapterQuery);
$chapterStatement->bindParam(':novel_id', $novel_id);
$chapterStatement->bindParam(':chapter_id', $chapter_id);
$chapterStatement->execute();
$chapterData = $chapterStatement->fetch(PDO::FETCH_ASSOC);

if (!$chapterData || $chapterData['email'] !== $email) {
  // Redirect to the novel reading page if the logged-in user is not the creator
  header("Location: read.php?novel_id=" . $novel_id . "&chapter_id=" . $chapter_id);
  exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Retrieve the form data
  $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

  // Update the existing chapter in the database
  $updateQuery = "UPDATE chapter SET title = :title, content = :content WHERE novel_id = :novel_id AND id = :chapter_id";
  $updateStatement = $db->prepare($updateQuery);
  $updateStatement->bindParam(':title', $title);
  $updateStatement->bindParam(':content', $content);
  $updateStatement->bindParam(':novel_id', $novel_id);
  $updateStatement->bindParam(':chapter_id', $chapter_id);

  if ($updateStatement->execute()) {
    // Redirect to the novel reading page after successful update
    header("Location: read.php?novel_id=" . $novel_id . "&chapter_id=" . $chapter_id);
    exit();
  } else {
    // Handle the error, e.g., display an error message
    $error = "Failed to update chapter. Please try again.";
  }
}

// Fetch existing chapter details for pre-filling the form
$chapterQuery = "SELECT chapter.*, novel.title AS novel_title
                 FROM chapter 
                 INNER JOIN novel ON chapter.novel_id = novel.id
                 WHERE chapter.novel_id = :novel_id AND chapter.id = :chapter_id";
$chapterStatement = $db->prepare($chapterQuery);
$chapterStatement->bindParam(':novel_id', $novel_id);
$chapterStatement->bindParam(':chapter_id', $chapter_id);
$chapterStatement->execute();
$existingChapter = $chapterStatement->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <title>Edit Chapter</title>
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
              <a class="link-body-emphasis py-2 text-decoration-none text-white" href="view.php?id=<?php echo $novel_id; ?>"><?php echo $existingChapter['novel_title']; ?></a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis py-2 text-decoration-none text-white" href="read.php?id=<?php echo $existingChapter['id']; ?>"><?php echo $existingChapter['title']; ?></a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis py-2 text-decoration-none text-white fw-bold" href="edit_chapter.php?novel_id=<?php echo $novel_id; ?>&chapter_id=<?php echo $existingChapter['id']; ?>">
                Edit <?php echo $existingChapter['title']; ?>
              </a>
            </li>
          </ol>
        </div>
        <div class="d-md-none d-lg-none">
          <a class="btn bg-body-tertiary fw-bold mb-2 p-3 w-100 text-start rounded" data-bs-toggle="collapse" href="#collapseModal" role="button" aria-expanded="false" aria-controls="collapseExample">
            <i class="bi bi-list" style="-webkit-text-stroke: 1px;"></i> Menu
          </a>
          <div class="collapse bg-body-tertiary mb-4 rounded" id="collapseModal">
            <div class="btn-group-vertical w-100">
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">ArtCODE</a>
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/novel/">Home</a>
              <a class="btn py-2 rounded text-start fw-medium" href="view.php?id=<?php echo $novel_id; ?>"><?php echo $existingChapter['novel_title']; ?></a>
              <a class="btn py-2 rounded text-start fw-medium" href="read.php?novel_id=<?php echo $novel_id; ?>&chapter_id=<?php echo $existingChapter['id']; ?>"><?php echo $existingChapter['title']; ?></a>
              <a class="btn py-2 rounded text-start fw-bold" href="edit_chapter.php?novel_id=<?php echo $novel_id; ?>&chapter_id=<?php echo $existingChapter['id']; ?>">
                <i class="bi bi-chevron-right small" style="-webkit-text-stroke: 2px;"></i> Edit <?php echo $existingChapter['title']; ?>
              </a>
            </div>
          </div>
        </div>
      </nav>
      <h2 class="mb-4 mt-3 text-center">Edit <?php echo $existingChapter['title']; ?></h2>
      <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
          <?= $error ?>
        </div>
      <?php endif; ?>
      <form class="mb-5" method="post">
        <div class="form-floating mb-2">
          <input class="form-control border border-3 rounded-4" type="text" id="title" placeholder="title" name="title" value="<?php echo $existingChapter['title']; ?>" required>
          <label class="fw-medium" for="title">Title</label>
        </div>
        <div class="form-floating mb-2">
          <textarea class="form-control vh-100 border border-3 rounded-4" type="text" id="content" placeholder="content" oninput="stripHtmlTags(this)" name="content" required><?php echo $existingChapter['content']; ?></textarea>
          <label class="fw-medium" for="content">Content</label>
        </div>
        <button type="submit" class="btn btn-secondary w-100 border border-3 rounded-4 fw-medium">update chapter</button>
      </form>
    </div>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
