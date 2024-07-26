<?php
require_once('../auth.php');

// Replace 'your-database-name.sqlite' with the actual path to your SQLite database file
$database = new SQLite3('../database.sqlite');

// Get the current page of the user from the database
$query = "SELECT numpage FROM users WHERE email = :email";
$statement = $database->prepare($query);
$statement->bindValue(':email', $_SESSION['email']);
$result = $statement->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);
$currentpage = $row['numpage'];

// Process the form submission if the user has selected a new page
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get the selected page from the form
  $selectedpage = $_POST['page'];

  // Update the user's page in the database
  $query = "UPDATE users SET numpage = :numpage WHERE email = :email";
  $statement = $database->prepare($query);
  $statement->bindValue(':numpage', $selectedpage);
  $statement->bindValue(':email', $_SESSION['email']);
  $statement->execute();

  // Redirect the user to a success page or any other desired location
  header("Location: set_display.php");
  exit;
}

// Close the database connection
$database->close();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container mb-5 mt-4">
      <h3 class="fw-bold mb-3">
        Change Image Display Mode
      </h3>
      <div class="card border-0 bg-body-tertiary rounded-4 shadow-sm p-4 mb-4">
        <h5 class="fw-bold">
          <i class="bi bi-display-fill me-2"></i> Select Mode
        </h5>
        <p class="text-muted mb-4">Choose between a simple or full view mode to adjust how images are displayed on the website. We recommend you to choose simple view to load images faster.</p>
        <form method="POST" action="">
          <div class="form-group">
            <label class="fw-semibold mb-3" for="page">Select mode:</label>
            <select class="form-select" id="page" name="page">
              <option value="simple_view" <?php if ($currentpage == 'simple_view') echo 'selected'; ?>>Simple View</option>
              <option value="view" <?php if ($currentpage == 'view') echo 'selected'; ?>>Full View</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary w-100 fw-bold mt-2">Save</button>
        </form>
      </div>
      <div class="d-flex">
        <div class="ms-auto btn-group gap-2 mt-2">
          <a href="../index.php" class="btn border-0 text-danger rounded fw-medium">Skip</a>
          <a href="set_picture.php" class="btn border-0 text-dark rounded fw-medium">Next</a>
        </div>
      </div>
    </div>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>