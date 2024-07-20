<?php
// admin/authentication/request.php
session_start(); // Ensure session is started

require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
$db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/database.sqlite');

header('Content-Type: application/json'); // Ensure the response is JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password']; // Plaintext password

  // Prepare the query to check both admin and users tables with consistent columns
  $query = '
    SELECT id, email, NULL AS password, "admin" AS user_type, status FROM admin WHERE email = :email
    UNION ALL
    SELECT id, email, password, "user" AS user_type, NULL AS status FROM users WHERE email = :email
  ';
  
  $stmt = $db->prepare($query);
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $result = $stmt->execute();
  $user = $result->fetchArray(SQLITE3_ASSOC);

  $response = [];

  if ($user) {
    // Verify password based on user type
    if ($user['user_type'] === 'admin') {
      // Admin login doesn't use password, just email presence
      $_SESSION['admin'] = $user;
      $response['success'] = true;
    } elseif ($user['user_type'] === 'user') {
      // User login requires password check
      if ($password === $user['password']) {
        $_SESSION['user'] = $user;
        $response['success'] = true;
      } else {
        $response['success'] = false;
        $response['message'] = 'Invalid email or password!';
      }
    }
  } else {
    $response['success'] = false;
    $response['message'] = 'Invalid email or password!';
  }

  echo json_encode($response);
  exit();
} else {
  // If not POST request, send an error
  echo json_encode(['success' => false, 'message' => 'Invalid request method!']);
  exit();
}
?>