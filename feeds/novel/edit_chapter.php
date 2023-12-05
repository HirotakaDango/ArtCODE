<?php
require_once('../../auth.php');

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
  $title = $_POST['title'];
  $content = $_POST['content'];

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
$chapterQuery = "SELECT * FROM chapter WHERE novel_id = :novel_id AND id = :chapter_id";
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
    <div class="container-fluid">
      <a class="btn fw-medium btn-outline-light position-absolute top-0 start-0 m-3" href="read.php?novel_id=<?php echo $novel_id; ?>&chapter_id=<?php echo $existingChapter['id']; ?>">back</a>
      <h2 class="my-4 text-center">Edit Chapter</h2>
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
