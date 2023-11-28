<?php
require_once('../auth.php');

try {
    $db = new PDO('sqlite:../database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$current_user_email = $_SESSION['email'];

// Fetch all chatrooms for the current user
try {
    $stmt = $db->prepare("SELECT DISTINCT chatroom_id, user_email FROM message_child WHERE email = :current_user_email OR user_email = :current_user_email");
    $stmt->bindParam(':current_user_email', $current_user_email, PDO::PARAM_STR);
    $stmt->execute();
    $chatrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ... Your HTML head content ... -->
</head>
<body>
    <div class="container">
        <h2>Conversations</h2>
        <?php if (isset($chatrooms)): ?>
            <ul>
                <?php foreach ($chatrooms as $chatroom): ?>
                    <?php
                        $chatroom_id = $chatroom['chatroom_id'];
                        $user_email = $chatroom['user_email'];
                    ?>
                    <li>
                        <a href="chatroom.php?chatroom_id=<?= $chatroom_id ?>&current_user=<?= $current_user_email ?>&to_user_email=<?= $user_email ?>">
                            Conversation with <?= $user_email ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>
