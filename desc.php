<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
  header('Location: session.php');
  exit();
}

// Connect to the database
$db = new SQLite3('database.sqlite');

// Handle the user's form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Get the user's ID from the session
  $username = $_SESSION['username'];

  // Get the user's input and sanitize it
  $desc = filter_var($_POST['desc'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

  // Update the user's profile description in the database
  $stmt = $db->prepare('UPDATE users SET desc = :desc WHERE username = :username');
  $stmt->bindValue(':desc', $desc, SQLITE3_TEXT);
  $stmt->bindValue(':username', $username, SQLITE3_TEXT);
  $stmt->execute();

  // Redirect to the profile page
  header('Location: desc.php');
  exit();
}

// Get the user's current profile description from the database
$username = $_SESSION['username'];
$stmt = $db->prepare('SELECT desc FROM users WHERE username = :username');
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);
$current_desc = htmlspecialchars($row['desc']);

// Close the database connection
$db->close();
?>

    <?php include('setheader.php'); ?>
    <div class="container">
      <h3 class="mt-4 text-center fw-bold text-secondary">Edit Description</h3>
      <form method="POST">
        <div class="form-floating mb-3">
          <textarea class="form-control rounded-3 border text-secondary fw-bold border-4" id="desc" name="desc" rows="5" style="height: 200px;" placeholder="Description:" maxlength="400"><?php echo htmlspecialchars($current_desc); ?></textarea>
          <label for="floatingInput" class="text-secondary fw-bold">Description:</label>
        </div>
        <header class="d-flex justify-content-center py-3">
          <ul class="nav nav-pills">
            <li class="nav-item"><button type="submit" class="btn btn-primary fw-bold">Save</button></li>
            <li class="nav-item d-md-none d-lg-none"><a href="setting.php" class="btn btn-danger ms-1 fw-bold">Back</a></li>
          </ul>
        </header>
      </form>
    </div>
    <?php include('end.php'); ?>