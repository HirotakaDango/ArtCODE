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

  // Get the user's input
  $twitter = htmlspecialchars($_POST['twitter'] ?? '');
  $pixiv = htmlspecialchars($_POST['pixiv'] ?? '');
  $other = htmlspecialchars($_POST['other'] ?? '');

  // Update the user's profile description in the database
  $stmt = $db->prepare('UPDATE users SET twitter = :twitter, pixiv = :pixiv, other = :other WHERE username = :username');
  $stmt->bindValue(':twitter', $twitter, SQLITE3_TEXT);
  $stmt->bindValue(':pixiv', $pixiv, SQLITE3_TEXT);
  $stmt->bindValue(':other', $other, SQLITE3_TEXT);
  $stmt->bindValue(':username', $username, SQLITE3_TEXT);
  $stmt->execute();

  // Redirect to the profile page
  header('Location: sns.php');
  exit();
}

// Get the user's current profile description from the database
$username = $_SESSION['username'];
$stmt = $db->prepare('SELECT twitter, pixiv, other FROM users WHERE username = :username');
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);
$current_twitter = htmlspecialchars($row['twitter'] ?? '');
$current_pixiv = htmlspecialchars($row['pixiv'] ?? '');
$current_other = htmlspecialchars($row['other'] ?? '');

// Close the database connection
$db->close();
?>

    <?php include('setheader.php'); ?>
    <div class="container">
      <h3 class="mt-4 text-center fw-bold text-secondary">Edit Your Linked SNS</h3>
      <form method="POST">
        <div class="form-floating mb-3">
          <input class="form-control rounded-3 border text-secondary fw-bold border-4" id="desc" name="twitter" placeholder="Twitter:" maxlength="180" value="<?php echo htmlspecialchars($current_twitter); ?>">
          <label for="floatingInput" class="text-secondary fw-bold">Twitter: <?php echo htmlspecialchars($current_twitter); ?></label>
        </div>
        <div class="form-floating mb-3">
          <input class="form-control rounded-3 border text-secondary fw-bold border-4" id="desc" name="pixiv" placeholder="Pixiv:" maxlength="180" value="<?php echo htmlspecialchars($current_pixiv); ?>">
          <label for="floatingInput" class="text-secondary fw-bold">Pixiv: <?php echo htmlspecialchars($current_pixiv); ?></label>
        </div>
        <div class="form-floating mb-3">
          <input class="form-control rounded-3 border text-secondary fw-bold border-4" id="desc" name="other" placeholder="Other:" maxlength="180" value="<?php echo htmlspecialchars($current_other); ?>">
          <label for="floatingInput" class="text-secondary fw-bold">Additional: <?php echo htmlspecialchars($current_other); ?></label>
        </div>
        <header class="d-flex justify-content-center py-3">
          <ul class="nav nav-pills">
            <li class="nav-item"><button type="submit" class="btn btn-primary fw-bold">Save</button></li>
            <li class="nav-item d-md-none"><a href="setting.php" class="btn btn-danger ms-1 fw-bold">Back</a></li>
          </ul>
        </header>
      </form>
    </div>
    <?php include('end.php'); ?>