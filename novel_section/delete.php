<?php
// admin/novel_section/delete.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();

// Connect to the SQLite database
$db = new PDO('sqlite:' . $_SERVER['DOCUMENT_ROOT'] . '/database.sqlite');

if (isset($_GET['id'])) {
  $post_id = $_GET['id'];

  // Use prepared statements to prevent SQL injection
  $query = "SELECT * FROM novel WHERE id = :id";
  $stmt = $db->prepare($query);
  $stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
  $stmt->execute();

  // Fetch the post
  $post = $stmt->fetch();

  if ($post) {
    // Delete records from the reply_comments table based on the comment ID (comment_id)
    $stmt = $db->prepare("DELETE FROM reply_comments_novel WHERE comment_id IN (SELECT id FROM comments_novel WHERE filename = :filename)");
    $stmt->bindValue(':filename', $post_id);
    $stmt->execute();

    // Delete associated comments from comments_novel table
    $deleteCommentsQuery = "DELETE FROM comments_novel WHERE filename = :filename";
    $stmt = $db->prepare($deleteCommentsQuery);
    $stmt->bindParam(':filename', $post_id, PDO::PARAM_INT);
    $stmt->execute();

    // Delete associations from favorites_novel table
    $deleteFavoritesQuery = "DELETE FROM favorites_novel WHERE novel_id = :novel_id";
    $stmt = $db->prepare($deleteFavoritesQuery);
    $stmt->bindParam(':novel_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Delete associations from chapter table
    $deleteChapterQuery = "DELETE FROM chapter WHERE novel_id = :novel_id";
    $stmt = $db->prepare($deleteChapterQuery);
    $stmt->bindParam(':novel_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();

    // Use prepared statement for DELETE query as well
    $deletePostQuery = "DELETE FROM novel WHERE id = :id";
    $stmt = $db->prepare($deletePostQuery);
    $stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
    $stmt->execute();

    // Delete the image from the database
    $stmt = $db->prepare("DELETE FROM novel WHERE filename = :filename");
    $stmt->bindValue(':filename', $post['filename']);
    $stmt->execute();

    // Delete the original image and thumbnail
    unlink('../../feeds/novel/images/' . $post['filename']);
    unlink('../../feeds/novel/thumbnails/' . $post['filename']);

    header("Location: /admin/novel_section/");
    exit;
  }
}

header('Location: ' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/admin/novel_section/');
exit; // exit the script to prevent further output
?>