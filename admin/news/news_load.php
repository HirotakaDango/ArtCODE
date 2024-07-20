<?php
// /admin/news/news_load.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();

$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Connect to the SQLite database
$db = new PDO('sqlite:' . $_SERVER['DOCUMENT_ROOT'] . '/database.sqlite');

// Prepare SQL query based on sort order
if ($sortOrder === 'newest') {
  $sql = 'SELECT * FROM news ORDER BY created_at DESC';
} else {
  $sql = 'SELECT * FROM news ORDER BY created_at ASC';
}

$stmt = $db->prepare($sql);
$stmt->execute();
$newsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to format date
function formatDate($date) {
  $timestamp = strtotime($date);
  return date('l, d F Y | H:i', $timestamp);
}

// Format the dates
foreach ($newsItems as &$news) {
  $news['created_at'] = formatDate($news['created_at']);
}

// Output as JSON
header('Content-Type: application/json');
echo json_encode($newsItems);
?>