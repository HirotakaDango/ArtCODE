<?php
require_once('../auth.php');

// Connect to the database
$db = new SQLite3('../database.sqlite');

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
  header('Location: bio.php');
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

    <main id="swup" class="transition-main">
      <?php include('setheader.php'); ?>
        <?php if (!empty($success_message)) { ?>
          <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php } ?>
        <div class="container mt-4">
          <div class="d-md-none mb-4">
            <div class="d-flex">
              <a class="text-decoration-none text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" href="/settings/">
                <i class="bi bi-chevron-left" style="-webkit-text-stroke: 2px;"></i>
              </a>
            </div>
          </div>
          <h3 class="fw-bold mb-3">
            Change Bio
          </h3>
          <div class="card border-0 bg-body-tertiary rounded-4 shadow-sm p-4 mb-4">
            <h5 class="fw-bold">
              <i class="bi bi-person-vcard me-2"></i> Update Bio
            </h5>
            <p class="text-muted mb-4">Edit your bio description below. Choose between a light or dark theme to adjust the appearance of the website to your preference.</p>
            <form method="POST" class="mt-3">
              <div class="form-floating mb-2">
                <textarea class="form-control rounded-3 border fw-bold border-4" id="desc" name="desc" rows="5" style="height: 400px;" oninput="stripHtmlTags(this)" placeholder="Current bio:" maxlength="4400"><?php echo strip_tags($current_desc); ?></textarea>
                <label for="desc" class="fw-bold">Current bio:</label>
              </div>
              <button type="submit" class="btn btn-primary w-100 fw-bold" name="submit">Save</button>
            </form>
          </div>
        </div>
      <?php include('end.php'); ?>
    </main>