<?php
// Connect to the SQLite database
$db = new SQLite3('../database.sqlite');

// Function to generate a unique verification code
function generateVerificationCode($length = 20) {
  return substr(bin2hex(random_bytes($length)), 0, $length);
}

// Function to check if the verification code is unique
function isCodeUnique($db, $code) {
  $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE verification_code = :verification_code');
  $stmt->bindValue(':verification_code', $code, SQLITE3_TEXT);
  $result = $stmt->execute();
  $count = $result->fetchArray(SQLITE3_ASSOC)['COUNT(*)'];
  return $count == 0;
}

// Fetch all users from the database
$results = $db->query('SELECT id FROM users');

while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
  $userId = $row['id'];

  // Generate a unique verification code
  do {
    $verificationCode = generateVerificationCode();
  } while (!isCodeUnique($db, $verificationCode));

  // Update the user's verification_code
  $updateStmt = $db->prepare('UPDATE users SET verification_code = :verification_code WHERE id = :id');
  $updateStmt->bindValue(':verification_code', $verificationCode, SQLITE3_TEXT);
  $updateStmt->bindValue(':id', $userId, SQLITE3_INTEGER);
  $updateStmt->execute();
}

echo 'Verification codes have been successfully generated and assigned to all users.';
?>