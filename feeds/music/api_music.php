<?php
// Include SQLite database connection
$db = new SQLite3('../../database.sqlite');

// Fetch all songs from the music table
$query = "SELECT music.id, music.file, music.cover, music.album, music.lyrics, music.title, users.artist
          FROM music
          LEFT JOIN users ON music.email = users.email
          ORDER BY users.artist ASC, music.album ASC, music.title ASC";
$result = $db->query($query);

// Check if there are rows in the result
if ($result) {
  $response = array(); // Initialize the response array

  // Fetch all rows and store in the $rows array
  $rows = [];
  while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $rows[] = $row;
  }
  $totalRows = count($rows);

  // Iterate through each row
  foreach ($rows as $index => $row) {
    // Fetch next music record for the specified song
    $nextIndex = ($index + 1) % $totalRows;
    $nextRow = $rows[$nextIndex];

    // Fetch previous music record for the specified song
    $prevIndex = ($index - 1 + $totalRows) % $totalRows;
    $prevRow = $rows[$prevIndex];

    // Create a new array for each song
    $songInfo = [
      'id' => $row['id'],
      'file' => $row['file'],
      'cover' => 'covers/' . $row['cover'],
      'album' => $row['album'],
      'title' => $row['title'],
      'lyrics' => $row['lyrics'],
      'artist' => $row['artist'],
      'nextPlay' => [
        'id' => $nextRow['id'],
        'file' => $nextRow['file'],
        'cover' => 'covers/' . $nextRow['cover'],
        'album' => $nextRow['album'],
        'title' => $nextRow['title']
      ],
      'prevPlay' => [
        'id' => $prevRow['id'],
        'file' => $prevRow['file'],
        'cover' => 'covers/' . $prevRow['cover'],
        'album' => $prevRow['album'],
        'title' => $prevRow['title']
      ]
    ];
    
    $response[] = $songInfo;
  }

  // Close the database connection
  $db->close();

  // Set the response header to JSON
  header('Content-Type: application/json');

  // Output the JSON response
  echo json_encode($response, JSON_PRETTY_PRINT);
} else {
  // If there is an error in the query, return an error response
  echo json_encode(array('error' => 'Error retrieving songs'));
}
?>
