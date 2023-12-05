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

// Check and create the chapter table if it doesn't exist
$createTableQuery = "
    CREATE TABLE IF NOT EXISTS chapter (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        novel_id INTEGER,
        email VARCHAR(255),
        title TEXT,
        content TEXT,
        FOREIGN KEY (novel_id) REFERENCES novel(id),
        FOREIGN KEY (email) REFERENCES users(email)
    );
";
$db->exec($createTableQuery);

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the form data
    $content = $_POST['content'];

    // Insert the new chapter into the database
    $insertQuery = "INSERT INTO chapter (novel_id, email, content) VALUES (:novel_id, :email, :content)";
    $insertStatement = $db->prepare($insertQuery);
    $insertStatement->bindParam(':novel_id', $id);
    $insertStatement->bindParam(':email', $_SESSION['email']);
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
    <div class="container-fluid">
      <a class="btn fw-medium btn-outline-light position-absolute top-0 start-0 m-3" href="view.php?id=<?php echo $id; ?>">back</a>
      <h2 class="my-4 text-center">Upload Chapter</h2>
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
