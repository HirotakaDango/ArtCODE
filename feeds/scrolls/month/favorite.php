<?php
require_once('../../../auth.php');
$db = new SQLite3('../../../database.sqlite');

// Handle favorite/unfavorite action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_SESSION['email'];
  $image_id = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;
  $action = isset($_POST['action']) ? $_POST['action'] : '';

  if ($action === 'favorite') {
    // Check if already favorited
    $existing_fav = $db->querySingle("SELECT COUNT(*) FROM favorites WHERE email = '$email' AND image_id = $image_id");

    if ($existing_fav == 0) {
      // Insert into favorites table
      $db->exec("INSERT INTO favorites (email, image_id) VALUES ('$email', $image_id)");

      // Return success response
      echo json_encode(['success' => true]);
    } else {
      // Already favorited, return error or just success if desired
      echo json_encode(['success' => true]); // Adjust as needed
    }
  } elseif ($action === 'unfavorite') {
    // Delete from favorites table
    $db->exec("DELETE FROM favorites WHERE email = '$email' AND image_id = $image_id");

    // Return success response
    echo json_encode(['success' => true]);
  } else {
    // Invalid action
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
  }
}

$db->close();
?>
