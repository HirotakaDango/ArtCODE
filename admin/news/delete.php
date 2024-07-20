<?php
// admin/news/delete.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newsId = filter_input(INPUT_POST, 'news_id', FILTER_SANITIZE_NUMBER_INT);

    if ($newsId && filter_var($newsId, FILTER_VALIDATE_INT)) {
        try {
            // Correct database path
            $pdo = new PDO('sqlite:' . $_SERVER['DOCUMENT_ROOT'] . '/database.sqlite');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Prepare and execute delete statement
            $stmt = $pdo->prepare('DELETE FROM news WHERE id = :id');
            $stmt->bindParam(':id', $newsId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No news item found with that ID']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
