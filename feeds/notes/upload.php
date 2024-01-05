<?php
require_once('auth.php');

$email = $_SESSION['email'];

$db = new PDO('sqlite:../../database.sqlite');

if (isset($_POST['submit'])) {
  // Sanitize and filter user inputs
  $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $tags = filter_input(INPUT_POST, 'tags', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $content = nl2br($content);
  $date = date('Y/m/d'); // format the current date as "YYYY-MM-DD"

  // Prepare and execute the SQL query
  $stmt = $db->prepare("INSERT INTO posts (title, content, tags, email, date) VALUES (:title, :content, :tags, :email, :date)");
  $stmt->execute(array(':title' => $title, ':content' => $content, ':tags' => $tags, ':email' => $_SESSION['email'], ':date' => $date));

  // Redirect to the desired location
  header('Location: ' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/feeds/notes/');
  exit(); // Add this line to stop script execution after redirect
}
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
    <main id="swup" class="transition-main">
    <?php include('header.php'); ?>
    <form method="post" enctype="multipart/form-data" class="container-fluid mt-3">
      <div class="input-group gap-3 mb-2">
        <div class="form-floating">
          <input class="form-control border-top-0 border-start-0 border-end-0 rounded-bottom-0 border-3 focus-ring focus-ring-dark" type="text" name="title" placeholder="Enter title" maxlength="100" required>  
          <label for="floatingInput" class="fw-bold"><small>Enter title</small></label>
        </div>
        <div class="form-floating">
          <input class="form-control border-top-0 border-start-0 border-end-0 rounded-bottom-0 border-3 focus-ring focus-ring-dark" type="text" name="tags" placeholder="Enter genre" maxlength="50" required>  
          <label for="floatingInput" class="fw-bold"><small>Enter genre</small></label>
        </div>
      </div>
      <div class="form-floating mb-2">
        <textarea class="form-control rounded border-3 focus-ring focus-ring-dark vh-100" name="content" onkeydown="if(event.keyCode == 13) { document.execCommand('insertHTML', false, '<br><br>'); return false; }" placeholder="Enter content" required></textarea>
        <label for="floatingInput" class="fw-bold"><small>Enter content</small></label>
      </div>
      <button class="btn btn-primary fw-bold mb-5 w-100" type="submit" name="submit">Submit</button>
    </form>
    </main>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
