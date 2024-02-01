<?php
require_once('auth.php');

// Connect to the database using PDO
$db = new PDO('sqlite:../../database.sqlite');

// Get the id from the query string
$id = $_GET['id'];

// Fetch the post from the database
$stmt = $db->prepare('SELECT content, title FROM posts WHERE id = :id');
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if ($post) {
  // Set headers for file download
  header('Content-Type: text/plain');
  header('Content-Disposition: attachment; filename="' . $post['title'] . '.txt"');

  // Output the content
    echo $post['content'];
} else {
  // Handle the case where the post with the given id is not found
  echo "Post not found";
}
?>
