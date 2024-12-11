<?php
// Connect to the SQLite database
$db = new SQLite3('../../database.sqlite');

// Create tables if not exist
$db->exec('CREATE TABLE IF NOT EXISTS texts (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, title TEXT NOT NULL, content TEXT NOT NULL, tags TEXT, date DATETIME, view_count INTEGER DEFAULT 0)');
$db->exec('CREATE TABLE IF NOT EXISTS text_favorites (id INTEGER PRIMARY KEY AUTOINCREMENT, text_id INTEGER NOT NULL, email TEXT NOT NULL, FOREIGN KEY (text_id) REFERENCES texts(id))');

// Get uid and offset parameters from URL
$uid = isset($_GET['uid']) ? (int)$_GET['uid'] : null;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = 5;  // Number of cards to show per request

// Ensure that uid is provided
if (!$uid) {
  echo json_encode(['success' => false, 'message' => 'User ID is required.']);
  exit;
}

// Build the SQL query with pagination and filtering by uid
$query = "SELECT texts.*, users.email AS user_email, users.artist 
          FROM texts 
          LEFT JOIN users ON texts.email = users.email 
          WHERE users.id = :uid 
          ORDER BY texts.id DESC
          LIMIT :limit OFFSET :offset";

// Prepare and execute the query
$stmt = $db->prepare($query);
$stmt->bindValue(':uid', $uid, SQLITE3_INTEGER);
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$results = $stmt->execute();

// Fetch the results
$texts = [];
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
  // Prepare the data to return as JSON
  $texts[] = [
    'id' => $row['id'],
    'title' => $row['title'],
    'author' => $row['artist'] ?? 'Unknown',
    'email' => $row['email']
  ];
}

// Check if there are more records to load
$moreRecords = $results->fetchArray() ? true : false;

echo json_encode([
  'success' => true,
  'texts' => $texts,
  'hasMore' => $moreRecords
]);
