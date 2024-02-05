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

  // Fetch each row and store in the $response array
  while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    // Fetch next music record for the specified song
    $queryNext = "SELECT music.id, music.file, music.cover, music.album, music.title
                  FROM music
                  WHERE (music.album = :album AND music.title > :title)
                     OR (music.album > :album)
                  ORDER BY music.album ASC, music.title ASC
                  LIMIT 1";
    $stmtNext = $db->prepare($queryNext);
    $stmtNext->bindParam(':album', $row['album'], SQLITE3_TEXT);
    $stmtNext->bindParam(':title', $row['title'], SQLITE3_TEXT);
    $nextRow = $stmtNext->execute()->fetchArray(SQLITE3_ASSOC);

    if (!$nextRow) {
      // If no next row, fetch the first music record in the playlist
      $queryFirstNext = "SELECT * FROM music WHERE (music.album > :album) OR (music.album = :album AND music.title > :title) ORDER BY music.album ASC, music.title ASC LIMIT 1";
      $stmtFirstNext = $db->prepare($queryFirstNext);
      $stmtFirstNext->bindParam(':album', $row['album'], SQLITE3_TEXT);
      $stmtFirstNext->bindParam(':title', $row['title'], SQLITE3_TEXT);
      $firstNextRow = $stmtFirstNext->execute()->fetchArray(SQLITE3_ASSOC);
      $nextRow = $firstNextRow ? $firstNextRow : $row;
    }

    // Fetch previous music record for the specified song
    $queryPrev = "SELECT music.id, music.file, music.cover, music.album, music.title
                  FROM music
                  WHERE (music.album = :album AND music.title < :title)
                     OR (music.album < :album)
                  ORDER BY music.album DESC, music.title DESC
                  LIMIT 1";
    $stmtPrev = $db->prepare($queryPrev);
    $stmtPrev->bindParam(':album', $row['album'], SQLITE3_TEXT);
    $stmtPrev->bindParam(':title', $row['title'], SQLITE3_TEXT);
    $prevRow = $stmtPrev->execute()->fetchArray(SQLITE3_ASSOC);

    if (!$prevRow) {
      // If no previous row, fetch the last music record in the playlist
      $queryLastPrev = "SELECT * FROM music WHERE (music.album < :album) OR (music.album = :album AND music.title < :title) ORDER BY music.album DESC, music.title DESC LIMIT 1";
      $stmtLastPrev = $db->prepare($queryLastPrev);
      $stmtLastPrev->bindParam(':album', $row['album'], SQLITE3_TEXT);
      $stmtLastPrev->bindParam(':title', $row['title'], SQLITE3_TEXT);
      $lastPrevRow = $stmtLastPrev->execute()->fetchArray(SQLITE3_ASSOC);
      $prevRow = $lastPrevRow ? $lastPrevRow : $row;
    }

    // Check if there is only one song in the playlist and set a flag for looping
    $loopPlaylist = $nextRow === $prevRow && $nextRow === $row;

    // If looping is enabled, set the next and previous to the current song
    if ($loopPlaylist) {
      $nextRow = $row;
      $prevRow = $row;
    }

    // Create a new array for each song and add it to the response array
    $songInfo = array(
      'id' => $row['id'],
      'file' => $row['file'],
      'cover' => 'covers/' . $row['cover'],
      'album' => $row['album'],
      'title' => $row['title'],
      'lyrics' => $row['lyrics'],
      'artist' => $row['artist'],
      'nextPlay' => $nextRow,
      'prevPlay' => $prevRow
    );

    $response[] = $songInfo;
  }

  // Close the database connection
  $db->close();

  // Set the response header to JSON
  header('Content-Type: application/json');

  // Output the JSON response
  echo json_encode($response);
} else {
  // If there is an error in the query, return an error response
  echo json_encode(array('error' => 'Error retrieving songs'));
}
?>
