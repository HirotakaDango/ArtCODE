<?php
require_once('../auth.php');

// Replace 'your-database-name.sqlite' with the actual path to your SQLite database file
$database = new SQLite3('../database.sqlite');

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
  header("Location: set_date.php");
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
        Set Your Date
      </h3>
      <p class="fw-semibold mb-4">Current date: <?php echo date("l, d F, Y", strtotime($currentborn)); ?></p>
      <div class="card border-0 bg-body-tertiary rounded-4 shadow-sm p-4 mb-4">
        <h5 class="fw-bold">
          <i class="bi bi-calendar-fill"></i> Set Date of Birth
        </h5>
        <p class="text-muted mb-4">Choose a new date to update your date of birth.</p>
        <form method="POST" action="">
          <div class="input-group">
            <input type="date" class="form-control fw-bold" name="born" placeholder="Select a date" value="<?php echo $currentborn; ?>" required>
            <span class="input-group-text"><i class="bi bi-calendar"></i></span>
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
