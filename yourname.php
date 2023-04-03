<?php
session_start();

// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
  header("Location: session.php");
  exit;
}

// Check if the form was submitted
if (isset($_POST['submit'])) {
  $username = $_SESSION['username'];

  // Sanitize user input
  $artist = filter_input(INPUT_POST, 'artist', FILTER_SANITIZE_STRING);

  // Validate user input
  $errors = array();
  if (empty($artist)) {
    $errors['artist'] = 'Please enter an artist name';
  } else if (strlen($artist) > 50) {
    $errors['artist'] = 'The artist name cannot be longer than 50 characters';
  }

  // If there are no errors, update the user's artist name in the database
  if (empty($errors)) {
    $stmt = $db->prepare("UPDATE users SET artist = :artist WHERE username = :username");
    $stmt->bindValue(':username', $username);
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
$username = $_SESSION['username'];
$stmt = $db->prepare("SELECT artist FROM users WHERE username = :username");
$stmt->bindValue(':username', $username);
$result = $stmt->execute();
$user = $result->fetchArray();
$artist = htmlspecialchars($user['artist']);

?>

    <?php include('setheader.php'); ?>
    <div class="container">
      <h3 class="text-secondary text-center mt-4 fw-bold"><i class="bi bi-gear"></i> Change Your Name</h3>
      <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; ?></div>
      <?php unset($_SESSION['success']); endif; ?>
      <form method="post" action="yourname.php">
        <div class="form-floating mb-3">
          <input type="text" class="form-control rounded-3 border text-secondary fw-bold border-4" id="artist" name="artist" value="<?php echo $artist; ?>" maxlength="40">
          <label for="floatingInput" class="text-secondary fw-bold">Your name: <?php echo $artist; ?></label>
        </div>
        <div class="container">
          <header class="d-flex justify-content-center py-3">
            <ul class="nav nav-pills">
              <li class="nav-item"><button type="submit" class="btn btn-primary me-1 fw-bold" name="submit">Save</button></li>
              <li class="nav-item d-md-none d-lg-none"><a href="setting.php" class="btn btn-danger ms-1 fw-bold">Back</a></li>
            </ul>
          </header>
        </div>
      </form>
    </div>
    <?php include('end.php'); ?>