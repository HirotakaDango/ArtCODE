<?php
// admin/update/update_logs.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();

// Retrieve the email from the session
$email = $_SESSION['admin']['email'];

$logFile = $_SERVER['DOCUMENT_ROOT'] . '/admin/update/log.json';

if (isset($_GET['clear']) && $_GET['clear'] === 'true') {
  // Clear the log file
  file_put_contents($logFile, json_encode([]));
  echo json_encode(['message' => 'Logs cleared']);
  exit;
}

// Read the log file
$logs = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];

// Return log entries as JSON
header('Content-Type: application/json');
echo json_encode($logs);
?>