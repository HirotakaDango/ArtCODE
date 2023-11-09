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
  header("Location: page.php");
  exit;
}

// Close the database connection
$database->close();
?>

    <main id="swup" class="transition-main">
    <?php include ('setheader.php');?>
    <div class="container mt-4">
      <h3 class="text-center fw-bold"><i class="bi bi-images"></i> Change page</h3>
      <p class="fw-semibold">Current page: <?php echo $currentpage; ?></p>
      <form method="POST" action="">
        <div class="form-group">
          <label class="fw-semibold mb-3" for="page">Select page:</label>
          <select class="form-select" id="page" name="page">
            <option value="250" <?php if ($currentpage == '250') echo 'selected'; ?>>250</option>
            <option value="200" <?php if ($currentpage == '200') echo 'selected'; ?>>200</option>
            <option value="150" <?php if ($currentpage == '150') echo 'selected'; ?>>150</option>
            <option value="100" <?php if ($currentpage == '100') echo 'selected'; ?>>100</option>
            <option value="50" <?php if ($currentpage == '50') echo 'selected'; ?>>50</option>
            <option value="30" <?php if ($currentpage == '30') echo 'selected'; ?>>30</option>
            <option value="20" <?php if ($currentpage == '20') echo 'selected'; ?>>20</option>
            <option value="10" <?php if ($currentpage == '10') echo 'selected'; ?>>10</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-bold mt-2">Save</button>
      </form>
    </div>
    <?php include('end.php'); ?> 
    </main>