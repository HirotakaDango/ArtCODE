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
  $message_1 = filter_input(INPUT_POST, 'message_1', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $message_2 = filter_input(INPUT_POST, 'message_2', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $message_3 = filter_input(INPUT_POST, 'message_3', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

  // Update the user's profile description in the database
  $stmt = $db->prepare('UPDATE users SET message_1 = :message_1, message_2 = :message_2, message_3 = :message_3 WHERE email = :email');
  $stmt->bindValue(':message_1', $message_1, SQLITE3_TEXT);
  $stmt->bindValue(':message_2', $message_2, SQLITE3_TEXT);
  $stmt->bindValue(':message_3', $message_3, SQLITE3_TEXT);
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->execute();

  // Set a success message for display on the next page
  if (empty($current_1) && empty($current_2) && empty($current_3)) {
    $_SESSION['success_message'] = 'successfully added';
  } else {
    $_SESSION['success_message'] = 'successfully changed';
  }

  // Redirect to the profile page
  header('Location: contact.php');
  exit();
}

// Get the user's current profile description from the database
$email = $_SESSION['email'];
$stmt = $db->prepare('SELECT message_1, message_2, message_3 FROM users WHERE email = :email');
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);
$current_1 = htmlspecialchars($row['message_1'] ?? '');
$current_2 = htmlspecialchars($row['message_2'] ?? '');
$current_3 = htmlspecialchars($row['message_3'] ?? '');

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
          <input class="form-control rounded-3 border text-dark fw-bold border-4" id="desc" name="message_1" placeholder="message_1:" maxlength="180" value="<?php echo htmlspecialchars($current_1); ?>">
          <label for="floatingInput" class="text-dark fw-bold">Linked 1: <?php echo htmlspecialchars($current_1); ?></label>
        </div>
        <div class="form-floating mb-3">
          <input class="form-control rounded-3 border text-dark fw-bold border-4" id="desc" name="message_2" placeholder="message_2:" maxlength="180" value="<?php echo htmlspecialchars($current_2); ?>">
          <label for="floatingInput" class="text-dark fw-bold">Linked 2: <?php echo htmlspecialchars($current_2); ?></label>
        </div>
        <div class="form-floating mb-2">
          <input class="form-control rounded-3 border text-dark fw-bold border-4" id="desc" name="message_3" placeholder="message_3:" maxlength="180" value="<?php echo htmlspecialchars($current_3); ?>">
          <label for="floatingInput" class="text-dark fw-bold">Linked 3: <?php echo htmlspecialchars($current_3); ?></label>
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-bold" name="submit">Save</button>
      </form>
    </div>
    <?php include('end.php'); ?>
    </main>