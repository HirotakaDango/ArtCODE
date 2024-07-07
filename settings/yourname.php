<?php
require_once('../auth.php');

// Connect to the SQLite database
$db = new SQLite3('../database.sqlite');

// Check if the form was submitted
if (isset($_POST['submit'])) {
  $email = $_SESSION['email'];

  // Sanitize user input
  $artist = filter_input(INPUT_POST, 'artist', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

  // Validate user input
  $errors = array();
  if (empty($artist)) {
    $errors['artist'] = 'Please enter an artist name';
  } else if (strlen($artist) > 50) {
    $errors['artist'] = 'The artist name cannot be longer than 50 characters';
  }

  // If there are no errors, update the user's artist name in the database
  if (empty($errors)) {
    $stmt = $db->prepare("UPDATE users SET artist = :artist WHERE email = :email");
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':artist', $artist);
    $stmt->execute();

    // Store the new artist name in the session for future use
    $_SESSION['artist'] = $artist;

    // Set a success message to display to the user
    $_SESSION['success'] = 'Your name has been updated';

    // Redirect the user to the homepage
    header("Location: yourname.php");
    exit;
  }
}

// Get the user's current artist name from the database
$email = $_SESSION['email'];
$stmt = $db->prepare("SELECT artist FROM users WHERE email = :email");
$stmt->bindValue(':email', $email);
$result = $stmt->execute();
$user = $result->fetchArray();
$artist = htmlspecialchars($user['artist']);

?>

    <main id="swup" class="transition-main">
      <?php include('setheader.php'); ?>
        <?php if (isset($_SESSION['success'])): ?>
          <div class="alert alert-success"><?php echo $_SESSION['success']; ?></div>
        <?php unset($_SESSION['success']); endif; ?>
        <div class="container mt-4">
          <div class="d-md-none mb-4">
            <div class="d-flex">
              <a class="text-decoration-none text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" href="/settings/">
                <i class="bi bi-chevron-left" style="-webkit-text-stroke: 2px;"></i>
              </a>
            </div>
          </div>
          <h3 class="fw-bold mb-3">
            Change Username
          </h3>
          <div class="card border-0 bg-body-tertiary rounded-4 shadow-sm p-4 mb-4">
            <h5 class="fw-bold">
              <i class="bi bi-person-circle me-2"></i> Update Username
            </h5>
            <p class="text-muted mb-4">Edit your username below. Choose between a light or dark theme to adjust the appearance of the website to your preference.</p>
            <form method="post" class="mt-3" action="yourname.php">
              <div class="form-floating mb-2">
                <input type="text" class="form-control rounded-3 border fw-bold border-4" id="artist" name="artist" value="<?php echo $artist; ?>" maxlength="40">
                <label for="artist" class="fw-bold">Your current username: <?php echo $artist; ?></label>
              </div>
              <button type="submit" class="btn btn-primary w-100 fw-bold" name="submit">Save</button>
            </form>
          </div>
        </div>
      <?php include('end.php'); ?>
    </main>