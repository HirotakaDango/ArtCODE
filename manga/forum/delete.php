<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: session.php');
  exit; // exit the script to prevent further output
}

$db = new PDO('sqlite:database.db');

if (isset($_GET['id'])) {
  $post_id = $_GET['id'];
  $user_id = $_SESSION['user_id'];

  // Check if the post with the given ID belongs to the current user
  $query = "SELECT * FROM posts WHERE id=:post_id AND user_id=:user_id";
  $stmt = $db->prepare($query);
  $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
  $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
  $stmt->execute();

  $post = $stmt->fetch();

  if ($post) {
    // If the post belongs to the user, delete it
    $delete_query = "DELETE FROM posts WHERE id=:post_id";
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $delete_stmt->execute();

    // Delete comments associated with the post
    $delete_comments_query = "DELETE FROM comments WHERE post_id=:post_id";
    $delete_comments_stmt = $db->prepare($delete_comments_query);
    $delete_comments_stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $delete_comments_stmt->execute();
  }
}

header('Location: index.php');
exit; // exit the script to prevent further output
?>