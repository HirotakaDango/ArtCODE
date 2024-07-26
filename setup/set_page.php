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
  header("Location: set_page.php");
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
        Change Page Number
      </h3>
      <div class="card border-0 bg-body-tertiary rounded-4 shadow-sm p-4 mb-4">
        <h5 class="fw-bold">
          <i class="bi bi-file-earmark-fill me-2"></i> Select Number
        </h5>
        <p class="text-muted mb-4">Choose a number to adjust how many images to display on each page. We recommend you to choose 12 to load images faster.</p>
        <form method="POST" action="">
          <div class="form-group">
            <label class="fw-semibold mb-3" for="page">Select number:</label>
            <select class="form-select" id="page" name="page">
              <option value="480" <?php if ($currentpage == '480') echo 'selected'; ?>>480</option>
              <option value="240" <?php if ($currentpage == '240') echo 'selected'; ?>>240</option>
              <option value="228" <?php if ($currentpage == '228') echo 'selected'; ?>>228</option>
              <option value="216" <?php if ($currentpage == '216') echo 'selected'; ?>>216</option>
              <option value="204" <?php if ($currentpage == '204') echo 'selected'; ?>>204</option>
              <option value="192" <?php if ($currentpage == '192') echo 'selected'; ?>>192</option>
              <option value="180" <?php if ($currentpage == '180') echo 'selected'; ?>>180</option>
              <option value="168" <?php if ($currentpage == '168') echo 'selected'; ?>>168</option>
              <option value="156" <?php if ($currentpage == '156') echo 'selected'; ?>>156</option>
              <option value="144" <?php if ($currentpage == '144') echo 'selected'; ?>>144</option>
              <option value="132" <?php if ($currentpage == '132') echo 'selected'; ?>>132</option>
              <option value="120" <?php if ($currentpage == '120') echo 'selected'; ?>>120</option>
              <option value="108" <?php if ($currentpage == '108') echo 'selected'; ?>>108</option>
              <option value="96" <?php if ($currentpage == '96') echo 'selected'; ?>>96</option>
              <option value="84" <?php if ($currentpage == '84') echo 'selected'; ?>>84</option>
              <option value="72" <?php if ($currentpage == '72') echo 'selected'; ?>>72</option>
              <option value="60" <?php if ($currentpage == '60') echo 'selected'; ?>>60</option>
              <option value="48" <?php if ($currentpage == '48') echo 'selected'; ?>>48</option>
              <option value="36" <?php if ($currentpage == '36') echo 'selected'; ?>>36</option>
              <option value="24" <?php if ($currentpage == '24') echo 'selected'; ?>>24</option>
              <option value="12" <?php if ($currentpage == '12') echo 'selected'; ?>>12</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary w-100 fw-bold mt-2">Save</button>
        </form>
      </div>
      <div class="d-flex">
        <div class="ms-auto btn-group gap-2 mt-2">
          <a href="../index.php" class="btn border-0 text-danger rounded fw-medium">Skip</a>
          <a href="set_display.php" class="btn border-0 text-dark rounded fw-medium">Next</a>
        </div>
      </div>
    </div>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>