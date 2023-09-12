<?php
require_once('auth.php');

// Replace 'your-database-name.sqlite' with the actual path to your SQLite database file
$database = new SQLite3('database.sqlite');

// Get the current born of the user from the database
$query = "SELECT born FROM users WHERE email = :email";
$statement = $database->prepare($query);
$statement->bindValue(':email', $_SESSION['email']);
$result = $statement->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);
$currentborn = $row['born'];

// Process the form submission if the user has selected a new born
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get the selected born from the form
  $selectedborn = $_POST['born'];

  // Update the user's born in the database
  $query = "UPDATE users SET born = :born WHERE email = :email";
  $statement = $database->prepare($query);
  $statement->bindValue(':born', $selectedborn);
  $statement->bindValue(':email', $_SESSION['email']);
  $statement->execute();

  // Redirect the user to a success page or any other desired location
  header("Location: regpic.php");
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
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container mt-3">
      <h3 class="text-center fw-bold"><i class="bi bi-calendar-fill"></i> Choose Date</h3>
      <form method="POST" action="">
        <div class="input-group">
          <div class="form-floating">
            <input name="born" type="date" class="form-control fw-bold" id="floatingInput" placeholder="Select date yy/mm/dd" required>
            <label class="fw-bold text-secondary" for="floatingInput">Select date yy/mm/dd</label>
          </div>
          <span class="input-group-text"><i class="bi bi-calendar-fill"></i></span>
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-bold mt-2">Save</button>
      </form>
      <header class="d-flex justify-content-center py-3">
        <ul class="nav nav-pills">
          <li class="nav-item"><a href="index.php" class="btn btn-danger ms-1 fw-bold">Skip</a></li>
          <li class="nav-item"><a href="regpic.php" class="btn btn-primary ms-1 fw-bold">Next</a></li>
        </ul>
      </header>
    </div>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
