<?php
// Connect to the SQLite database using parameterized query
$db = new SQLite3('database.sqlite');

if (isset($_GET['q'])) {
  // Get the user input from the query parameter
  $input = $_GET['q'];

  // Separate the input by commas to get individual words
  $words = explode(',', $input);

  // Fetch all tags and titles from the database
  $stmt = $db->prepare("SELECT DISTINCT tags, title FROM images");
  $result = $stmt->execute();

  // Store all tags and titles in separate arrays
  $allTags = array();
  $allTitles = array();
  while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $tags = explode(',', $row['tags']);
    foreach ($tags as $tag) {
      $suggestion = trim($tag);
      if (!in_array($suggestion, $allTags)) {
        $allTags[] = $suggestion;
      }
    }

    $title = $row['title'];
    if (!in_array($title, $allTitles)) {
      $allTitles[] = $title;
    }
  }

  // Filter the tags and titles based on each word and store the suggestions
  $suggestions = array();
  foreach ($words as $word) {
    $trimmedWord = trim($word);
    foreach ($allTags as $tag) {
      // Check if the tag starts with the word
      if (stripos($tag, $trimmedWord) === 0) {
        $suggestions[] = $tag;
      }
    }

    foreach ($allTitles as $title) {
      // Check if the title starts with the word
      if (stripos($title, $trimmedWord) === 0) {
        $suggestions[] = $title;
      }
    }
  }

  // Send the suggestions as a JSON response
  echo json_encode($suggestions);
}
?>
