<?php
require_once('../auth.php');

// Replace 'your-database-name.sqlite' with the actual path to your SQLite database file
$database = new SQLite3('../database.sqlite');

// Get the current page of the user from the database
$query = "SELECT display FROM users WHERE email = :email";
$statement = $database->prepare($query);
$statement->bindValue(':email', $_SESSION['email']);
$result = $statement->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);
$currentpage = $row['display'];

// Process the form submission if the user has selected a new page
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get the selected page from the form
  $selectedpage = $_POST['page'];

  // Update the user's page in the database
  $query = "UPDATE users SET display = :display WHERE email = :email";
  $statement = $database->prepare($query);
  $statement->bindValue(':display', $selectedpage);
  $statement->bindValue(':email', $_SESSION['email']);
  $statement->execute();

  // Redirect the user to a success page or any other desired location
  header("Location: display.php");
  exit;
}

// Close the database connection
$database->close();
?>

    <main id="swup" class="transition-main">
    <?php include ('setheader.php');?>
    <div class="container mt-4">
      <h3 class="text-center fw-bold"><i class="bi bi-display"></i> Change page</h3>
      <p class="fw-semibold">Current page: <?php echo $currentpage; ?></p>
      <form method="POST" action="">
        <div class="form-group">
          <label class="fw-semibold mb-3" for="page">Select page:</label>
          <select class="form-select" id="page" name="page">
            <option value="simple_view" <?php if ($currentpage == '20') echo 'selected'; ?>>simple view</option>
            <option value="view" <?php if ($currentpage == '10') echo 'selected'; ?>>full view</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-bold mt-2">Save</button>
      </form>
    </div>
    <?php include('end.php'); ?> 
    </main>