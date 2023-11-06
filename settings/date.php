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
  header("Location: date.php");
  exit;
}

// Close the database connection
$database->close();
?>

    <main id="swup" class="transition-main">
    <?php include ('setheader.php');?>
    <div class="container mt-4">
      <h3 class="text-center fw-bold"><i class="bi bi-calendar-fill"></i> Change Date</h3>
      <p class="fw-semibold">Current date: <?php echo $currentborn; ?></p>
      <form method="POST" action="">
        <div class="input-group">
          <input type="date" class="form-control fw-bold" name="born" placeholder="Select a date" value="<?php echo $currentborn; ?>" required>
          <span class="input-group-text"><i class="bi bi-calendar"></i></span>
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-bold mt-2">Save</button>
      </form>
    </div>
    <?php include('end.php'); ?>
    </main>