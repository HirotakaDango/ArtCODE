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

  // Convert newlines to <br> tags
  $desc = nl2br($desc);

  // Update the user's profile description in the database
  $stmt = $db->prepare('UPDATE users SET `desc` = :desc WHERE email = :email');
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
$current_desc = $row['desc'] ?? '';

// Get the success message from the session
$success_message = $_SESSION['success_message'] ?? '';

// Clear the success message from the session
unset($_SESSION['success_message']);

// Close the database connection
$db->close();
?>

    <?php include('setheader.php'); ?>
    <div class="container-fluid">
      <h3 class="mt-4 text-center fw-bold text-dark"><i class="bi bi-person-vcard-fill"></i> Edit Bio</h3>
      <?php if (!empty($success_message)) { ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
      <?php } ?>
      <form method="POST" class="mt-3">
        <div class="form-floating mb-2">
          <textarea class="form-control rounded-3 border text-dark fw-bold border-4" id="desc" name="desc" rows="5" style="height: 400px;" oninput="stripHtmlTags(this)" placeholder="Description:" maxlength="400"><?php echo strip_tags($current_desc); ?></textarea>
          <label for="floatingInput" class="text-dark fw-bold">Description:</label>
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-bold" name="submit">Save</button>
      </form>
    </div>
    <?php include('end.php'); ?>