<?php
require_once('../../auth.php');
$dbL = new SQLite3('../../database.sqlite');

// Handle favorite/unfavorite action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $emailL = $_SESSION['email'];
  $image_idL = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;
  $actionL = isset($_POST['action']) ? $_POST['action'] : '';

  if ($actionL === 'favorite') {
    // Check if already favorited
    $existing_favL = $dbL->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$emailL' AND image_id = $image_idL");

    if ($existing_favL == 0) {
      // Insert into favorites table
      $dbL->exec("INSERT INTO favorites (email, image_id) VALUES ('$emailL', $image_idL)");

      // Return success response
      echo json_encode(['success' => true]);
    } else {
      // Already favorited, return success
      echo json_encode(['success' => true]);
    }
  } elseif ($actionL === 'unfavorite') {
    // Delete from favorites table
    $dbL->exec("DELETE FROM favorites WHERE email = '$emailL' AND image_id = $image_idL");

    // Return success response
    echo json_encode(['success' => true]);
  } else {
    // Invalid action
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
  }
}

$dbL->close();
?>
