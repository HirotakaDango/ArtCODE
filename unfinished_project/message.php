<?php
require_once('auth.php');

try {
    $db = new PDO('sqlite:database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$user_id = $_GET['user_id'];
$chatroom_id = 1; 

$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL,
    password TEXT NOT NULL,
    artist TEXT,
    `desc` TEXT,
    `bgpic` TEXT,
    pic TEXT,
    twitter TEXT,
    pixiv TEXT,
    other TEXT,
    region TEXT,
    joined DATE,
    born DATE,
    message_1 TEXT,
    message_2 TEXT,
    message_3 TEXT
)");

$db->exec("CREATE TABLE IF NOT EXISTS message_parent (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL,
    user_id INTEGER NOT NULL,
    user_email TEXT NOT NULL,
    datetime DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS message_child (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    message TEXT NOT NULL,
    email TEXT NOT NULL,
    chatroom_id INTEGER NOT NULL,
    user_email TEXT NOT NULL,
    datetime DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(chatroom_id) REFERENCES message_parent(id)
)");

function getMessages($chatroom_id) {
    global $db;
    $stmt = $db->prepare("SELECT message_child.message, message_child.email, message_parent.user_email, message_child.datetime
                         FROM message_child
                         JOIN message_parent ON message_child.chatroom_id = message_parent.id
                         WHERE message_parent.id = :chatroom_id
                         ORDER BY message_child.datetime ASC");
    $stmt->bindParam(':chatroom_id', $chatroom_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getEmail($userid)
{
    global $db;
    $stmt = $db->prepare("SELECT email FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $userid, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function insertMessage($message, $current_user_email, $chatroom_id, $recipient_user_email) {
    global $db;
    $to = getEmail($recipient_user_email);
    $stmt = $db->prepare("INSERT INTO message_child (message, email, chatroom_id, user_email) VALUES (:message, :current_user_email, :chatroom_id, :to_user_email)");
    $stmt->bindParam(':message', $message, PDO::PARAM_STR);
    $stmt->bindParam(':current_user_email', $current_user_email, PDO::PARAM_STR);
    $stmt->bindParam(':chatroom_id', $chatroom_id, PDO::PARAM_INT);
    $stmt->bindParam(':to_user_email',  $to['email'], PDO::PARAM_STR);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message']) && !empty($_POST['message'])) {
        $message = $_POST['message'];
        $current_user_email = $_SESSION['email'];
        $recipient_user_id = $_GET['user_id'];

        try {
            $stmt = $db->prepare("SELECT id FROM message_parent WHERE (email = :current_user_email AND user_id = :recipient_user_id) OR (user_email = :current_user_email AND user_id = :recipient_user_id)");
            $stmt->bindParam(':current_user_email', $current_user_email, PDO::PARAM_STR);
            $stmt->bindParam(':recipient_user_id', $recipient_user_id, PDO::PARAM_INT);
            $stmt->execute();
            $chatroom = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$chatroom) {
                $stmt = $db->prepare("INSERT INTO message_parent (email, user_id, user_email) VALUES (:current_user_email, :recipient_user_id, :current_user_email)");
                $stmt->bindParam(':current_user_email', $current_user_email, PDO::PARAM_STR);
                $stmt->bindParam(':recipient_user_id', $recipient_user_id, PDO::PARAM_INT);
                $stmt->execute();

                $chatroom_id = $db->lastInsertId();
            } else {
                $chatroom_id = $chatroom['id'];
            }

            insertMessage($message, $current_user_email, $chatroom_id, $_GET['user_id']);
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }
}

$current_user_email = $_SESSION['email'];
// echo $current_user_email;
$current_user_id = $_GET['user_id'];
// echo $current_user_id.'test';
// $to = getEmail( $_GET['user_id']);
// echo $to['email'];

try {
    $stmt = $db->prepare("SELECT id FROM message_parent WHERE (email = :current_user_email AND user_id = :user_id) OR (user_email = :current_user_email AND user_id = :user_id)");
    $stmt->bindParam(':current_user_email', $current_user_email, PDO::PARAM_STR);
    $stmt->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
    $stmt->execute();
    $chatrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Message Page</title>
</head>
<body>
    <div class="container">
        <h2>Messages</h2>
        <?php if (isset($chatrooms)): ?>
            <?php foreach ($chatrooms as $chatroom): ?>
                <?php
                //echo $chatroom['id'];
                    $messages = getMessages($chatroom_id);
                    foreach ($messages as $msg):
                        $isCurrentUser = ($msg['email'] === $current_user_email);
                        $messageClass = $isCurrentUser ? 'alert-info' : 'alert-secondary';
                        $alignClass = $isCurrentUser ? 'text-end' : 'text-start';
                ?>
                        <div class="alert <?= $messageClass ?> <?= $alignClass ?>" role="alert">
                            <strong><?= $msg['user_email'] ?>:</strong> <?= $msg['message'] ?> (<?= $msg['datetime'] ?>)
                        </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label for="message" class="form-label">Your Message</label>
                <textarea class="form-control" id="message" name="message" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
    </div>
</body>
</html>
