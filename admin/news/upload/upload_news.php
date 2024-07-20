<?php
header('Content-Type: application/json');

function filter_string($string) {
  return trim($string);
}

function nl2br_custom($string) {
  return str_replace("\n", '<br>', $string);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    // Database connection
    $db = new PDO('sqlite:' . $_SERVER['DOCUMENT_ROOT'] . '/database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $title = isset($_POST['title']) ? filter_string($_POST['title']) : '';
    $description = isset($_POST['description']) ? nl2br_custom(filter_string($_POST['description'])) : '';

    if (!empty($title) && !empty($description)) {
      // Prepare the SQL statement
      $stmt = $db->prepare('INSERT INTO news (title, description) VALUES (:title, :description)');

      // Bind parameters and execute the statement
      $stmt->bindParam(':title', $title);
      $stmt->bindParam(':description', $description);
      $stmt->execute();

      // Respond with success message
      echo json_encode([
        'status' => 'success',
        'message' => 'News uploaded successfully!'
      ]);
    } else {
      // Respond with error message if title or description is missing
      echo json_encode([
        'status' => 'danger',
        'message' => 'Both title and description are required!'
      ]);
    }
  } catch (PDOException $e) {
    // Respond with error message in case of database error
    echo json_encode([
      'status' => 'danger',
      'message' => 'Database error: ' . $e->getMessage()
    ]);
  }
} else {
  // Respond with error message for invalid request method
  echo json_encode([
    'status' => 'danger',
    'message' => 'Invalid request method!'
  ]);
}
?>