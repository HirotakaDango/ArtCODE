<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  header('Location: session.php');
  exit();
}

// Connect to the database
$db = new SQLite3('database.sqlite');

// Handle the user's form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Get the user's ID from the session
  $email = $_SESSION['email'];

  // Get the user's input and sanitize it
  $desc = filter_var($_POST['desc'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

  // Update the user's profile description in the database
  $stmt = $db->prepare('UPDATE users SET desc = :desc WHERE email = :email');
  $stmt->bindValue(':desc', $desc, SQLITE3_TEXT);
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->execute();

  // Set the success message
  $_SESSION['success_message'] = 'Successfully updated your profile description.';

  // Redirect to the profile page
  header('Location: desc.php');
  exit();
}

// Get the user's current profile description from the database
$email = $_SESSION['email'];
$stmt = $db->prepare('SELECT desc FROM users WHERE email = :email');
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);
$current_desc = htmlspecialchars($row['desc'] ?? '');

// Get the success message from the session
$success_message = $_SESSION['success_message'] ?? '';

// Clear the success message from the session
unset($_SESSION['success_message']);

// Close the database connection
$db->close();
?>

    <?php include('setheader.php'); ?>
    <div class="container">
      <h3 class="mt-4 text-center fw-bold text-secondary">Edit Description</h3>
      <?php if (!empty($success_message)) { ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
      <?php } ?>
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