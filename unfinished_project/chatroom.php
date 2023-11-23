<?php
require_once('auth.php'); // Include your authentication file for session handling

// Connect to SQLite database using PDO with secure connection
try {
    $db = new PDO('sqlite:database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get the current user's email and user_id
$current_user_email = $_SESSION['email'];
$current_user_id = $_SESSION['id'];

// Fetch users who have messaged the current user
try {
    $stmt = $db->prepare("SELECT DISTINCT users.id, users.email
                         FROM users
                         JOIN message_parent ON users.id = message_parent.user_id
                         WHERE message_parent.user_email = :current_user_email
                         AND message_parent.user_id != :current_user_id");
    $stmt->bindParam(':current_user_email', $current_user_email, PDO::PARAM_STR);
    $stmt->bindParam(':current_user_id', $current_user_id, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <title>Chatroom</title>
</head>
<body>
    <div class="container">
        <h2>Users Messaging You</h2>
        <div class="row">
            <?php foreach ($users as $user): ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= $user['email'] ?></h5>
                            <p class="card-text">Click below to continue the conversation:</p>
                            <a href="message.php?user_id=<?= $user['id'] ?>" class="btn btn-primary">View Messages</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
