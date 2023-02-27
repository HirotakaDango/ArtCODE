<?php
session_start();

$db = new SQLite3('../message');
$db_file = '../message';

// Create users and messages tables if they don't exist
try {
  $pdo = new PDO("sqlite:" . $db_file);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT NOT NULL UNIQUE, password TEXT NOT NULL)");
  $pdo->exec("CREATE TABLE IF NOT EXISTS messages (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, message TEXT NOT NULL, FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE)");
} catch (PDOException $e) {
  die("Could not connect to database: " . $e->getMessage());
}

if (!isset($_SESSION['user_id'])) {
  header('Location: session.php');
  exit();
}

// Handle message deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
  $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ? AND user_id = ?");
  $stmt->execute([$_POST['delete'], $_SESSION['user_id']]);
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit();
}

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message']) && trim($_POST['message']) !== '') {
  $stmt = $pdo->prepare("INSERT INTO messages (user_id, message) VALUES (?, ?)");
  $stmt->execute([$_SESSION['user_id'], htmlspecialchars($_POST['message'])]);
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit();
}

// Handle logout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
  session_unset();
  session_destroy();
  header('Location: session.php');
  exit();
} 

// Display chat messages
$stmt = $pdo->prepare("SELECT messages.id, messages.user_id, messages.message, users.username FROM messages INNER JOIN users ON messages.user_id = users.id ORDER BY messages.id DESC");
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Chat Group</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
</head>
<body>
  <div class="container mt-1">
    <form method="post">
      <div class="d-flex justify-content-end mt-3 mb-2">
        <a href="../index.php" class="btn btn-primary me-1">Home</a>
        <input type="submit" name="logout" value="Logout" class="btn btn-danger ms-1">
      </div>
    </form>
    <h1 class="text-center">Chat Group</h1>
    <form method="post">
      <div class="form-group mb-2">
        <input type="text" name="message" class="form-control" placeholder="message">
      </div>
      <input type="submit" value="Send" class="btn btn-primary fw-bold w-100">
      <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['user_id']); ?>">
    </form>
    <hr>
    <?php foreach ($messages as $message): ?>
      <div>
        <p><strong><?php echo htmlspecialchars($message['username']); ?>:</strong></p>
        <p><?php echo htmlspecialchars($message['message']); ?></p>
        <?php if ($message['user_id'] == $_SESSION['user_id']): ?>
          <form method="post" style="display: inline;">
            <input type="hidden" name="delete" value="<?php echo $message['id']; ?>">
            <button type="submit" class="btn btn-danger">Delete</button>
          </form>
        <?php endif; ?>
      </div>
      <hr>
    <?php endforeach; ?>
  </div>
</body>
</html>
