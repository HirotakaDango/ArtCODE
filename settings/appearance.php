<?php
require_once('../auth.php');

// Replace 'your-database-name.sqlite' with the actual path to your SQLite database file
$database = new SQLite3('../database.sqlite');

// Get the current page of the user from the database
$query = "SELECT mode FROM users WHERE email = :email";
$statement = $database->prepare($query);
$statement->bindValue(':email', $_SESSION['email']);
$result = $statement->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);
$currenttheme = $row['mode'];

// Process the form submission if the user has selected a new page
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get the selected page from the form
  $selectedpage = $_POST['page'];

  // Update the user's page in the database
  $query = "UPDATE users SET mode = :mode WHERE email = :email";
  $statement = $database->prepare($query);
  $statement->bindValue(':mode', $selectedpage);
  $statement->bindValue(':email', $_SESSION['email']);
  $statement->execute();

  // Redirect the user to a success page or any other desired location
  header("Location: appearance.php");
  exit;
}

// Close the database connection
$database->close();
?>

    <main id="swup" class="transition-main">
      <?php include('setheader.php'); ?>
        <div class="container mb-5 mt-4">
          <div class="d-md-none mb-4">
            <div class="d-flex">
              <a class="text-decoration-none text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" href="/settings/">
                <i class="bi bi-chevron-left" style="-webkit-text-stroke: 2px;"></i>
              </a>
            </div>
          </div>
          <h3 class="fw-bold mb-3">
            Change Theme Appearance
          </h3>
          <p class="fw-semibold mb-4">Current theme appearance: <?php echo ($currenttheme == 'dark') ? 'dark' : 'light'; ?></p>
          <div class="card border-0 bg-body-tertiary rounded-4 shadow-sm p-4 mb-4">
            <h5 class="fw-bold">
              <i class="bi bi-palette-fill me-2"></i> Select Mode
            </h5>
            <p class="text-muted mb-4">Choose between a light or dark theme to adjust the appearance of the website to your preference.</p>
            <form method="POST" action="">
              <div class="form-group mb-2">
                <label class="fw-semibold mb-2" for="page">Select mode:</label>
                <select class="form-select" id="page" name="page">
                  <option value="light" <?php if ($currenttheme == 'light') echo 'selected'; ?>>light</option>
                  <option value="dark" <?php if ($currenttheme == 'dark') echo 'selected'; ?>>dark</option>
                </select>
              </div>
              <button type="submit" class="btn btn-primary w-100 fw-bold">Save</button>
            </form>
          </div>
        </div>
      <?php include('end.php'); ?> 
    </main>