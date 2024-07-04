<?php
require_once('../../auth.php');
$dbP = new SQLite3('../../database.sqlite');

// Handle favorite/unfavorite action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $emailP = $_SESSION['email'];
  $image_idP = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;
  $actionP = isset($_POST['action']) ? $_POST['action'] : '';

  if ($actionP === 'favorite') {
    // Check if already favorited
    $existing_favP = $dbP->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$emailP' AND image_id = $image_idP");

    if ($existing_favP == 0) {
      // Insert into favorites table
      $dbP->exec("INSERT INTO favorites (email, image_id) VALUES ('$emailP', $image_idP)");

      // Return success response
      echo json_encode(['success' => true]);
    } else {
      // Already favorited, return success
      echo json_encode(['success' => true]);
    }
  } elseif ($actionP === 'unfavorite') {
    // Delete from favorites table
    $dbP->exec("DELETE FROM favorites WHERE email = '$emailP' AND image_id = $image_idP");

    // Return success response
    echo json_encode(['success' => true]);
  } else {
    // Invalid action
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
  }
}

$dbP->close();
?>
