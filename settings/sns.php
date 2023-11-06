<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  header('Location: ../session.php');
  exit();
}

// Connect to the database
$db = new SQLite3('../database.sqlite');

// Handle the user's form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Get the user's ID from the session
  $email = $_SESSION['email'];

  // Get the user's input
  $twitter = filter_input(INPUT_POST, 'twitter', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $pixiv = filter_input(INPUT_POST, 'pixiv', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $other = filter_input(INPUT_POST, 'other', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

  // Update the user's profile description in the database
  $stmt = $db->prepare('UPDATE users SET twitter = :twitter, pixiv = :pixiv, other = :other WHERE email = :email');
  $stmt->bindValue(':twitter', $twitter, SQLITE3_TEXT);
  $stmt->bindValue(':pixiv', $pixiv, SQLITE3_TEXT);
  $stmt->bindValue(':other', $other, SQLITE3_TEXT);
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->execute();

  // Set a success message for display on the next page
  if (empty($current_twitter) && empty($current_pixiv) && empty($current_other)) {
    $_SESSION['success_message'] = 'successfully added';
  } else {
    $_SESSION['success_message'] = 'successfully changed';
  }

  // Redirect to the profile page
  header('Location: sns.php');
  exit();
}

// Get the user's current profile description from the database
$email = $_SESSION['email'];
$stmt = $db->prepare('SELECT twitter, pixiv, other FROM users WHERE email = :email');
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);
$current_twitter = htmlspecialchars($row['twitter'] ?? '');
$current_pixiv = htmlspecialchars($row['pixiv'] ?? '');
$current_other = htmlspecialchars($row['other'] ?? '');

// Close the database connection
$db->close();
?>

    <main id="swup" class="transition-main">
    <?php include('setheader.php'); ?>
    <div class="container">
      <h3 class="mt-4 text-center fw-bold text-dark"><i class="bi bi-phone-fill"></i> Edit Your Linked SNS</h3>
      <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
          <?php echo $_SESSION['success_message']; ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
      <?php endif; ?>
      <form method="POST">
        <div class="form-floating mt-3 mb-3">
          <input class="form-control rounded-3 border text-dark fw-bold border-4" id="desc" name="twitter" placeholder="Twitter:" maxlength="180" value="<?php echo htmlspecialchars($current_twitter); ?>">
          <label for="floatingInput" class="text-dark fw-bold">Twitter: <?php echo htmlspecialchars($current_twitter); ?></label>
        </div>
        <div class="form-floating mb-3">
          <input class="form-control rounded-3 border text-dark fw-bold border-4" id="desc" name="pixiv" placeholder="Pixiv:" maxlength="180" value="<?php echo htmlspecialchars($current_pixiv); ?>">
          <label for="floatingInput" class="text-dark fw-bold">Pixiv: <?php echo htmlspecialchars($current_pixiv); ?></label>
        </div>
        <div class="form-floating mb-2">
          <input class="form-control rounded-3 border text-dark fw-bold border-4" id="desc" name="other" placeholder="Other:" maxlength="180" value="<?php echo htmlspecialchars($current_other); ?>">
          <label for="floatingInput" class="text-dark fw-bold">Additional: <?php echo htmlspecialchars($current_other); ?></label>
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-bold" name="submit">Save</button>
      </form>
    </div>
    <?php include('end.php'); ?>
    </main>