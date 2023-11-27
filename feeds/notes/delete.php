<?php
require_once('../../auth.php');

$email = $_SESSION['email'];

$db = new PDO('sqlite:../../database.sqlite');

if (isset($_GET['id'])) {
  $post_id = $_GET['id'];

  // Use prepared statements to prevent SQL injection
  $query = "SELECT * FROM posts WHERE id = :id AND email = :email";
  $stmt = $db->prepare($query);
  $stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
  $stmt->bindParam(':email', $email, PDO::PARAM_STR);
  $stmt->execute();

  // Fetch the post
  $post = $stmt->fetch();

  if ($post) {
    // Use prepared statement for DELETE query as well
    $query = "DELETE FROM posts WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
    $stmt->execute();
  }
}

header('Location: ' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/feeds/notes/');
exit; // exit the script to prevent further output
?>

