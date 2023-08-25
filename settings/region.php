<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  header("Location: ../session.php");
  exit;
}

// Replace 'your-database-name.sqlite' with the actual path to your SQLite database file
$database = new SQLite3('../database.sqlite');

// Get the current region of the user from the database
$query = "SELECT region FROM users WHERE email = :email";
$statement = $database->prepare($query);
$statement->bindValue(':email', $_SESSION['email']);
$result = $statement->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);
$currentregion = $row['region'];

// Process the form submission if the user has selected a new region
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get the selected region from the form
  $selectedregion = $_POST['region'];

  // Update the user's region in the database
  $query = "UPDATE users SET region = :region WHERE email = :email";
  $statement = $database->prepare($query);
  $statement->bindValue(':region', $selectedregion);
  $statement->bindValue(':email', $_SESSION['email']);
  $statement->execute();

  // Redirect the user to a success page or any other desired location
  header("Location: region.php");
  exit;
}

// Close the database connection
$database->close();
?>

  <?php include ('setheader.php');?>
  <div class="container mt-4">
    <h3 class="text-center fw-bold"><i class="bi bi-globe-asia-australia"></i> Change Region</h3>
    <p class="fw-semibold">Current region: <?php echo $currentregion; ?></p>
    <form method="POST" action="">
      <div class="form-group">
        <label class="fw-semibold mb-3" for="region">Select region:</label>
        <select class="form-select" id="region" name="region">
          <option value="Australia" <?php if ($currentregion == 'Australia') echo 'selected'; ?>>Australia</option>
          <option value="Canada" <?php if ($currentregion == 'Canada') echo 'selected'; ?>>Canada</option>
          <option value="China" <?php if ($currentregion == 'China') echo 'selected'; ?>>China</option>
          <option value="German" <?php if ($currentregion == 'German') echo 'selected'; ?>>German</option>
          <option value="Indonesia" <?php if ($currentregion == 'Indonesia') echo 'selected'; ?>>Indonesia</option>
          <option value="Japan" <?php if ($currentregion == 'Japan') echo 'selected'; ?>>Japan</option>
          <option value="Malaysia" <?php if ($currentregion == 'Malaysia') echo 'selected'; ?>>Malaysia</option>
          <option value="Singapore" <?php if ($currentregion == 'Singapore') echo 'selected'; ?>>Singapore</option>
          <option value="South Korea" <?php if ($currentregion == 'South Korea') echo 'selected'; ?>>South Korea</option>
          <option value="Taiwan" <?php if ($currentregion == 'Taiwan') echo 'selected'; ?>>Taiwan</option>
          <option value="United Kingdom" <?php if ($currentregion == 'United Kingdom') echo 'selected'; ?>>United Kingdom</option>
          <option value="United States" <?php if ($currentregion == 'United States') echo 'selected'; ?>>United States</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary w-100 fw-bold mt-2">Save</button>
    </form>
  </div>
  <?php include('end.php'); ?> 